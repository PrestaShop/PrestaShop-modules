<?php

/*
* Adyen Payment Module
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
*  @author Rik ter Beek <rikt@adyen.com>
*  @copyright  Copyright (c) 2013 Adyen (http://www.adyen.com)
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class AdyenNotificationModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		$order_id = (int)Tools::getValue('merchantReference');
		Logger::addLog('Adyen module: incoming notification for id_order '.$order_id);
		
		if ($this->validateNotificationCredential())
		{
			$psp_reference = (string)Tools::getValue('pspReference');
			$event_code = (string)Tools::getValue('eventCode');
			$auth_result = (string)Tools::getValue('authResult');
			$payment_method = (string)Tools::getValue('paymentMethod');
			$success = (string)Tools::getValue('success');
			
			$event_data = (!empty($event_code)) ? $event_code : $auth_result;
			
			// check if notification is already executed on server based on psp_reference and event_code
			if ((int)$order_id > 0 && !$this->isDuplicate($psp_reference, $event_code))
			{
				// save notification to table so notification is handled only once
				Db::getInstance()->insert('adyen_event_data', array (
						'psp_reference' => pSQL($psp_reference),
						'adyen_event_code' => pSQL($event_code),
						'adyen_event_result' => pSQL($event_data),
						'id_order' => (int)$order_id,
						'payment_method' => pSQL($payment_method),
						'created_at' => date('Y-m-d H:i:s')
				));
				
				// get the order
				$order = new Order($order_id);
				
				$history = new OrderHistory();
				$history->id_order = (int)$order->id;
				
				if ((strcmp($success, 'false') == 0 || $success == '') || $event_code == 'CANCELLATION')
				{
					// failed if post value success is false or not filled in
					$history->changeIdOrderState((int)Configuration::get('ADYEN_STATUS_CANCELLED'), (int)($order->id));
					$history->add();
					Logger::addLog('Adyen module: status for id_order '.$order->id.' is changed to cancelled');
				} else
				{
					// if success is not false then check if eventCode is AUTHORISATION so that order status is accepted
					if ($event_code == 'AUTHORISATION')
					{
						$history->changeIdOrderState((int)Configuration::get('ADYEN_STATUS_AUTHORIZED'), (int)($order->id));
						$history->add();
						Logger::addLog('Adyen module: status for id_order '.$order->id.' is changed to authorized');
					} else
						Logger::addLog('Adyen module: status for id_order '.$order->id.' is '.$event_code.' and is ignored');
				}
			} else
				Logger::addLog('Adyen module: incoming notification ignored because it is already handled for id_order '.$order_id);
		} else {
			Logger::addLog('Adyen module: invalid credential for incoming notification of id_order '.$order_id, 4);
			// unauthorized
			header('HTTP/1.1 401 Unauthorized',true,401);
			exit();
		}
		// always return accepted
		die('[accepted]');
	}
	public function isDuplicate($psp_reference, $event_code)
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.'adyen_event_data
			    WHERE psp_reference = \''.pSQL($psp_reference).'\' AND adyen_event_code = \''.pSQL($event_code).'\' ';
		
		if (Db::getInstance()->getRow($sql))
			return true;
		
		return false;
	}
	public function validateNotificationCredential()
	{
		$this->fixCgiHttpAuthentication();
		$user_configuration = Configuration::get('ADYEN_NOTI_USERNAME');
		$user_password = Configuration::get('ADYEN_NOTI_PASSWORD');
		$user_name = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
		$password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
		
		if ($user_configuration == $user_name && $user_password == $password)
			return true;
		
		return false;
	}
	
	/**
	 * Fix these global variables for the CGI
	 */
	public function fixCgiHttpAuthentication() {
		if (isset($_SERVER['REDIRECT_REMOTE_AUTHORIZATION']) && $_SERVER['REDIRECT_REMOTE_AUTHORIZATION'] != '') {
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode($_SERVER['REDIRECT_REMOTE_AUTHORIZATION']));
		} elseif(!empty($_SERVER['HTTP_AUTHORIZATION'])){
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(Tools::substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		} elseif (!empty($_SERVER['REMOTE_USER'])) { // When cgi and .htaccess modrewrite patch is executed
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(Tools::substr($_SERVER['REMOTE_USER'], 6)));
		} elseif (!empty($_SERVER['REDIRECT_REMOTE_USER'])) {
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(Tools::substr($_SERVER['REDIRECT_REMOTE_USER'], 6)));
		}
	}
}