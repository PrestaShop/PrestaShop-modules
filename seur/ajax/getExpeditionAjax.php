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

$module_instance = Module::getInstanceByName('seur');

try
{
	$sc_options = array(
		'connection_timeout' => 30
	);
	$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_E'), $sc_options);

	$nuevadate = strtotime('-15 days', strtotime(date('Y-m-d')));
	$data_merchant = SeurLib::getMerchantData();
	$data = array(
		'in0' => 'S',
		'in1' => ( Tools::getValue('expedition_number') ? pSQL(Tools::getValue('expedition_number')) :'' ), /*numero expedicion*/
		'in2' => '',
		'in3' => ( Tools::getValue('reference_number') ? pSQL(Tools::getValue('reference_number')) :'' ), /*numero referencia*/
		'in4' => pSQL($data_merchant['ccc']).'-'.pSQL($data_merchant['franchise']),
		'in5' => ( !Tools::getValue('start_date') ? date('d-m-Y', $nuevadate) : Tools::getValue('start_date')),
		'in6' => ( !Tools::getValue('end_date') ? date('d-m-Y', $nuevadate) : Tools::getValue('end_date')),
		'in7' => (  Tools::getValue('order_state') ? Tools::getValue('order_state') : '' ),
		'in8' => '',
		'in9' => '',
		'in10' => '',
		'in11' => '',
		'in12' => Configuration::get('SEUR_WS_USERNAME'),
		'in13' => Configuration::get('SEUR_WS_PASSWORD'),
		'in14' => 'N'
	);

	$response = $soap_client->consultaListadoExpedicionesStr($data);

	if (empty($response->out))
	{
		echo SeurLib::displayErrors ($module_instance->l('No results.', 'getExpeditionAjax'));
		return false;
	}

	$string_xml = htmlspecialchars_decode( $response->out );
	$xml = simplexml_load_string( $string_xml );
	$delivery = $xml->EXPEDICION;

	$context = Context::getContext();
	$context->smarty->assign('delivery', $delivery);
	$context->smarty->display(_PS_MODULE_DIR_.'seur/views/templates/admin/get_expedition.tpl');
}
catch (PrestaShopException $e)
{
	$e->displayMessage();
}