<?php namespace SnappySdk;

use Guzzle\Http\Message\Request;

class Client {

	/**
	 * The auth implementation.
	 *
	 * @var \Snappy\AuthInterface
	 */
	protected $auth;

	/**
	 * The URL end-point for the API.
	 *
	 * @var string
	 */
	protected $url = 'https://app.besnappy.com/api/v1/';

	/**
	 * Create a new Snappy Client instance.
	 *
	 * @param  \Snappy\AuthInterface  $auth
	 * @return void
	 */
	public function __construct(AuthInterface $auth, $url = null)
	{
		$this->auth = $auth;

		if ( ! is_null($url)) $this->url = $url;
	}

	/**
	 * Get all of the mailboxes you have access to.
	 *
	 * @return array
	 */
	public function getAccounts()
	{
		return $this->send($this->getHttp()->get($this->url.'accounts'));
	}

	/**
	 * Get all of the mailboxes for a given account ID.
	 *
	 * @param  int  $accountId
	 * @return array
	 */
	public function getMailboxes($accountId)
	{
		return $this->send($this->getHttp()->get($this->url.'account/'.$accountId.'/mailboxes'));
	}

	/**
	 * Get all of the documents for a given account ID.
	 *
	 * @param  int  $accountId
	 * @return array
	 */
	public function getDocuments($accountId)
	{
		return $this->send($this->getHttp()->get($this->url.'account/'.$accountId.'/documents'));	
	}

	/**
	 * Get all of the staff for a given account ID.
	 *
	 * @param  int  $accountId
	 * @return array
	 */
	public function getStaff($accountId)
	{
		return $this->send($this->getHttp()->get($this->url.'account/'.$accountId.'/staff'));
	}

	/**
	 * Get all of the tickets for a given mailbox ID.
	 *
	 * @param  int  $mailboxId
	 * @return array
	 */
	public function getWaitingTickets($mailboxId)
	{
		return $this->send($this->getHttp()->get($this->url.'mailbox/'.$mailboxId.'/tickets'));
	}

	/**
	 * Get the details for a given ticket ID.
	 *
	 * @param  int  $ticketId
	 * @return array
	 */
	public function getTicketDetails($ticketId)
	{
		return $this->send($this->getHttp()->get($this->url.'ticket/'.$ticketId));
	}

	/**
	 * Get the notes for the given ticket ID.
	 *
	 * @param  int  $ticketId
	 * @return array
	 */
	public function getTicketNotes($ticketId)
	{
		return $this->send($this->getHttp()->get($this->url.'ticket/'.$ticketId.'/notes'));	
	}

	/**
	 * Send a new message via your Snappy account.
	 *
	 * @param  \Snappy\Message  $message
	 * @return string
	 */
	public function sendMessage(Message $message)
	{
		$request = $this->getHttp()->post($this->url.'note', null, $this->buildMessagePayload($message));

		return $this->sendPlain($request);
	}

	/**
	 * Build the JSON payload for sending a note to the API.
	 *
	 * @param  \Snappy\Message  $message
	 * @return string
	 */
	protected function buildMessagePayload(Message $message)
	{
		$payload = array();

		if (isset($message->mailboxId)) {
			$payload['mailbox_id'] = $message->mailboxId;
		}

		if (isset($message->staffId)) {
			$payload['staff_id'] = $message->staffId;
		}

		$payload['id'] = $message->ticketId;
		$payload['subject'] = (string) $message->subject;
		$payload['message'] = $message->message;
		$payload['attachments'] = array();
		$payload['tags'] = $message->tags;
		$payload['scope'] = $message->scope ?: 'public';

		if (isset($message->from)) {
			$payload['from'][] = array('name' => $message->from['name'], 'address' => $address = $message->from['address']);
			if (filter_var($address, FILTER_VALIDATE_EMAIL) === false)
			{
				throw new \InvalidArgumentException("Invalid e-mail address [$address].");
			}
		}

		foreach (array('to', 'bcc', 'cc') as $type) {
			foreach ($message->{$type} as $name => $address) {
				if (is_numeric($name)) $name = $address;
				$payload[$type][] = compact('name', 'address');
				if (filter_var($address, FILTER_VALIDATE_EMAIL) === false)
				{
					throw new \InvalidArgumentException("Invalid e-mail address [$address].");
				}
			}
		}

		return json_encode($payload);
	}

	/**
	 * Replace a given ticket's tags with the given tags.
	 *
	 * @param  int    $ticketId
	 * @param  array  $tags
	 * @return void
	 */
	public function updateTicketTags($ticketId, array $tags)
	{
		$request = $this->getHttp()->post($this->url.'ticket/'.$ticketId.'/tags');

		$request->addPostFields(array('tags' => json_encode($tags)));

		$this->sendPlain($request);
	}

	/**
	 * Get the wall posts for the given account.
	 *
	 * @param  int    $accountId
	 * @param  int    $after
	 * @return array
	 */
	public function getWallPosts($accountId, $after = 0)
	{
		return $this->send($this->getHttp()->get($this->url.'account/'.$accountId.'/wall?after='.$after));
	}

	/**
	 * Get the wall posts for the given account.
	 *
	 * @param  int     $accountId
	 * @param  string  $content
	 * @param  string  $type
	 * @param  array   $tags
	 * @param  int     $ticket
	 * @param  int     $ntoe
	 * @return array
	 */
	public function postToWall($accountId, $content, $type = 'post', array $tags = array(), $ticket = null, $note = null)
	{
		$payload = json_encode(compact('content', 'type', 'tags', 'ticket', 'note'));

		return $this->sendPlain($this->getHttp()->post($this->url.'account/'.$accountId.'/wall', null, $payload));
	}

	/**
	 * Send the request and return the JSON payload as an array.
	 *
	 * @param  \Guzzle\Http\Message\Request  $request
	 * @param  bool  $json
	 * @return array|string
	 */
	protected function send(Request $request, $json = true)
	{
		$request = $this->auth->addCredentialsToRequest($request);

		$response = $request->send();

		return $json ? json_decode($response->getBody(), true) : $response->getBody();
	}

	/**
	 * Send the request and get a plain response.
	 *
	 * @param  \Guzzle\Http\Message\Request  $request
	 * @return string
	 */
	protected function sendPlain(Request $request)
	{
		return $this->send($request, false);
	}

	/**
	 * Get a new HTTP client instance.
	 *
	 * @return \Guzzle\Http\Client
	 */
	public function getHttp()
	{
		$client = new \Guzzle\Http\Client;
		$client->setSslVerification(false, false, false);
		return $client;
	}

}