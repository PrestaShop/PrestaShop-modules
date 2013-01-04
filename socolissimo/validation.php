<?php
/*
* 2007-2013 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include('../../config/config.inc.php');
include('../../init.php');
require_once(_PS_MODULE_DIR_.'socolissimo/classes/SCFields.php');

// Init the Context (inherit Socolissimo and handle error)
$so = new SCFields(Tools::getValue('DELIVERYMODE'));

// Init the Display
$display = new BWDisplay();
$display->setTemplate(dirname(__FILE__).'/error.tpl');

$errors_list = array();
$redirect = __PS_BASE_URI__.((_PS_VERSION_ < '1.5') ? 'order.php?' : 'index.php?controller=order&');
$so->context->smarty->assign('so_url_back', $redirect);

$return = array();

// If error code not defined or empty / null
$errors_codes = ($tab = Tools::getValue('ERRORCODE')) ? explode(' ', trim($tab)) : array();

// If no required error code, start to get the POST data
if (!$so->checkErrors($errors_codes, SCError::REQUIRED))
{
	foreach ($_POST AS $key => $val)
		if ($so->isAvailableFields($key))
			$return[strtoupper($key)] = utf8_encode(urldecode(stripslashes($val)));
	
	// GET parameter, the only one
	$return['TRRETURNURLKO'] = Tools::getValue('TRRETURNURLKO');

	foreach ($so->getFields(SCFields::REQUIRED) as $field)
		if (!isset($return[$field]))
			$errors_list[] = $so->l('This key is required for Socolissimo:').$field;
}
else
	foreach($errors_codes as $code)
		$errors_list[] = $so->l('Error code:').' '.$so->getError($code);

if (empty($errors_list))
{
	if ($so->isCorrectSignKey(Tools::getValue('SIGNATURE'), $return) &&
			$so->context->cart->id && saveOrderShippingDetails($so->context->cart->id, (int)($return['TRCLIENTNUMBER']),$return, $so))
	{
		$TRPARAMPLUS = explode('|', Tools::getValue('TRPARAMPLUS'));
		
		if (count($TRPARAMPLUS) > 1)
		{
			$so->context->cart->id_carrier = (int)$TRPARAMPLUS[0];
			$so->context->cart->gift = (int)$TRPARAMPLUS[1];
		}
                elseif (count($TRPARAMPLUS) == 1)
                {
                    $so->context->cart->id_carrier = (int) $TRPARAMPLUS[0];
                }

		if ((int)$so->context->cart->gift && Validate::isMessage($TRPARAMPLUS[2]))
			$so->context->cart->gift_message = strip_tags($TRPARAMPLUS[2]);

		if (!$so->context->cart->update())
			$errors_list[] = $so->l('Cart can\'t be updated. Please try again your selection');
		else
			Tools::redirect($redirect.'step=3&cgv=1&id_carrier=' . $so->context->cart->id_carrier);
	}
	else
		$errors_list[] = $so->getError('999');
}

$so->context->smarty->assign('error_list', $errors_list);
$display->run();

function saveOrderShippingDetails($idCart, $idCustomer, $soParams, $so_object)
{
	$deliveryMode = array(
		'DOM' => 'Livraison à domicile', 'BPR' => 'Livraison en Bureau de Poste',
		'A2P' => 'Livraison Commerce de proximité', 'MRL' => 'Livraison Commerce de proximité',
		'CIT' => 'Livraison en Cityssimo', 'ACP' => 'Agence ColiPoste', 'CDI' => 'Centre de distribution',
		'RDV' => 'Livraison sur Rendez-vous');

	$db = Db::getInstance();
	$db->executeS('SELECT * FROM '._DB_PREFIX_.'socolissimo_delivery_info WHERE id_cart = '.(int)($idCart).' AND id_customer ='.(int)($idCustomer));
	$numRows = (int)($db->NumRows());
	if ($numRows == 0)
	{
		$sql = 'INSERT INTO '._DB_PREFIX_.'socolissimo_delivery_info
			( `id_cart`, `id_customer`, `delivery_mode`, `prid`, `prname`, `prfirstname`, `prcompladress`,
			`pradress1`, `pradress2`, `pradress3`, `pradress4`, `przipcode`, `prtown`, `cephonenumber`, `ceemail` ,
			`cecompanyname`, `cedeliveryinformation`, `cedoorcode1`, `cedoorcode2`)
			VALUES ('.(int)($idCart).','.(int)($idCustomer).',';
		if ($so_object->delivery_mode == SCFields::RELAY_POINT)
			$sql .= '\''.pSQL($soParams['DELIVERYMODE']).'\''.',
					'.(isset($soParams['PRID']) ? '\''.pSQL($soParams['PRID']).'\'' : '\'\'').',
					'.(isset($soParams['PRNAME']) ? '\''.pSQL($soParams['PRNAME']).'\'' : '\'\'').',
					'.(isset($deliveryMode[$soParams['DELIVERYMODE']]) ? '\''.$deliveryMode[$soParams['DELIVERYMODE']].'\'' : '\'So Colissimo\'').',
					'.(isset($soParams['PRCOMPLADRESS']) ? pSQL($soParams['PRCOMPLADRESS']) : '\'\'').',			
					'.(isset($soParams['PRADRESS1']) ? '\''.pSQL($soParams['PRADRESS1']).'\'' : '\'\'').',
					'.(isset($soParams['PRADRESS2']) ? '\''.pSQL($soParams['PRADRESS2']).'\'' : '\'\'').',
					'.(isset($soParams['PRADRESS3']) ? '\''.pSQL($soParams['PRADRESS3']).'\'' : '\'\'').',
					'.(isset($soParams['PRADRESS4']) ? '\''.pSQL($soParams['PRADRESS4']).'\'' : '\'\'').',
					'.(isset($soParams['PRZIPCODE']) ? '\''.pSQL($soParams['PRZIPCODE']).'\'' : '\'\'').',
					'.(isset($soParams['PRTOWN']) ? '\''.pSQL($soParams['PRTOWN']).'\'' : '\'\'').',
					'.(isset($soParams['CEPHONENUMBER']) ? '\''.pSQL($soParams['CEPHONENUMBER']).'\'' : '\'\'').',
					'.(isset($soParams['CEEMAIL']) ? '\''.pSQL($soParams['CEEMAIL']).'\'' : '\'\'').',
					'.(isset($soParams['CECOMPANYNAME']) ? '\''.pSQL($soParams['CECOMPANYNAME']).'\'' : '\'\'').',
					'.(isset($soParams['CEDELIVERYINFORMATION']) ? '\''.pSQL($soParams['CEDELIVERYINFORMATION']).'\'' : '\'\'').',
					'.(isset($soParams['CEDOORCODE1']) ? '\''.pSQL($soParams['CEDOORCODE1']).'\'' : '\'\'').',
					'.(isset($soParams['CEDOORCODE2']) ? '\''.pSQL($soParams['CEDOORCODE2']).'\'' : '\'\'').')';
		else
			$sql .= '\''.pSQL($soParams['DELIVERYMODE']).'\',\'\',
					'.(isset($soParams['CENAME']) ? '\''.ucfirst(pSQL($soParams['CENAME'])).'\'' : '\'\'').',
					'.(isset($soParams['CEFIRSTNAME']) ? '\''.ucfirst(pSQL($soParams['CEFIRSTNAME'])).'\'' : '\'\'').',
					'.(isset($soParams['CECOMPLADRESS']) ? '\''.pSQL($soParams['CECOMPLADRESS']).'\'' : '\'\'').',
					'.(isset($soParams['CEADRESS1']) ? '\''.pSQL($soParams['CEADRESS1']).'\'' : '\'\'').',
					'.(isset($soParams['CEADRESS2']) ? '\''.pSQL($soParams['CEADRESS2']).'\'' : '\'\'').',
					'.(isset($soParams['CEADRESS3']) ? '\''.pSQL($soParams['CEADRESS3']).'\'' : '\'\'').',
					'.(isset($soParams['CEADRESS4']) ? '\''.pSQL($soParams['CEADRESS4']).'\'' : '\'\'').',
					'.(isset($soParams['CEZIPCODE']) ? '\''.pSQL($soParams['CEZIPCODE']).'\'' : '\'\'').',
					'.(isset($soParams['CETOWN']) ? '\''.pSQL($soParams['CETOWN']).'\'' : '\'\'').',
					'.(isset($soParams['CEPHONENUMBER']) ? '\''.pSQL($soParams['CEPHONENUMBER']).'\'' : '\'\'').',
					'.(isset($soParams['CEEMAIL']) ? '\''.pSQL($soParams['CEEMAIL']).'\'' : '\'\'').',
					'.(isset($soParams['CECOMPANYNAME']) ? '\''.pSQL($soParams['CECOMPANYNAME']).'\'' : '\'\'').',
					'.(isset($soParams['CEDELIVERYINFORMATION']) ? '\''.pSQL($soParams['CEDELIVERYINFORMATION']).'\'' : '\'\'').',
					'.(isset($soParams['CEDOORCODE1']) ? '\''.pSQL($soParams['CEDOORCODE1']).'\'' : '\'\'').',
					'.(isset($soParams['CEDOORCODE2']) ? '\''.pSQL($soParams['CEDOORCODE2']).'\'' : '\'\'').')';

		if (Db::getInstance()->execute($sql))
			return true;
	}
	else
	{
		$table = _DB_PREFIX_.'socolissimo_delivery_info';
		$values = array();
		$values['delivery_mode'] = pSQL($soParams['DELIVERYMODE']);

		if ($so_object->delivery_mode == SCFields::RELAY_POINT)
		{
			isset($soParams['PRID']) ? $values['prid'] = pSQL($soParams['PRID']) : '';
			isset($soParams['PRNAME']) ? $values['prname'] = ucfirst(pSQL($soParams['PRNAME'])) : '';
			isset($deliveryMode[$soParams['DELIVERYMODE']]) ? $values['prfirstname'] = $deliveryMode[$soParams['DELIVERYMODE']] : $values['prfirstname'] = 'So Colissimo';
			isset($soParams['PRCOMPLADRESS']) ? $values['prcompladress'] = pSQL($soParams['PRCOMPLADRESS']) : '';
			isset($soParams['PRADRESS1']) ? $values['pradress1'] = pSQL($soParams['PRADRESS1']) : '';
			isset($soParams['PRADRESS2']) ? $values['pradress2'] = pSQL($soParams['PRADRESS2']) : '';
			isset($soParams['PRADRESS3']) ? $values['pradress3'] = pSQL($soParams['PRADRESS3']) : '';
			isset($soParams['PRADRESS4']) ? $values['pradress4'] = pSQL($soParams['PRADRESS4']) : '';
			isset($soParams['PRZIPCODE']) ? $values['przipcode'] = pSQL($soParams['PRZIPCODE']) : '';
			isset($soParams['CETOWN']) ? $values['prtown'] = pSQL($soParams['CETOWN']) : '';
			isset($soParams['CEPHONENUMBER']) ? $values['cephonenumber'] = pSQL($soParams['CEPHONENUMBER']) : '';
			isset($soParams['CEEMAIL']) ? $values['ceemail'] = pSQL($soParams['CEEMAIL']) : '';
			isset($soParams['CEDELIVERYINFORMATION']) ? $values['cedeliveryinformation'] = pSQL($soParams['CEDELIVERYINFORMATION']) : '';
			isset($soParams['CEDOORCODE1']) ? $values['cedoorcode1'] = pSQL($soParams['CEDOORCODE1']) : '';
			isset($soParams['CEDOORCODE2']) ? $values['cedoorcode2'] = pSQL($soParams['CEDOORCODE2']) : '';
			isset($soParams['CECOMPANYNAME']) ? $values['cecompanyname'] = pSQL($soParams['CECOMPANYNAME']) : '';
		}
		else
		{
			isset($soParams['PRID']) ? $values['prid'] = pSQL($soParams['PRID']) : $values['prid'] = '';
			isset($soParams['CENAME']) ? $values['prname'] = ucfirst(pSQL($soParams['CENAME'])) : '';
			isset($soParams['CEFIRSTNAME']) ? $values['prfirstname'] = ucfirst(pSQL($soParams['CEFIRSTNAME'])) : '';
			isset($soParams['CECOMPLADRESS']) ? $values['prcompladress'] = pSQL($soParams['CECOMPLADRESS']) : '';
			isset($soParams['CEADRESS1']) ? $values['pradress1'] = pSQL($soParams['CEADRESS1']) : '';
			isset($soParams['CEADRESS2']) ? $values['pradress2'] = pSQL($soParams['CEADRESS2']) : '';
			isset($soParams['CEADRESS3']) ? $values['pradress3'] = pSQL($soParams['CEADRESS3']) : '';
			isset($soParams['CEADRESS4']) ? $values['pradress4'] = pSQL($soParams['CEADRESS4']) : '';
			isset($soParams['CEZIPCODE']) ? $values['przipcode'] = pSQL($soParams['CEZIPCODE']) : '';
			isset($soParams['PRTOWN']) ? $values['prtown'] = pSQL($soParams['PRTOWN']) : '';
			isset($soParams['CEEMAIL']) ? $values['ceemail'] = pSQL($soParams['CEEMAIL']) : '';
			isset($soParams['CEPHONENUMBER']) ? $values['cephonenumber'] = pSQL($soParams['CEPHONENUMBER']) : '';
			isset($soParams['CEDELIVERYINFORMATION']) ? $values['cedeliveryinformation'] = pSQL($soParams['CEDELIVERYINFORMATION']) : '';
			isset($soParams['CEDOORCODE1']) ? $values['cedoorcode1'] = pSQL($soParams['CEDOORCODE1']) : '';
			isset($soParams['CEDOORCODE2']) ? $values['cedoorcode2'] = pSQL($soParams['CEDOORCODE2']) : '';
			isset($soParams['CECOMPANYNAME']) ? $values['cecompanyname'] = pSQL($soParams['CECOMPANYNAME']) : '';
		}
		$where = ' `id_cart` =\''.(int)($idCart).'\' AND `id_customer` =\''.(int)($idCustomer).'\'';

		if (Db::getInstance()->autoExecute($table, $values, 'UPDATE', $where))
			return true;
	}
}
