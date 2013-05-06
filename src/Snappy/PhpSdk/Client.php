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
	public function __construct(AuthInterface $uath)
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
	 * Send the request and return the JSON payload as an array.
	 *
	 * @param  \Guzzle\Http\Message\Request  $request
	 * @param  bool  $json
	 * @return array|string
	 */
	protected function send(Request $request, $json = true)
	{
		$response = $request->send();

		return $json ? json_decode($response->getBody(), true) : $response->getBody();
	}

}