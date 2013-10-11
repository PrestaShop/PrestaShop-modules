<?php
/*
* Prestashop PaymentSense Re-Directed Payment Module
* Copyright (C) 2013 PaymentSense.
*
* This program is free software: you can redistribute it and/or modify it under the terms
* of the AFL Academic Free License as published by the Free Software Foundation, either
* version 3 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
* without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
* See the AFL Academic Free License for more details. You should have received a copy of the
* AFL Academic Free License along with this program. If not, see <http://opensource.org/licenses/AFL-3.0/>.
*
*  @author PaymentSense <devsupport@paymentsense.com>
*  @copyright  2013 PaymentSense
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
This file is part of the Prestashop PaymentSense Re-Directed Payment Module
See paymentsense.php for Licensing and support info.
File Last Modified: 12/03/2013 - By Shaun Ponting - Opal Creations
File Last Modified: 15/08/2013 - By Adam - Opal Creations, swapped intval to int and updated TPL paths
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/paymentsense.php');

$paymentsense = new PaymentSense();
if ($paymentsense->active)
{
	if ((int)(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
		$rewrited_url = __PS_BASE_URI__;

	/* Only allow certain parameters to be passed through */
	$valid_params = array('ps_checksum', 'ps_payment_amount', 'ps_currency_code', 'ps_merchant_reference', 'ps_email',
	'ps_cardholder_name', 'ps_houseno', 'ps_postcode', 'ps_success_url', 'ps_failure_url', 'ps_success_redirect_url',
	'ps_failure_redirect_url', 'ps_return_url', 'ps_payment_amount', 'ps_currency_code', 'ps_merchant_reference', 'test');

	/* Grab the acceptable parameters and filter to prevent XSS */
	$parameters = array();
	foreach ($valid_params as $param_name)
		if (array_key_exists($param_name, $_GET))
			$parameters[$param_name] = htmlspecialchars(Tools::getValue($param_name));

		$smarty->assign('form_tag', '<form name="redirectform" method="POST" action="https://pay.netbanx.com/'.
		htmlentities(Configuration::get('PAYMENTSENSE_GATEWAYID'), ENT_COMPAT | ENT_HTML401, 'UTF-8').'">');

	/* Setup the form for the template */
	$form_source = '';
	foreach ($parameters as $param => $value)
		$form_source .= '<input type="hidden" name="'.$param.'" value="'.$value.'" />';

	$smarty->assign('form', $form_source);
	$smarty->display(dirname(__FILE__).'/views/templates/front/redirect.tpl');
}