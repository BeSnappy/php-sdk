<?php namespace SnappySdk;

use Guzzle\Http\Message\Request;

class Client {

	/**
	 * The auth implementation.
	 *
	 * @var \SnappySdk\AuthInterface
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
	 * @param  \SnappySdk\AuthInterface  $auth
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
	 * Search a given account's tickets.
	 *
	 * @param  int  $accountId
	 * @param  string  $query
	 * @param  int  $page
	 * @return array
	 */
	public function search($accountId, $query, $page = 1)
	{
		return $this->send($this->getHttp()->get($this->url.'account/'.$accountId.'/search?query='.$query.'&page='.$page));
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
	 * Get a contact for the given account ID and contact ID or e-mail address.
	 *
	 * @param  int  $accountId
	 * @param  string|int  $idOrEmail
	 * @return array
	 */
	public function getContact($accountId, $idOrEmail)
	{
		return $this->send($this->getHttp()->get($this->url.'account/'.$accountId.'/contacts/'.$idOrEmail));
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
	 * Get all of the tickets for a given mailbox ID that are in the inbox.
	 *
	 * @param  int  $mailboxId
	 * @return array
	 */
	public function getInboxTickets($mailboxId)
	{
		return $this->send($this->getHttp()->get($this->url.'mailbox/'.$mailboxId.'/inbox'));
	}

	/**
	 * Get all of the tickets for a given mailbox ID that are assigned to you.
	 *
	 * @param  int  $mailboxId
	 * @return array
	 */
	public function getYourTickets($mailboxId)
	{
		return $this->send($this->getHttp()->get($this->url.'mailbox/'.$mailboxId.'/yours'));
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
	 * @param  array  $headers
	 * @return string
	 */
	public function sendMessage(Message $message, array $headers = array())
	{
		$request = $this->getHttp()->post($this->url.'note', null, $this->buildMessagePayload($message));

		foreach ($headers as $key => $value)
		{
			$request->setHeader($key, $value);
		}

		return (string) $this->sendPlain($request);
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
		$payload['status'] = $message->keepWaiting ? 'waiting' : null;

		if ($message->system)
		{
			$payload['system'] = true;
		}

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

		$payload['contact_extras'] = $message->contactExtras;

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
	 * Get the wall posts for the given account.
	 *
	 * @param  int  $accountId
	 * @param  int  $postId
	 * @return array
	 */
	public function deleteWallPost($accountId, $postId)
	{
		$request = $this->getHttp()->delete($this->url.'account/'.$accountId.'/wall/'.$postId);

		$this->sendPlain($request);
	}

	/**
	 * Comment on a wall post.
	 *
	 * @param  int  $accountId
	 * @param  int  $postId
	 * @param  string  $comment
	 * @return array
	 */
	public function commentOnWallPost($accountId, $postId, $comment)
	{
		$request = $this->getHttp()->post($this->url.'account/'.$accountId.'/wall/'.$postId.'/comment');

		$request->addPostFields(array('comment' => $comment));

		$this->sendPlain($request);
	}

	/**
	 * Delete a wall comment.
	 *
	 * @param  int  $accountId
	 * @param  int  $postId
	 * @param  int  $commentId
	 * @return array
	 */
	public function deleteWallComment($accountId, $postId, $commentId)
	{
		$request = $this->getHttp()->delete($this->url.'account/'.$accountId.'/wall/'.$postId.'/comment/'.$commentId);

		$this->sendPlain($request);
	}

	/**
	 * Comment on a wall post.
	 *
	 * @param  int  $accountId
	 * @param  int  $postId
	 * @return array
	 */
	public function likeWallPost($accountId, $postId)
	{
		$request = $this->getHttp()->post($this->url.'account/'.$accountId.'/wall/'.$postId.'/like');

		$this->sendPlain($request);
	}

	/**
	 * Unlike a wall comment.
	 *
	 * @param  int  $accountId
	 * @param  int  $postId
	 * @return array
	 */
	public function unlikeWallPost($accountId, $postId)
	{
		$request = $this->getHttp()->delete($this->url.'account/'.$accountId.'/wall/'.$postId.'/like');

		$this->sendPlain($request);
	}

	/**
	 * Get the FAQs for the given account.
	 *
	 * @param  int  $accountId
	 * @return array
	 */
	public function getFaqs($accountId)
	{
		return $this->send($this->getHttp()->get($this->url.'account/'.$accountId.'/faqs'));
	}

	/**
	 * Search a given account's FAQs.
	 *
	 * @param  int  $accountId
	 * @param  string  $query
	 * @param  int  $page
	 * @return array
	 */
	public function searchFaqs($accountId, $query, $page = 1)
	{
		return $this->send($this->getHttp()->get($this->url.'account/'.$accountId.'/faqs/search?query='.$query.'&page='.$page));
	}

	/**
	 * Get the FAQs for the given account.
	 *
	 * @param  int  $accountId
	 * @param  string  $title
	 * @param  string  $url
	 * @return array
	 */
	public function createFaq($accountId, $title, $url = 'faq')
	{
		$payload = json_encode(compact('title', 'url'));

		return $this->send($this->getHttp()->post(
			$this->url.'account/'.$accountId.'/faqs', null, $payload
		));
	}

	/**
	 * Update the FAQs for the given account.
	 *
	 * @param  int  $accountId
	 * @param  string  $title
	 * @param  string  $url
	 * @return array
	 */
	public function updateFaq($accountId, $faqId, $title, $url = 'faq')
	{
		$payload = json_encode(compact('title', 'url'));

		return $this->send($this->getHttp()->put(
			$this->url.'account/'.$accountId.'/faqs/'.$faqId, null, $payload
		));
	}

	/**
	 * Delete the given FAQ.
	 *
	 * @param  int  $accountId
	 * @param  int  $faqId
	 * @return array
	 */
	public function deleteFaq($accountId, $faqId)
	{
		return $this->sendPlain($this->getHttp()->delete(
			$this->url.'account/'.$accountId.'/faqs/'.$faqId
		));
	}

	/**
	 * Get the FAQs topics for the given account.
	 *
	 * @param  int  $accountId
	 * @param  int  $faqId
	 * @return array
	 */
	public function getFaqTopics($accountId, $faqId)
	{
		return $this->send($this->getHttp()->get(
			$this->url.'account/'.$accountId.'/faqs/'.$faqId.'/topics')
		);
	}

	/**
	 * Create an FAQ topic for the given account.
	 *
	 * @param  int  $accountId
	 * @param  int  $faqId
	 * @param  string  $topic
	 * @param  int  $order
	 * @return array
	 */
	public function createFaqTopic($accountId, $faqId, $topic, $order = 0)
	{
		$payload = json_encode(compact('topic', 'order'));

		return $this->send($this->getHttp()->post(
			$this->url.'account/'.$accountId.'/faqs/'.$faqId.'/topics', null, $payload
		));
	}

	/**
	 * Update the FAQ topic for the given account.
	 *
	 * @param  int  $accountId
	 * @param  int  $faqId
	 * @param  int  $topicId
	 * @param  string  $topic
	 * @param  int  $order
	 * @return array
	 */
	public function updateFaqTopic($accountId, $faqId, $topicId, $topic, $order = 0)
	{
		$payload = json_encode(compact('topic', 'order'));

		return $this->send($this->getHttp()->put(
			$this->url.'account/'.$accountId.'/faqs/'.$faqId.'/topics/'.$topicId, null, $payload
		));
	}

	/**
	 * Delete the FAQ topic for the given account.
	 *
	 * @param  int  $accountId
	 * @param  int  $faqId
	 * @param  int  $topicId
	 * @return array
	 */
	public function deleteFaqTopic($accountId, $faqId, $topicId)
	{
		return $this->send($this->getHttp()->delete(
			$this->url.'account/'.$accountId.'/faqs/'.$faqId.'/topics/'.$topicId
		));
	}

	/**
	 * Get the FAQs topic questions for the given account.
	 *
	 * @param  int  $accountId
	 * @param  int  $faqId
	 * @param  int  $topicId
	 * @return array
	 */
	public function getTopicQuestions($accountId, $faqId, $topicId)
	{
		return $this->send($this->getHttp()->get(
			$this->url.'account/'.$accountId.'/faqs/'.$faqId.'/topics/'.$topicId.'/questions')
		);
	}

	/**
	 * Update the FAQ topic for the given account.
	 *
	 * @param  int  $accountId
	 * @param  int  $faqId
	 * @param  int  $topicId
	 * @param  string  $question
	 * @param  string  $answer
	 * @return array
	 */
	public function createTopicQuestion($accountId, $faqId, $topicId, $question, $answer)
	{
		$payload = json_encode(compact('question', 'answer'));

		return $this->send($this->getHttp()->post(
			$this->url.'account/'.$accountId.'/faqs/'.$faqId.'/topics/'.$topicId.'/questions', null, $payload
		));
	}

	/**
	 * Update the FAQ topic for the given account.
	 *
	 * @param  int  $accountId
	 * @param  int  $faqId
	 * @param  int  $topicId
	 * @param  int  $questionId
	 * @param  string  $question
	 * @param  string  $answer
	 * @return array
	 */
	public function updateTopicQuestion($accountId, $faqId, $topicId, $questionId, $question, $answer)
	{
		$payload = json_encode(compact('question', 'answer'));

		return $this->send($this->getHttp()->put(
			$this->url.'account/'.$accountId.'/faqs/'.$faqId.'/topics/'.$topicId.'/questions/'.$questionId, null, $payload
		));
	}

	/**
	 * Update the FAQ topics for the given question.
	 *
	 * @param  int  $accountId
	 * @param  int  $faqId
	 * @param  int  $topicId
	 * @param  int  $questionId
	 * @param  array  $topics
	 * @return array
	 */
	public function syncQuestionTopics($accountId, $faqId, $topicId, $questionId, array $topics)
	{
		$payload = json_encode(compact('topics'));

		return $this->send($this->getHttp()->put(
			$this->url.'account/'.$accountId.'/questions/'.$questionId.'/topics', null, $payload
		));
	}

	/**
	 * Update the FAQ topic for the given account.
	 *
	 * @param  int  $accountId
	 * @param  int  $faqId
	 * @param  int  $topicId
	 * @param  int  $questionId
	 * @param  string  $question
	 * @param  string  $answer
	 * @return array
	 */
	public function deleteTopicQuestion($accountId, $faqId, $topicId, $questionId)
	{
		return $this->send($this->getHttp()->delete(
			$this->url.'account/'.$accountId.'/faqs/'.$faqId.'/topics/'.$topicId.'/questions/'.$questionId
		));
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
		return (string) $this->send($request, false);
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