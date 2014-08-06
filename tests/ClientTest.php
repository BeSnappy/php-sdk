<?php

use Mockery as m;

class ClientTest extends PHPUnit_Framework_TestCase {

	public function testSendMessageCreatesProperPayload()
	{
		$client = $this->getMock('SnappySdk\Client', array('getHttp', 'sendPlain'), array($auth = m::mock('SnappySdk\AuthInterface')));
		$client->expects($this->once())->method('getHttp')->will($this->returnValue($guzzle = m::mock('StdClass')));
		$client->expects($this->once())->method('sendPlain');

		$guzzle->shouldReceive('post')->once()->with('https://app.besnappy.com/api/v1/note', null, json_encode(array(
			'mailbox_id' => 1,
			'staff_id' => 2,
			'id' => null,
			'subject' => 'Subject',
			'message' => 'Message',
			'attachments' => array(),
			'tags' => array('foo', 'bar'),
			'scope' => 'public',
			'status' => null,
			'to' => array(array('name' => 'Taylor Otwell', 'address' => 'taylor@userscape.com')),
			'contact_extras' => array('paid' => true),
		)))->andReturn(m::mock('Guzzle\Http\Message\Request'));

		$message = new SnappySdk\Message;
		$message->mailboxId = 1;
		$message->staffId = 2;
		$message->subject = 'Subject';
		$message->message = 'Message';
		$message->tags = array('foo', 'bar');
		$message->setContactExtras(array('paid' => true));
		$message->addTo('taylor@userscape.com', 'Taylor Otwell');

		$client->sendMessage($message);
	}


	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testClientThrowsExceptionOnInvalidEmails()
	{
		$client = new SnappySdk\Client(m::mock('SnappySdk\AuthInterface'));
		$message = new SnappySdk\Message;
		$message->mailboxId = 1;
		$message->staffId = 2;
		$message->subject = 'Subject';
		$message->message = 'Message';
		$message->tags = array('foo', 'bar');
		$message->addTo('?????????? ....', 'Taylor Otwell');

		$client->sendMessage($message);
	}

}