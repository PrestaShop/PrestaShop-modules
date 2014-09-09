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

header('Content-Type: text/html; charset=utf-8');

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');

if (class_exists('SeurLib') == false)
	include_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');

$token = Tools::getValue('token');
$admin_token = Tools::getAdminToken('AdminModules'.(int)Tab::getIdFromClassName('AdminModules').(int)Tools::getValue('id_employee'));

if ($token != $admin_token)
	exit;

try
{
	$sc_options = array(
		'connection_timeout' => 30
	);

	$postcode = Tools::getValue('post_code');
	if (!Validate::isPostCode($postcode))
		return false;

	$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_SP'), $sc_options);
	$data = array(
		'in0' => '',
		'in1' => '',
		'in2' => $postcode,
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
		$towns = array();

		if ($xml->attributes()->NUM[0] > 1)
		{
			for ($i = 1; $i <= $xml->attributes()->NUM[0]; $i++)
			{
				$reg = 'REG'.$i;
				$towns['towns'][] = utf8_decode($xml->$reg->NOM_POBLACION);
			}
		}
		elseif ($xml->attributes()->NUM[0] == 1)
			$towns['towns'][] = utf8_decode((string)$xml->REG1->NOM_POBLACION);

		$towns['state'] = utf8_decode((string)$xml->REG1->NOM_PROVINCIA);
		$towns['iso'] = (string)$xml->REG1->COD_PAIS_ISO;
		$towns['franchise'] = (string)$xml->REG1->COD_UNIDAD_ADMIN;

		echo Tools::jsonEncode($towns);
	}
}
catch (PrestaShopException $e)
{
	$e->displayMessage();
}