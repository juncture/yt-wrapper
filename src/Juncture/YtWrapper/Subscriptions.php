<?php namespace Juncture\YtWrapper;

class Subscriptions extends YtWrapper {

	private $filters = [];

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

	public function get($dataTypes)
	{
		if (empty($this->filters))
		{
			throw new Exception("Subscriptions: Missing required search filters", 1);

		}

		$options = $this->filters;

		return $this->yt->subscriptions->listSubscriptions($dataTypes, $options);
	}
}
