<?php

/*
 * 2007-2014 PrestaShop
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
 *  @author Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright  2007-2014 PrestaShop SA / 1997-2013 Quadra Informatique
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include('../../config/config.inc.php');
include('../../init.php');
require_once(_PS_MODULE_DIR_.'socolissimo/classes/SCFields.php');

/* Init the Context (inherit Socolissimo and handle error) */
if (!Tools::getValue('DELIVERYMODE'))
	$so = new SCFields(Tools::getValue('deliveryMode')); /* api 3.0 mobile */
else
	$so = new SCFields(Tools::getValue('DELIVERYMODE')); /* api 4.0 */


/* Init the Display */
$display = new BWDisplay();
$display->setTemplate(dirname(__FILE__).'/views/templates/front/error.tpl');

$errors_list = array();
$redirect = __PS_BASE_URI__.(version_compare(_PS_VERSION_, '1.5', '<') ? 'order.php?' : 'index.php?controller=order&');
$so->context->smarty->assign('so_url_back', $redirect);

$return = array();

/* If error code not defined or empty / null */
$errors_codes = ($tab = Tools::getValue('ERRORCODE')) ? explode(' ', trim($tab)) : array();

/* If no required error code, start to get the POST data */
if (!$so->checkErrors($errors_codes, SCError::REQUIRED))
{
	foreach ($_POST as $key => $val)
		if ($so->isAvailableFields($key))
			if (!isset($_POST['CHARSET'])) /* only way to know if api is 3.0 to get encode for accentued chars in key calculation */
				$return[strtoupper($key)] = utf8_encode(stripslashes($val));
			else
				$return[strtoupper($key)] = stripslashes($val);

	/* GET parameter, the only one */
	$return['TRRETURNURLKO'] = Tools::getValue('trReturnUrlKo'); /* api 3.0 mobile */
	if (!$return['TRRETURNURLKO'])
		$return['TRRETURNURLKO'] = Tools::getValue('TRRETURNURLKO'); /* api 4.0 */

	foreach ($so->getFields(SCFields::REQUIRED) as $field)
		if (!isset($return[$field]))
			$errors_list[] = $so->l('This key is required for Socolissimo:').$field;
}
else
	foreach ($errors_codes as $code)
		$errors_list[] = $so->l('Error code:').' '.$so->getError($code);

if (empty($errors_list))
{
	if ($so->isCorrectSignKey($return['SIGNATURE'], $return) &&
			$so->context->cart->id && saveOrderShippingDetails($so->context->cart->id, (int)$return['TRCLIENTNUMBER'], $return, $so))
	{
		$trparamplus = explode('|', $return['TRPARAMPLUS']);

		if (count($trparamplus) > 1)
		{
			$so->context->cart->id_carrier = (int)$trparamplus[0];
			if ($trparamplus[1] == 'checked' || $trparamplus[1] == 1) /* value can be "undefined" or "not checked" */
				$so->context->cart->gift = 1;
			else
				$so->context->cart->gift = 0;
		}
		elseif (count($trparamplus) == 1)
			$so->context->cart->id_carrier = (int)$trparamplus[0];

		if ((int)$so->context->cart->gift && Validate::isMessage($trparamplus[2]))
			$so->context->cart->gift_message = strip_tags($trparamplus[2]);

		if (!$so->context->cart->update())
			$errors_list[] = $so->l('Cart cannot be updated. Please try again your selection');
		else
			Tools::redirect($redirect.'step=3&cgv=1&id_carrier='.$so->context->cart->id_carrier);
	}
	else
		$errors_list[] = $so->getError('999');
}

$so->context->smarty->assign('error_list', $errors_list);
$display->run();

function saveOrderShippingDetails($id_cart, $id_customer, $so_params, $so_object)
{
	// if api use is 3.0 we need to decode for accentued chars
	if (!isset($so_params['CHARSET']))
		foreach ($so_params as $key => $value)
			$so_params[$key] = utf8_decode($value);

	$delivery_mode = array(
		'DOM' => 'Livraison à domicile', 'BPR' => 'Livraison en Bureau de Poste',
		'A2P' => 'Livraison Commerce de proximité', 'MRL' => 'Livraison Commerce de proximité', 'CMT' => 'Livraison commerçants Belgique',
		'CIT' => 'Livraison en Cityssimo', 'ACP' => 'Agence ColiPoste', 'CDI' => 'Centre de distribution', 'BDP' => 'Bureau de poste Belge',
		'RDV' => 'Livraison sur Rendez-vous');

	// default country france
	if (isset($so_params['PRPAYS']))
		$country_code = $so_params['PRPAYS'];
	elseif (isset($so_params['CEPAYS']))
		$country_code = $so_params['CEPAYS'];
	else
		$country_code = 'FR';

	$db = Db::getInstance();
	$db->executeS('SELECT * FROM '._DB_PREFIX_.'socolissimo_delivery_info WHERE id_cart = '.(int)$id_cart.' AND id_customer ='.(int)$id_customer);
	$num_rows = (int)$db->NumRows();
	if ($num_rows == 0)
	{
		$sql = 'INSERT INTO '._DB_PREFIX_.'socolissimo_delivery_info
			( `id_cart`, `id_customer`, `delivery_mode`, `prid`, `prname`, `prfirstname`, `prcompladress`,
			`pradress1`, `pradress2`, `pradress3`, `pradress4`, `przipcode`, `prtown`,`cecountry`, `cephonenumber`, `ceemail` ,
			`cecompanyname`, `cedeliveryinformation`, `cedoorcode1`, `cedoorcode2`,`codereseau`, `cename`, `cefirstname`)
			VALUES ('.(int)$id_cart.','.(int)$id_customer.',';
		if ($so_object->delivery_mode == SCFields::RELAY_POINT)
			$sql .= '\''.pSQL($so_params['DELIVERYMODE']).'\',
					'.(isset($so_params['PRID']) ? '\''.pSQL($so_params['PRID']).'\'' : '\'\'').',
					'.(isset($so_params['CENAME']) ? '\''.pSQL($so_params['CENAME']).'\'' : '\'\'').',
					'.(isset($so_params['CEFIRSTNAME']) ? '\''.ucfirst(pSQL($so_params['CEFIRSTNAME'])).'\'' : '\'\'').',
					'.(isset($so_params['PRCOMPLADRESS']) ? '\''.pSQL($so_params['PRCOMPLADRESS']).'\'' : '\'\'').',
					'.(isset($so_params['PRNAME']) ? '\''.pSQL($so_params['PRNAME']).'\'' : '\'\'').',
					'.(isset($so_params['PRADRESS1']) ? '\''.pSQL($so_params['PRADRESS1']).'\'' : '\'\'').',
					'.(isset($so_params['PRADRESS3']) ? '\''.pSQL($so_params['PRADRESS3']).'\'' : '\'\'').',
					'.(isset($so_params['PRADRESS4']) ? '\''.pSQL($so_params['PRADRESS4']).'\'' : '\'\'').',
					'.(isset($so_params['PRZIPCODE']) ? '\''.pSQL($so_params['PRZIPCODE']).'\'' : '\'\'').',
					'.(isset($so_params['PRTOWN']) ? '\''.pSQL($so_params['PRTOWN']).'\'' : '\'\'').',
					'.(isset($country_code) ? '\''.pSQL($country_code).'\'' : '\'\'').',
					'.(isset($so_params['CEPHONENUMBER']) ? '\''.pSQL($so_params['CEPHONENUMBER']).'\'' : '\'\'').',
					'.(isset($so_params['CEEMAIL']) ? '\''.pSQL($so_params['CEEMAIL']).'\'' : '\'\'').',
					'.(isset($so_params['CECOMPANYNAME']) ? '\''.pSQL($so_params['CECOMPANYNAME']).'\'' : '\'\'').',
					'.(isset($so_params['CEDELIVERYINFORMATION']) ? '\''.pSQL($so_params['CEDELIVERYINFORMATION']).'\'' : '\'\'').',
					'.(isset($so_params['CEDOORCODE1']) ? '\''.pSQL($so_params['CEDOORCODE1']).'\'' : '\'\'').',
					'.(isset($so_params['CEDOORCODE2']) ? '\''.pSQL($so_params['CEDOORCODE2']).'\'' : '\'\'').',
                                    '.(isset($so_params['CODERESEAU']) ? '\''.pSQL($so_params['CODERESEAU']).'\'' : '\'\'').',
                                    '.(isset($so_params['CENAME']) ? '\''.ucfirst(pSQL($so_params['CENAME'])).'\'' : '\'\'').',
                                    '.(isset($so_params['CEFIRSTNAME']) ? '\''.ucfirst(pSQL($so_params['CEFIRSTNAME'])).'\'' : '\'\'').')';
		else
			$sql .= '\''.pSQL($so_params['DELIVERYMODE']).'\',\'\',
					'.(isset($so_params['CENAME']) ? '\''.ucfirst(pSQL($so_params['CENAME'])).'\'' : '\'\'').',
					'.(isset($so_params['CEFIRSTNAME']) ? '\''.ucfirst(pSQL($so_params['CEFIRSTNAME'])).'\'' : '\'\'').',
					'.(isset($so_params['CECOMPLADRESS']) ? '\''.pSQL($so_params['CECOMPLADRESS']).'\'' : '\'\'').',
					'.(isset($so_params['CEADRESS1']) ? '\''.pSQL($so_params['CEADRESS1']).'\'' : '\'\'').',
					'.(isset($so_params['CEADRESS2']) ? '\''.pSQL($so_params['CEADRESS2']).'\'' : '\'\'').',
					'.(isset($so_params['CEADRESS3']) ? '\''.pSQL($so_params['CEADRESS3']).'\'' : '\'\'').',
					'.(isset($so_params['CEADRESS4']) ? '\''.pSQL($so_params['CEADRESS4']).'\'' : '\'\'').',
					'.(isset($so_params['CEZIPCODE']) ? '\''.pSQL($so_params['CEZIPCODE']).'\'' : '\'\'').',
					'.(isset($so_params['CETOWN']) ? '\''.pSQL($so_params['CETOWN']).'\'' : '\'\'').',
					'.(isset($country_code) ? '\''.pSQL($country_code).'\'' : '\'\'').',
					'.(isset($so_params['CEPHONENUMBER']) ? '\''.pSQL($so_params['CEPHONENUMBER']).'\'' : '\'\'').',
					'.(isset($so_params['CEEMAIL']) ? '\''.pSQL($so_params['CEEMAIL']).'\'' : '\'\'').',
					'.(isset($so_params['CECOMPANYNAME']) ? '\''.pSQL($so_params['CECOMPANYNAME']).'\'' : '\'\'').',
					'.(isset($so_params['CEDELIVERYINFORMATION']) ? '\''.pSQL($so_params['CEDELIVERYINFORMATION']).'\'' : '\'\'').',
					'.(isset($so_params['CEDOORCODE1']) ? '\''.pSQL($so_params['CEDOORCODE1']).'\'' : '\'\'').',
					'.(isset($so_params['CEDOORCODE2']) ? '\''.pSQL($so_params['CEDOORCODE2']).'\'' : '\'\'').',
                                    '.(isset($so_params['CODERESEAU']) ? '\''.pSQL($so_params['CODERESEAU']).'\'' : '\'\'').',
                                    '.(isset($so_params['CENAME']) ? '\''.ucfirst(pSQL($so_params['CENAME'])).'\'' : '\'\'').',
                                    '.(isset($so_params['CEFIRSTNAME']) ? '\''.ucfirst(pSQL($so_params['CEFIRSTNAME'])).'\'' : '\'\'').')';
		if (Db::getInstance()->execute($sql))
			return true;
	}
	else
	{
		$table = _DB_PREFIX_.'socolissimo_delivery_info';
		$values = array();
		$values['delivery_mode'] = pSQL($so_params['DELIVERYMODE']);

		if ($so_object->delivery_mode == SCFields::RELAY_POINT)
		{
			isset($so_params['PRID']) ? $values['prid'] = pSQL($so_params['PRID']) : '';
			isset($so_params['PRNAME']) ? $values['prname'] = ucfirst(pSQL($so_params['PRNAME'])) : '';
			isset($delivery_mode[$so_params['DELIVERYMODE']]) ? $values['prfirstname'] =
							pSQL($delivery_mode[$so_params['DELIVERYMODE']]) : $values['prfirstname'] = 'So Colissimo';
			isset($so_params['PRCOMPLADRESS']) ? $values['prcompladress'] = pSQL($so_params['PRCOMPLADRESS']) : '';
			isset($so_params['PRADRESS1']) ? $values['pradress1'] = pSQL($so_params['PRADRESS1']) : '';
			isset($so_params['PRADRESS2']) ? $values['pradress2'] = pSQL($so_params['PRADRESS2']) : '';
			isset($so_params['PRADRESS3']) ? $values['pradress3'] = pSQL($so_params['PRADRESS3']) : '';
			isset($so_params['PRADRESS4']) ? $values['pradress4'] = pSQL($so_params['PRADRESS4']) : '';
			isset($so_params['PRZIPCODE']) ? $values['przipcode'] = pSQL($so_params['PRZIPCODE']) : '';
			isset($so_params['PRTOWN']) ? $values['prtown'] = pSQL($so_params['PRTOWN']) : '';
			isset($country_code) ? $values['cecountry'] = pSQL($country_code) : '';
			isset($so_params['CEPHONENUMBER']) ? $values['cephonenumber'] = pSQL($so_params['CEPHONENUMBER']) : '';
			isset($so_params['CEEMAIL']) ? $values['ceemail'] = pSQL($so_params['CEEMAIL']) : '';
			isset($so_params['CEDELIVERYINFORMATION']) ? $values['cedeliveryinformation'] = pSQL($so_params['CEDELIVERYINFORMATION']) : '';
			isset($so_params['CEDOORCODE1']) ? $values['cedoorcode1'] = pSQL($so_params['CEDOORCODE1']) : '';
			isset($so_params['CEDOORCODE2']) ? $values['cedoorcode2'] = pSQL($so_params['CEDOORCODE2']) : '';
			isset($so_params['CECOMPANYNAME']) ? $values['cecompanyname'] = pSQL($so_params['CECOMPANYNAME']) : '';
			isset($so_params['CODERESEAU']) ? $values['codereseau'] = pSQL($so_params['CODERESEAU']) : '';
			isset($so_params['CENAME']) ? $values['cename'] = pSQL($so_params['CENAME']) : '';
			isset($so_params['CEFIRSTNAME']) ? $values['cefirstname'] = pSQL($so_params['CEFIRSTNAME']) : '';
		}
		else
		{
			isset($so_params['PRID']) ? $values['prid'] = pSQL($so_params['PRID']) : $values['prid'] = '';
			isset($so_params['CENAME']) ? $values['prname'] = ucfirst(pSQL($so_params['CENAME'])) : '';
			isset($so_params['CEFIRSTNAME']) ? $values['prfirstname'] = ucfirst(pSQL($so_params['CEFIRSTNAME'])) : '';
			isset($so_params['CECOMPLADRESS']) ? $values['prcompladress'] = pSQL($so_params['CECOMPLADRESS']) : '';
			isset($so_params['CEADRESS1']) ? $values['pradress1'] = pSQL($so_params['CEADRESS1']) : '';
			isset($so_params['CEADRESS2']) ? $values['pradress2'] = pSQL($so_params['CEADRESS2']) : '';
			isset($so_params['CEADRESS3']) ? $values['pradress3'] = pSQL($so_params['CEADRESS3']) : '';
			isset($so_params['CEADRESS4']) ? $values['pradress4'] = pSQL($so_params['CEADRESS4']) : '';
			isset($so_params['CEZIPCODE']) ? $values['przipcode'] = pSQL($so_params['CEZIPCODE']) : '';
			isset($so_params['CETOWN']) ? $values['prtown'] = pSQL($so_params['CETOWN']) : '';
			isset($country_code) ? $values['cecountry'] = pSQL($country_code) : '';
			isset($so_params['CEEMAIL']) ? $values['ceemail'] = pSQL($so_params['CEEMAIL']) : '';
			isset($so_params['CEPHONENUMBER']) ? $values['cephonenumber'] = pSQL($so_params['CEPHONENUMBER']) : '';
			isset($so_params['CEDELIVERYINFORMATION']) ? $values['cedeliveryinformation'] = pSQL($so_params['CEDELIVERYINFORMATION']) : '';
			isset($so_params['CEDOORCODE1']) ? $values['cedoorcode1'] = pSQL($so_params['CEDOORCODE1']) : '';
			isset($so_params['CEDOORCODE2']) ? $values['cedoorcode2'] = pSQL($so_params['CEDOORCODE2']) : '';
			isset($so_params['CECOMPANYNAME']) ? $values['cecompanyname'] = pSQL($so_params['CECOMPANYNAME']) : '';
			isset($so_params['CODERESEAU']) ? $values['codereseau'] = pSQL($so_params['CODERESEAU']) : '';
			isset($so_params['CENAME']) ? $values['cename'] = pSQL($so_params['CENAME']) : '';
			isset($so_params['CEFIRSTNAME']) ? $values['cefirstname'] = pSQL($so_params['CEFIRSTNAME']) : '';
		}
		$where = ' `id_cart` =\''.(int)$id_cart.'\' AND `id_customer` =\''.(int)$id_customer.'\'';

		if (Db::getInstance()->autoExecute($table, $values, 'UPDATE', $where))
			return true;
	}
}
