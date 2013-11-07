<?php
/*
*  @author Incentivibe <info@incentivibe.com>
*  @copyright  2012-2013 Incentivibe
*  @version  Release: $Revision: 1.1 $
*
*/

class IncentivibeApi
{
	const INCENTIVIBE_API_URL = 'https://www.incentivibe.com/api/';

	public function __construct()
	{

	}

	public function makeApiCall($action, $params = array())
	{
		switch ($action)
		{
			case 'register':
				$url = self::INCENTIVIBE_API_URL.'users/sign_up';
				$response = $this->makePostRequest($url, $params);
				break;
			case 'login':
				$url = self::INCENTIVIBE_API_URL.'users/sign_in';
				$response = $this->makePostRequest($url, $params);
				break;
		}

		return $response;
	}

	private function makePostRequest($url, $params = array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

		$response = curl_exec($ch);
		curl_close($ch);
		$response = Tools::jsondecode($response, true);

		// ob_start();
		// var_dump($response);
		// $contents = ob_get_contents();
		// ob_end_clean();
		// error_log($contents);

		return $response;
	}

	private function makeGetRequest($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		curl_close($ch);
	}
}
