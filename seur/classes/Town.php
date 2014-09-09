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
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Town
{
	public static function getTowns($postal_code)
	{
		try
		{
			$sc_options = array(
				'connection_timeout' => 30
			);

			$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_SP'), $sc_options);

			$data = array(
				'in0' => '',
				'in1' => '',
				'in2' => $postal_code,
				'in3' => '',
				'in4' => '',
				'in5' => Configuration::get('SEUR_WS_USERNAME'),
				'in6' => Configuration::get('SEUR_WS_PASSWORD')
			);

			$response = $soap_client->infoPoblacionesCortoStr($data);

			if (empty($response->out))
				return false;
			else
			{
				$string_xml = htmlspecialchars_decode((($response->out)));
				$xml = simplexml_load_string($string_xml);
				return $xml;
			}
		}
		catch (PrestaShopException $e)
		{
			$e->displayMessage();
		}
	}
}