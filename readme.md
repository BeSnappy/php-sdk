# Snappy PHP SDK

## Installation

The SDK should be installed via [Composer](http://getcomposer.org). Simply add the `snappy/php-sdk` dependency to your `composer.json` file.

## Usage

First, create a new authentication provider:

	$auth = new SnappySdk\UserPassAuthentication('user', 'pass');

Next, create an SDK client instance:

	$client = new SnappySdk\Client($auth);

Now you're ready to start retrieving information from Snappy!

	$accounts = $client->getAccounts();

## Sending Messages

To send a message to your Snappy account, create a new `Message` instance, and use the `sendMessage` method on the client:

	$message = new SnappySdk\Message;

	$message->mailboxId = 3;
	$message->setFrom('foo@bar.com', 'John Smith');
	$message->subject = 'Hello World';
	$message->message = 'This is my message!';

	$nonce = $client->sendMessage($message);

To send an outgoing message, a `staffId` must be specified, as well as the recipient address:

	$message = new SnappySdk\Message;

	$message->mailboxId = 3;
	$message->staffId = 3;
	$message->addTo('foo@bar.com', 'John Smith');
	$message->subject = 'Hello World';
	$message->message = 'This is my message!';

	$nonce = $client->sendMessage($message);

If you are attaching a message to an existing thread, add the ticket "nonce" to the message:

	$message->ticketId = $nonce;

## Available Methods

These are the available SDK client methods. They all interact with the [Snappy API](https://github.com/BeSnappy/api-docs).

- getAccounts 
- search($query, $page = 1)
- getMailboxes($accountId)
- getDocuments($accountId)
- getStaff($accountId)
- getContact($accountId, $idOrEmailAddress)
- getWaitingTickets($mailboxId)
- getTicketDetails($ticketId)
- getTicketNotes($ticketId)
- updateTicketTags($ticketId, $tags)
- sendMessage($message)
- getWallPost($accountId, $after = 0)
- postToWall($accountId, $content, $type = 'post', $tags = array(), $ticket = null, $note = null)
- deleteWallPost($accountId, $postId)
- commentOnWallPost($accountId, $postId, $comment)
- deleteWallComment($accountId, $postId, $commentId)
- likeWallPost($accountId, $postId)
- unlikeWallPost($accountId, $postId)
- getFaqs($accountId)
- searchFaqs($accountId, $query, $page = 1)
- createFaq($accountId, $title, $url = 'faq')
- updateFaq($accountId, $faqId, $title, $url = 'faq')
- deleteFaq($accountId, $faqId)
- getFaqTopics($accountId, $faqId)
- createFaqTopic($accountId, $faqId, $topic, $order = 0)
- updateFaqTopic($accountId, $faqId, $topic, $order = 0)
- deleteFaqTopic($accountId, $faqId, $topicId)
- getTopicQuestions($accountId, $faqId, $topicId)
- createTopicQuestion($accountId, $faqId, $topicId, $question, $answer)
- updateTopicQuestion($accountId, $faqId, $topicId, $questionId, $question, $answer)
- deleteTopicQuestion($accountId, $faqId, $topicId, $questionId)

