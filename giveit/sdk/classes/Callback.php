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

class GiveItSdkCallback {
	public function __construct()
	{
		$this->parent = GiveItSdk::getInstance();
		$this->crypt = GiveItSdkCrypt::getInstance();
	}

	protected function decodeJson($json)
	{
		$json = preg_replace('/[^a-zA-Z0-9\s\p{P}]/', null, $json);

		return Tools::jsonDecode($json);
	}

}

class GiveItSdkCallbackSale extends GiveItSdkCallback {
	public function parse($data)
	{
		print_r($data);

		$json = $this->crypt->decode($data['data'], $this->parent->data_key);
		$sale = $this->decodeJson($json);

		switch (json_last_error())
		{
			case JSON_ERROR_NONE :
				break;

			case JSON_ERROR_DEPTH :
				echo ' - Maximum stack depth exceeded';
				break;

			case JSON_ERROR_STATE_MISMATCH :
				echo ' - Underflow or the modes mismatch';
				break;

			case JSON_ERROR_CTRL_CHAR :
				echo ' - Unexpected control character found';
				break;

			case JSON_ERROR_SYNTAX :
				echo ' - Syntax error, malformed JSON';
				break;

			case JSON_ERROR_UTF8 :
				echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
				break;

			default :
				echo ' - Unknown error';
				break;
		}

		return $sale;
	}

}
