<?php namespace Juncture\YtWrapper;

class YtWrapper {

	protected $client;

	protected $yt;

	private $subscriptions;

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

	public function where()
	{
		switch (func_num_args())
		{
			case 0:
			{
				throw new Exception("Not enough arguments for where()", 1);
				break;
			}
			case 1:
			{
				$this->setFilters(func_get_arg(0));
				break;
			}
			case 2:
			{
				$key = func_get_arg(0);
				$this->setFilters([$key => func_get_arg(1)]);

				unset($key);
				break;
			}
			default:
				throw new Exception("Too many arguments for where()", 1);
		}

		return $this;
	}

	private function setFilters($filters)
	{
		if ( ! is_array($filters))
		{
			throw new Exception('setFilters requires a key=>val array, '.gettype($filters), 1);
		}

		foreach ($filters as $filter => $value)
		{
			$this->setFilter($filter, $value);
		}

		return $this;
	}

	private function setFilter($filter, $value)
	{
		$this->filters[$filter] = $value;

		return $this;
	}

	public function resetFilters()
	{
		$this->filters = [];

		return $this;
	}

	public function get($dataTypes)
	{
		switch (func_num_args())
		{
			case 1:
				if (empty($this->filters))
				{
					throw new Exception("Subscriptions: Missing required search filters", 1);

				}

				$arg1 = $dataTypes;
				$arg2 = $this->filters;
				break;
			case 2:
				$arg1 = func_get_arg(0);
				$arg2 = func_get_arg(1);
				break;
			default:
				throw new Exception("Error Processing Request", 1);
		}

		$service = $this->yt->{$this->resource};

		$method = 'list'.$this->method;

		return $service->$method($arg1, $arg2);
	}

	public function subscriptions()
	{
		$this->resetFilters();
		$this->method = 'Subscriptions';
		$this->resource = 'subscriptions';

		return $this;
	}
}
