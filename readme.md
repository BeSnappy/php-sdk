# Snappy PHP SDK

## Installation

The SDK should be installed via [Composer](http://getcomposer.org). Simply add the `snappy/php-sdk` dependency to your `composer.json` file.

## Usage

First, create a new authentication provider:

	$auth = new Snappy\PhpSdk\UserPassAuthentication('user', 'pass');

Next, create an SDK client instance:

	$client = new Snappy\PhpSdk\Client($auth);

Now you're ready to start retrieving information from Snappy!

	$accounts = $client->getAccounts();

## List Of Available Methods

- getAccounts
- getMailboxes($accountId)
- getDocuments($accountId)
- getStaff($accountId)
- getWaitingTickets($mailboxId)
- getTicketDetails($ticketId)
- getTicketNotes($ticketId)
- updateTicketTags($ticketId, $tags)
- sendMessage($message)

## Sending Messages

To send a message to your Snappy account, create a new `Message` instance, and use the `sendMessage` method on the client:

	$message = new Snappy\PhpSdk\Message;

	$message->mailboxId = 3;
	$message->setFrom('foo@bar.com', 'John Smith');
	$message->subject = 'Hello World';
	$message->message = 'This is my message!';

	$client->sendMessage($message);

To send an outgoing message, a `staffId` must be specified, as well as the recipient address:

	$message = new Snappy\PhpSdk\Message;

	$message->mailboxId = 3;
	$message->staffId = 3;
	$message->addTo('foo@bar.com', 'John Smith');
	$message->subject = 'Hello World';
	$message->message = 'This is my message!';

	$client->sendMessage($message);