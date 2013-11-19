<?php namespace SnappySdk;

use Guzzle\Http\Message\Request;

class BasicAuthentication implements AuthInterface {

	/**
	 * The API key, available via the You > Your Settings menu in Snappy.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * Create a new basic authentication instance.
	 *
	 * @param  string  $key
	 * @return void
	 */ 
	public function __construct($key)
	{
		$this->key = $key;
	}

	/**
	 * Add the authentication credentials to a request.
	 *
	 * @param  \Guzzle\Http\Message\Request  $request
	 * @return \Guzzle\Http\Message\Request
	 */
	public function addCredentialsToRequest(Request $request)
	{
		$request->setAuth($this->key, 'x', 'basic');

		return $request;
	}

}