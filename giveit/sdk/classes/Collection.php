<?php
/**
 * 2013 Give.it
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@give.it so we can send you a copy immediately.
 *
 * @author    JSC INVERTUS www.invertus.lt <help@invertus.lt>
 * @copyright 2013 Give.it
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of Give.it
 */

class GiveItSdkCollection {
	public $type;

	private $client;
	private $data;
	private $pages;

	public function __construct($type)
	{
		$this->type = $type;

		$this->setupClient();

		$this->pages = (object)array('size' => 10, 'current' => 1, );
	}

	private function setupClient()
	{
		$this->client = GiveItSdkClient::getInstance();
	}

	private function getBaseURL()
	{
		return '/'.Tools::strtolower($this->type).'s';
	}

	public function setLimit($limit)
	{
		$this->pages->size = $limit;
	}

	public function addFilter($name, $value)
	{
		$this->filters[] = "$name=$value";
	}

	public function getPage($page_number = 1)
	{
		$options = array('page' => $page_number, );

		$url = $this->buildURL($options);
		$response = $this->client->sendGET($url);

		return $this->parseCollectionResponse($response);

	}

	public function nextPage()
	{
		if (!$this->pages->next)
			return false;

		return $this->getPage($this->pages->next);
	}

	public function previousPage()
	{
		return $this->getPage($this->pages->previous);
	}

	public function buildURL($override_options = array())
	{
		$url = $this->getBaseURL();

		$options = array('limit' => $this->pages->size, 'page' => $this->pages->current, );

		foreach ($override_options as $key => $val)
		{
			if (isset($options[$key]))
				$options[$key] = $val;
		}

		$url .= '?'.http_build_query($options);

		return $url;
	}

	private function parseCollectionResponse($response)
	{
		if (!is_object($response))
			return false;

		$this->pages = $response->pages;
		$this->data = $response->data;

		return $this->data;
	}

	public function all()
	{
		$url = $this->buildURL();
		$response = $this->client->sendGET($url);

		return $this->parseCollectionResponse($response);
	}

	public function since($date)
	{
		if ($date == 'yesterday')
			$date = date('Y-m-d', strtotime('yesterday'));

		$url = $this->getBaseURL()."?filter:created_at=]$date";

		$collection = $this->client->sendGET($url);

		return $collection;

	}

	public function get($id)
	{
		$url = $this->getBaseURL()."/$id";

		$response = $this->client->sendGET($url);

		// TODO: error checking - valid response? no errors?

		$class = 'GiveItSdk'.$this->type;

		$object = new $class($response);

		return $object;
	}

}
