<?php namespace Snappy\PhpSdk;

use Guzzle\Http\Message\Request;

class UserPassAuthentication implements AuthInterface {

	/**
	 * The username.
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * The password.
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Create a new user / password authentication instance.
	 *
	 * @param  string  $username
	 * @param  string  $password
	 * @return void
	 */
	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * Add the authentication credentials to a request.
	 *
	 * @param  \Guzzle\Http\Message\Request  $request
	 * @return \Guzzle\Http\Message\Request
	 */
	public function addCredentialsToRequest(Request $request)
	{
		$request->setAuth($this->username, $this->password);

		return $request;
	}

}