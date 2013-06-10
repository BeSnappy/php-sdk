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
			'to' => array(array('name' => 'Taylor Otwell', 'address' => 'taylor@userscape.com')),
		)))->andReturn(m::mock('Guzzle\Http\Message\Request'));

		$message = new SnappySdk\Message;
		$message->mailboxId = 1;
		$message->staffId = 2;
		$message->subject = 'Subject';
		$message->message = 'Message';
		$message->tags = array('foo', 'bar');
		$message->addTo('taylor@userscape.com', 'Taylor Otwell');

		$client->sendMessage($message);
	}

}