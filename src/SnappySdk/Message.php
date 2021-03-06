<?php namespace SnappySdk;

class Message {

	/**
	 * The mailbox ID of the message the mailbox is assigned to.
	 *
	 * @var int
	 */
	public $mailboxId;

	/**
	 * The ticket ID (if any) the message should be attached to.
	 *
	 * @var int
	 */
	public $ticketId;

	/**
	 * The staff ID of the person sending the message (if outgoing).
	 *
	 * @var int
	 */
	public $staffId;

	/**
	 * The scope of the message (public or private)
	 *
	 * @var string
	 */
	public $scope;

	/**
	 * The subject of the message.
	 *
	 * @var string
	 */
	public $subject;

	/**
	 * The sender of the message (if incoming).
	 *
	 * @var array
	 */
	public $from;

	/**
	 * Set the extra attributes for the contact.
	 *
	 * @var array
	 */
	public $contactExtras = null;

	/**
	 * The recipients of the message.
	 *
	 * @var array
	 */
	public $to = array();

	/**
	 * The copied recipients of the message.
	 *
	 * @var array
	 */
	public $cc = array();

	/**
	 * The blind copied recipients of the message.
	 *
	 * @var array
	 */
	public $bcc = array();

	/**
	 * The contents of the message.
	 *
	 * @var string
	 */
	public $message;

	/**
	 * The tags to attach to the ticket (if outgoing)
	 *
	 * @var array
	 */
	public $tags = array();

	/**
	 * Indicates if the message should remain in "waiting" state.
	 *
	 * @var bool
	 */
	public $keepWaiting = false;

	/**
	 * Indicates if the message is a "system" message.
	 */
	public $system = false;

	/**
	 * Set the sender of the message.
	 *
	 * @param  string  $name
	 * @param  string  $address
	 * @return \SnappySdk\Message
	 */
	public function setFrom($address, $name = null)
	{
		if (is_null($name)) $name = $address;

		$this->from = compact('name', 'address');

		return $this;
	}

	/**
	 * Set the extra attributes for the contacts.
	 *
	 * @param  array  $extras
	 * @return \SnappySdk\Message
	 */
	public function setContactExtras(array $extras = array())
	{
		$this->contactExtras = $extras;

		return $this;
	}

	/**
	 * Add a recipient to the message.
	 *
	 * @param  string  $address
	 * @param  string  $name
	 * @return \SnappySdk\Message
	 */
	public function addTo($address, $name = null)
	{
		return $this->addRecipient('to', $address, $name);
	}

	/**
	 * Add a copied recipient to the message.
	 *
	 * @param  string  $address
	 * @param  string  $name
	 * @return \Snappy\Message
	 */
	public function addCc($address, $name = null)
	{
		return $this->addRecipient('cc', $address, $name);
	}

	/**
	 * Add a blind copied recipient to the message.
	 *
	 * @param  string  $address
	 * @param  string  $name
	 * @return \SnappySdk\Message
	 */
	public function addBcc($address, $name = null)
	{
		return $this->addRecipient('bcc', $address, $name);
	}

	/**
	 * Add a recipient to the messgae.
	 *
	 * @param  string  $type
	 * @param  string  $address
	 * @param  string  $name
	 * @return \SnappySdk\Message
	 */
	protected function addRecipient($type, $address, $name)
	{
		if (is_null($name)) $name = $address;

		$this->{$type}[$name] = $address;

		return $this;
	}

}