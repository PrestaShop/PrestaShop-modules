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
File Last Modified: 16/07/2013 - By Lewis Ayres-Stephens - PaymentSense - Failed Transaction Retry fixed
File Modified: 09/10/2013 - By Lewis Ayres-Stephens - PaymentSense - replaced ‘global $smarty' with the context : ‘$this->context->smarty’
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/paymentsense.php');

include('../../header.php');
$paymentsense = new PaymentSense();

if ($paymentsense->active)
{
	$return_message1 = '';
	$return_message2 = '';
	$return_message3 = '';
	$return_message4 = '';
	$return_message5 = '';
	$return_message6 = '';
	
	if (trim(Configuration::get('PAYMENTSENSE_PSK') == '') || !Configuration::get('PAYMENTSENSE_PSK') || trim(Configuration::get('PAYMENTSENSE_GATEWAYPASS') == '') || !Configuration::get('PAYMENTSENSE_GATEWAYPASS'))
	die('PS GATEWAY NOT CONFIGURED. NO SUCCESS ALLOWED');

	if (Configuration::get('PS_REWRITING_SETTINGS') == 1)
		$rewrited_url = __PS_BASE_URI__;

	$return_message = '';
	$debug_message = '<h3>Debug</h3>';
	$cart_id = (int)Tools::substr(Tools::getValue('OrderID'), strpos(Tools::getValue('OrderID'), '~') + 1);

	if (Tools::getValue('HashDigest') != '' && $cart_id > 0)
	{
		$genhash = sha1('PreSharedKey='.Configuration::get('PAYMENTSENSE_PSK').'&MerchantID='.Configuration::get('PAYMENTSENSE_GATEWAYID').'&Password='.Configuration::get('PAYMENTSENSE_GATEWAYPASS').'&CrossReference='.Tools::getValue('CrossReference').'&OrderID='.Tools::getValue('OrderID'));
		if ($genhash == Tools::getValue('HashDigest'))
		{
			$debug_message .= '<p><b>Hash Check:</b> Passed</p>';
			$sql = 'SELECT CONCAT(o.id_order, "|", oh.id_order_state, "|",osl.name, "|",m.message) returnedstring 
			FROM '._DB_PREFIX_.'orders o
			LEFT JOIN '._DB_PREFIX_.'order_history oh ON (oh.id_order = o.id_order)
			LEFT JOIN '._DB_PREFIX_.'order_state_lang osl ON (osl.id_lang = o.id_lang AND osl.id_order_state = oh.id_order_state)
			LEFT JOIN '._DB_PREFIX_.'message m ON (m.id_order = o.id_order)
			WHERE o.id_cart = '.(int)$cart_id;

			$db_result = Db::getInstance()->getValue($sql);
			$debug_message .= 'MySQL Query: '.$sql.'';
			$debug_message .= 'DB Result: '.$db_result.'';

			if (Tools::strlen($db_result) > 0)
			{
				$order_details = explode('|', $db_result);
				$debug_message .= 'Order ID:</b> '.$order_details[0].'';
				$debug_message .= 'Order Status ID:</b> '.$order_details[1].'';
				$debug_message .= 'Order Status:</b> '.$order_details[2].'';
				$debug_message .= 'Order Message:</b> '.$order_details[3].'';

				if ($order_details[1] == 2)
				{
					$return_message1 .= 'Payment Successful';
					$return_message2 .= 'Your payment was successful, you should receive a confirmation by email from us shortly.';
					$return_message3 .= 'Order Status: '.$order_details[2].'';
					$return_message4 .= 'Order Message: '.$order_details[3].'';
					$return_message5 .= 'Thank you for your order.';
				}
				elseif ($order_details[1] == 8)
				{
					$url = htmlspecialchars($_SERVER['REQUEST_URI']);
					$returnurl = dirname(dirname(dirname($url))).'/order.php?submitReorder&id_order='.$order_details[0].'\'';
					$return_message1 .= 'Payment Failed';
					$return_message2 .= 'There has been a problem with your payment. Your order has not been successful.';
					$return_message3 .= 'The message below was returned from the payment gateway.';
					$return_message4 .= 'Order Status: '.$order_details[2].'';
					$return_message5 .= 'Order Message: '.$order_details[3].'';
				}
				elseif ($order_details[1] == 9)
				{
					$return_message1 .= 'Out of Stock';
					$return_message2 .= 'Payment has been taken but your order is on hold. Please contact us for more information about your order.';
					$return_message3 .= 'Order Status: '.$order_details[2].'';
					$return_message4 .= 'Order Message: '.$order_details[3].'';
				}
				else
				{
					$url = htmlspecialchars($_SERVER['REQUEST_URI']);
					$returnurl = dirname(dirname(dirname($url))).'/order.php?submitReorder&id_order='.$order_details[0].'\'';
					$return_message1 .= 'Payment Failed';
					$return_message2 .= 'There has been a problem with your payment. Your order is on hold.';
					$return_message3 .= 'Order Status:</b> '.$order_details[2].'';
					$return_message4 .= 'Order Message:</b> '.$order_details[3].'';
				}
			}
			else
				$debug_message .= '<p><b></b> '.$db_result.'</p>';
		}
		else
		{
			$return_message1 .= 'Order Not Processed<p>Your order has not been processed further. Please contact us quoting the message below.';
			$return_message2 .= 'Hash Check: Failed';
			$return_message3 .= 'Your payment may have been successful, however our security checks have flagged up an issue.';
			$return_message4 .= 'Please email the following message to us so we can investigate this issue further:';
			$return_message5 .= 'Hash Check Failed:<br>'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'';
			$debug_message .= '<p><b>Hash Check:</b> Failed</p>';
		}
		$debug_message .= '<p><b>Passed Hash:</b> '.$_GET['HashDigest'].'</p>';
		$debug_message .= '<p><b>Generated Hash:</b> '.$genhash.'</p>';
		$debug_message .= '<p><b>Hash Values:</b> PreSharedKey='.Configuration::get('PAYMENTSENSE_PSK').'&MerchantID='.Configuration::get('PAYMENTSENSE_GATEWAYID').'&Password='.Configuration::get('PAYMENTSENSE_GATEWAYPASS').'&CrossReference='.Tools::getValue('CrossReference').'&OrderID='.Tools::getValue('OrderID').'</p>';
	}

	if (Configuration::get('PAYMENTSENSE_DEBUG') == 'True')
		$return_message .= $debug_message;
$return_test = 'There has been a problem with your payment';
	$context->smarty->assign('ReturnMessage1', $return_message1);
	$context->smarty->assign('ReturnMessage2', $return_message2);
	$context->smarty->assign('ReturnMessage3', $return_message3);
	$context->smarty->assign('ReturnMessage4', $return_message4);
	$context->smarty->assign('ReturnMessage5', $return_message5);
	$context->smarty->display(dirname(__FILE__).'/views/templates/front/success.tpl');

	/*include(dirname(__FILE__).'/../../footer.php');*/
	
	$controller=new FrontController();
	$controller->init();
	$controller->initContent();
	$controller->setMedia();
	$controller->displayFooter();

}
