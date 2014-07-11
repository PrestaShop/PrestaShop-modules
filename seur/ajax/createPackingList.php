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
$admin_token_15 = Tools::getAdminToken('AdminSeur15'.(int)Tab::getIdFromClassName('AdminSeur15').(int)Tools::getValue('id_employee'));

if (($token != $admin_token) && ($token != $admin_token_15))
	exit;

$back = Tools::getValue('back');
if (!Validate::isUrl($back))
	exit;

try
{
	$sc_options = array(
		'connection_timeout' => 30
	);

	$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_M'), $sc_options);
	$merchant_data = SeurLib::getMerchantData();

	$data = array(
		'in0' => $merchant_data['nif_dni'],
		'in1' => $merchant_data['franchise'],
		'in2' => $merchant_data['user'],
		'in3' => $merchant_data['pass']
	);
	$response = $soap_client->generacionPDFDetalleNoFecha($data);

	if ($response->out == 'NO SE PUDIERON RECUPERAR DATOS PARA LA GENERACION DEL MANIFIESTO')
	{
		$url = $back.'&token='.urlencode(Tools::getValue('token')).'&codigo=-1&error='.urlencode((string)$response->out);
		die(Tools::redirectAdmin($url));
	}
	elseif ($response->out == 'ERROR USUARIO/PASSWORD ERRONEOS')
	{
		$url = $back.'&token='.urlencode(Tools::getValue('token')).'&codigo=-1&error='.urlencode((string)$response->out);
		die(Tools::redirectAdmin($url));
	}
	else
	{
		$pdf = base64_decode($response->out);
		ob_end_clean();
		header('Content-type: application/pdf');
		header('Content-Disposition: inline; filename="manifiesto_'.date('d-m-Y').'".pdf"');
		echo $pdf;
	}
}
catch (SoapFault $fault)
{
	$url = $back.'&token='.urlencode(Tools::getValue('token')).'&codigo=Error&error='.urlencode($fault->getMessage());
	die(Tools::redirect($url));
}