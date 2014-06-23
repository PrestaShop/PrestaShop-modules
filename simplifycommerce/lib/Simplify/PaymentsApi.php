<?php
/**
 * Simplify Commerce module to start accepting payments now. It's that simple.
 *
 * Redistribution and use in source and binary forms, with or without modification, are 
 * permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of 
 * conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * Neither the name of the MasterCard International Incorporated nor the names of its 
 * contributors may be used to endorse or promote products derived from this software 
 * without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS' AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES 
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT 
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; 
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER 
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING 
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF 
 * SUCH DAMAGE.
 *
 *  @author    MasterCard (support@simplify.com)
 *  @version   Release: 1.0.1
 *  @copyright 2014, MasterCard International Incorporated. All rights reserved. 
 *  @license   See licence.txt
 */

class SimplifyPaymentsApi
{

	/**
	 * @ignore
	 */
	public static $method_map = array(
		'create' => 'POST',
		'delete' => 'DELETE',
		'list' => 'GET',
		'show' => 'GET',
		'update' => 'PUT'
	);

	/**
	 * @ignore
	 */
	public static function createObject($object, $authentication = null)
	{
		$payments_api = new SimplifyPaymentsApi();

		$json_object = $payments_api->execute('create', $object, $authentication);

		$o = $payments_api->convertFromHashToObject($json_object, $object->getClazz());

		return $o;
	}

	/**
	 * @ignore
	 */
	public static function findObject($object, $authentication = null)
	{
		$payments_api = new SimplifyPaymentsApi();

		$json_object = $payments_api->execute('show', $object, $authentication);
		$o = $payments_api->convertFromHashToObject($json_object, $object->getClazz());

		return $o;
	}

	/**
	 * @ignore
	 */
	public static function updateObject($object, $authentication = null)
	{
		$payments_api = new SimplifyPaymentsApi();

		$json_object = $payments_api->execute('update', $object, $authentication);
		$o = $payments_api->convertFromHashToObject($json_object, $object->getClazz());

		return $o;
	}

	/**
	 * @ignore
	 */
	public static function deleteObject($object, $authentication = null)
	{
		$payments_api = new SimplifyPaymentsApi();

		$json_object = $payments_api->execute('delete', $object, $authentication);

		return $json_object;
	}

	/**
	 * @ignore
	 */
	public static function listObject($object, $criteria = null, $authentication = null)
	{
		if ($criteria != null)
		{
			if (isset($criteria['max']))
				$object->max = $criteria['max'];
			if (isset($criteria['offset']))
				$object->offset = $criteria['offset'];
			if (isset($criteria['sorting']))
				$object->sorting = $criteria['sorting'];
			if (isset($criteria['filter']))
				$object->filter = $criteria['filter'];
		}

		$payments_api = new SimplifyPaymentsApi();
		$json_object = $payments_api->execute('list', $object, $authentication);

		$ret = new SimplifyResourceList();
		if (array_key_exists('list', $json_object) & is_array($json_object['list']))
		{
			foreach ($json_object['list'] as $obj)
				array_push($ret->list, $payments_api->convertFromHashToObject($obj, $object->getClazz()));

			$ret->total = $json_object['total'];
		}

		return $ret;
	}

	/**
	 * @ignore
	 */
	public function convertFromHashToObject($from, $to_clazz)
	{
		$clazz = 'stdClass';
		$to_clazz = 'Simplify'.$to_clazz;
		if ('stdClass' != $to_clazz && class_exists("{$to_clazz}", false))
			$clazz = "{$to_clazz}";

		$object = new $clazz();

		foreach ($from as $key => $value)
		{
			if (is_array($value) && count(array_keys($value)))
			{
				$new_clazz = 'Simplify'.Tools::ucfirst($key);

				if (!class_exists($new_clazz, false))
					$new_clazz = 'stdClass';

				$object->$key = $this->convertFromHashToObject($value, $new_clazz);
			}
			else
				$object->$key = $value;
		}

		return $object;
	}

	/**
	 * @ignore
	 */
	public function getUrl($public_key, $action, $object)
	{
		$url = $this->fixUrl(Simplify::$api_base_sandbox_url);
		if ($this->isLiveKey($public_key))
			$url = $this->fixUrl(Simplify::$api_base_live_url);

		$url = $this->fixUrl($url).urlencode(lcfirst($object->getClazz())).'/';

		$query_params = array();
		if ($action == 'show')
			$url .= urlencode($object->id);
		elseif ($action == 'list')
		{
			$query_params = array_merge($query_params, array('max' => $object->max, 'offset' => $object->offset));
			if (is_array($object->filter) && count(array_keys($object->filter)))
			{
				foreach ($object->filter as $key => $value)
					$query_params["filter[$key]"] = $value;
			}
			if (is_array($object->sorting) && count(array_keys($object->sorting)))
			{
				foreach ($object->sorting as $key => $value)
					$query_params["sorting[$key]"] = $value;
			}
			$query = http_build_query($query_params);
			if ($query != '')
			{
				if (strpos($url, '?', Tools::strlen($url)) === false) $url .= '?';
				$url .= $query;
			}

		}
		elseif ($action == 'delete')
			$url .= urlencode($object->id);
		elseif ($action == 'update')
			$url .= urlencode($object->id);

		return $url;
	}

	/**
	 * @ignore
	 */
	public function getMethod($action)
	{
		if (array_key_exists(Tools::strtolower($action), self::$method_map))
			return self::$method_map[Tools::strtolower($action)];

		return 'GET';
	}

	/**
	 * @ignore
	 */
	private function execute($action, $object, $authentication)
	{
		$http = new SimplifyHTTP();

		return $http->apiRequest($this->getUrl($authentication->public_key, $action, $object), $this->getMethod($action),
			$authentication, Tools::jsonEncode($object->getProperties()));
	}

	/**
	 * @ignore
	 */
	public function jwsDecode($hash, $authentication)
	{
		$http = new SimplifyHTTP();

		$data = $http->jwsDecode($authentication, $hash);

		return Tools::jsonDecode($data, true);
	}

	/**
	 * @ignore
	 */
	private function fixUrl($url)
	{
		if ($this->endsWith($url, '/'))
			return $url;

		return $url.'/';
	}

	/**
	 * @ignore
	 */
	private function isLiveKey($k)
	{
		return strpos($k, 'lvpb') === 0;
	}

	/**
	 * @ignore
	 */
	private function endsWith($s, $c)
	{
		return Tools::substr($s, -Tools::strlen($c)) == $c;
	}

	/**
	 * Helper function to build the Authentication object for backwards compatibility.
	 * An array of all the arguments passed to one of the API functions is checked against what
	 * we expect to received.  If it's greater, then we're assuming that the user is using the older way of
	 * passing the keys. i.e as two separate strings.  We take those two string and create the Authentication object
	 *
	 * @ignore
	 * @param $authentication
	 * @param $args
	 * @param $expected_arg_count
	 * @return SimplifyAuthentication
	 */
	public static function buildAuthenticationObject($authentication = null, $args, $expected_arg_count)
	{
		if (count($args) > $expected_arg_count)
			$authentication = new SimplifyAuthentication($args[$expected_arg_count - 1], $args[$expected_arg_count]);

		if ($authentication == null)
			$authentication = new SimplifyAuthentication();

		// check that the keys have been set, if not use the global keys
		if (empty($authentication->public_key))
			$authentication->public_key = Simplify::$public_key;

		if (empty($authentication->private_key))
			$authentication->private_key = Simplify::$private_key;

		return $authentication;
	}

}

