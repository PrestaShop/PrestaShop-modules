<?php
/**
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Implements the service Sceau de Confiance including every webservices access methodes
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class Sceau extends SceauService
{

	/**
	 * send an XML stream using sendrating.cgi and method POST and returns the answer of the script as a string
	 *
	 * @param XMLElement $xml
	 * @return string
	 */
	public function sendSendrating(SceauControl &$xml)
	{
		$this->addCrypt($xml);
		SceauLogger::insertLogSceau(__METHOD__, $xml->saveXML());
		$data = array(
			'SiteID' => $this->getSiteId(),
			'XMLInfo' => $xml->saveXML(),
			'CheckSum' => md5($xml->saveXML()),
		);
		$con = new SceauSocket($this->getUrlsendrating(), 'POST', $data);
		$res = $con->send();

		return $res;
	}

	/**
	 * generates and returns the crypt value for the order $order
	 *
	 * @param XMLElement $order
	 * @return string
	 */
	public function generateCrypt(SceauControl $order)
	{
		$refid = $order->getOneElementByTagName('refid')->nodeValue;
		$timestamp = $order->getOneElementByTagName('ip')->getAttribute('timestamp');
		$email = $order->getOneElementByTagName('email')->nodeValue;
		$secure_string = $this->getAuthkey().'_'.$refid.'+'.$timestamp.'='.Tools::strtolower($email);
		$crypt_value = md5($secure_string);
		return $crypt_value;
	}

	/**
	 * adds the element <crypt> into the stream if it has no element <crypt> already, do nothing otherwise
	 *
	 * @param XMLElement $order 
	 */
	public function addCrypt(SceauControl &$order)
	{
		$crypt = $order->getOneElementByTagName('crypt');
		if (is_null($crypt))
			$order->createChild('crypt', $this->generateCrypt($order), array());
		else
			$crypt->node_value = $this->generateCrypt($order);
	}

}