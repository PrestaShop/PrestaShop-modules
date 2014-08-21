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

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');

if (class_exists('SeurLib') == false)
	include_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');

$token = Tools::getValue('token');
$admin_token = Tools::getAdminToken('AdminSeur'.(int)Tab::getIdFromClassName('AdminSeur').(int)Tools::getValue('id_employee'));

if ($token != $admin_token)
	exit;

$current_expedition_number = Tools::getValue('expedition_number');
$current_expedition_number = preg_replace('#[^0-9]#', '', $current_expedition_number);

try
{
	$sc_options = array(
		'connection_timeout' => 30
	);

	$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_A'), $sc_options);

	$data_delivery_note = array(
		'in0' => '',
		'in1' => ($current_expedition_number ? $current_expedition_number : '' ),
		'in2' => '',
		'in3' => '',
		'in4' => '',
		'in5' => Configuration::get('SEUR_WS_USERNAME'),
		'in6' => Configuration::get('SEUR_WS_PASSWORD'),
		'in7' => 'S'
	);

	$response = $soap_client->consultaAlbaranes($data_delivery_note);
	$string_xml = htmlspecialchars_decode((($response->out)));
	$xml = simplexml_load_string($string_xml);

	if ($xml->DESCRIPCION)
	{
		$url = urlencode(Tools::getValue('back')).'&token='.urlencode(Tools::getValue('token')).
			'&codigo='.urlencode($xml->CODIGO).'&error='.urlencode((string)$xml->DESCRIPCION);
		die(Tools::redirect($url));
	}

	if ($current_expedition_number)
	{
		$image = strip_tags($string_xml);
		$image = base64_decode($image);

		header('Content-type: application/octet-stream');
		header("Content-Disposition: attachment; filename=\"{$current_expedition_number}.png\"\n");
		ob_end_clean();
		echo $image;

		file_put_contents('../files/deliveries_notes/'.$current_expedition_number.'.png', $image);
	}
}
catch (SoapFault $fault)
{
	$url = urlencode(Tools::getValue('back')).'&token='.urlencode(Tools::getValue('token')).'&codigo=Error&error='.urlencode($fault->getMessage());
	die(Tools::redirect($url));
}