<?php namespace Snappy\PhpSdk;

use Guzzle\Http\Message\Request;

class Client {

	/**
	 * The auth implementation.
	 *
	 * @var \Snappy\PhpSdk\AuthInterface
	 */
	protected $auth;

	/**
	 * The URL end-point for the API.
	 *
	 * @var string
	 */
	const URL = 'https://app.besnappy.com/api/v1/';

	/**
	 * Create a new Snappy Client instance.
	 *
	 * @param  \Snappy\PhpSdk\AuthInterface  $auth
	 * @return void
	 */
	public function __construct(AuthInterface $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Get all of the mailboxes you have access to.
	 *
	 * @return array
	 */
	public function getAccounts()
	{
		return $this->send($this->getHttp()->get(static::URL.'accounts'));
	}

	/**
	 * Get all of the mailboxes for a given account ID.
	 *
	 * @param  int  $accountId
	 * @return array
	 */
	public function getMailboxes($accountId)
	{
		return $this->send($this->getHttp()->get(static::URL.'account/'.$accountId.'/mailboxes'));
	}

	/**
	 * Get all of the documents for a given account ID.
	 *
	 * @param  int  $accountId
	 * @return array
	 */
	public function getDocuments($accountId)
	{
		return $this->send($this->getHttp()->get(static::URL.'account/'.$accountId.'/documents'));	
	}

	/**
	 * Get all of the staff for a given account ID.
	 *
	 * @param  int  $accountId
	 * @return array
	 */
	public function getStaff($accountId)
	{
		return $this->send($this->getHttp()->get(static::URL.'account/'.$accountId.'/staff'));
	}

	/**
	 * Get all of the tickets for a given mailbox ID.
	 *
	 * @param  int  $mailboxId
	 * @return array
	 */
	public function getWaitingTickets($mailboxId)
	{
		return $this->send($this->getHttp()->get(static::URL.'mailbox/'.$mailboxId.'/tickets'));
	}

	/**
	 * Get the details for a given ticket ID.
	 *
	 * @param  int  $ticketId
	 * @return array
	 */
	public function getTicketDetails($ticketId)
	{
		return $this->send($this->getHttp()->get(static::URL.'ticket/'.$ticketId));
	}

	/**
	 * Get the notes for the given ticket ID.
	 *
	 * @param  int  $ticketId
	 * @return array
	 */
	public function getTicketNotes($ticketId)
	{
		return $this->send($this->getHttp()->get(static::URL.'ticket/'.$ticketId.'/notes'));	
	}

	/**
	 * Send a new message via your Snappy account.
	 *
	 * @param  \Snappy\PhpSdk\Message  $message
	 * @return void
	 */
	public function sendMessage(Message $message)
	{
		$request = $this->getHttp()->post(static::URL.'note', null, $this->buildMessagePayload($message));

		$this->sendPlain($request);
	}

	/**
	 * Build the JSON payload for sending a note to the API.
	 *
	 * @param  \Snappy\PhpSdk\Message  $message
	 * @return string
	 */
	protected function buildMessagePayload(Message $message)
	{
		$payload = array();

		if (isset($message->mailboxId)) {
			$payload['mailbox_id'] = $message->mailboxId;
		}

		if (isset($note->staffId)) {
			$payload['staff_id'] = $note->staffId;
		}

		$payload['id'] = $note->ticketId;
		$payload['subject'] = (string) $note->subject;
		$payload['message'] = $note->message;
		$payload['attachments'] = array();
		$payload['tags'] = $note->tags;
		$payload['scope'] = $note->scope ?: 'public';

		if (isset($message->from)) {
			$payload['from'][] = array('name' => $message->from['name'], 'address' => $message->from['address']);			
		}

		foreach (array('to', 'bcc', 'cc') as $type) {
			foreach ($message->{$type} as $name => $address) {
				if (is_numeric($name)) $name = $address;
				$payload[$type][] = compact('name', 'address');
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
		$request = $this->getHttp()->post(static::URL.'ticket/'.$ticketId.'/tags');

		$request->addPostFields(array('tags' => json_encode($tags)));

		$this->sendPlain($request);
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
	protected function getHttp()
	{
		return new \Guzzle\Http\Client;
	}

}