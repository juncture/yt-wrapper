<?php namespace Juncture\YtWrapper;

class YtWrapper {

	protected $client;

	protected $yt;

	private $subscriptions = new Subscriptions();

	public function init($config)
	{
		$this->initGoogleService();

		$this->setClientConfig($config);

		$this->initYoutubeService();
	}

	protected function initGoogleService()
	{
		if (is_null($this->client))
		{
			$this->client = new \Google_Client();
		}
	}

	public function setClientConfig(\stdClass $config)
	{
		$this->client->setDeveloperKey($config->developerKey);
		$this->client->setClientId($config->clientId);
		$this->client->setClientSecret($config->clientSecret);

		$this->client->setRedirectUri(\URL::to('oauth'));
	}

	protected function initYoutubeService()
	{
		if (is_null($this->yt))
		{
			$this->client->loadService('YouTube');

			$this->yt = new \Google_YoutubeService($this->client);
		}
	}

	public function setToken($token)
	{
		if ($token)
		{
			$this->client->setAccessToken($token);
		}

		return $this->setState();
	}

	private function setState()
	{
		if ( ! $this->client->getAccessToken())
		{
			$state = mt_rand();
			$this->client->setState($state);
			\Session::put('state', $state);

			return false;
		}

		return true;
	}

	public function getAuthUrl()
	{
		return $this->client->createAuthUrl();
	}

	public function auth()
	{
		$this->authCheckCode(\Input::get('code'));

		$this->authCheckState(\Input::get('state'));

		$this->client->authenticate();

		return $this->client->getAccessToken();
	}

	private function authCheckCode($code)
	{
		if ( ! $code)
		{
			throw new \Exception('Missing code');
		}

		return true;
	}

	private function authCheckState($state)
	{
		if (strval(\Session::get('state')) !== strval($state))
		{
			throw new \Exception('Session state did not match');
		}

		return true;
	}

	public function subscriptions()
	{
		return $this->subscriptions;
	}
}
