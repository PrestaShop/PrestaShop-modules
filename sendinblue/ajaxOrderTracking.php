<?php
/**
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
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include_once(_PS_CLASS_DIR_.'/../classes/Customer.php');
include(dirname(__FILE__).'/sendinblue.php');

if (Tools::getValue('token') != Tools::encrypt(Configuration::get('PS_SHOP_NAME')))
	die('Error: Invalid Token');

$sendin = new Sendinblue();

if (Configuration::get('Sendin_order_tracking_Status') == 0)
{
	
	$customer_detail = $sendin->getAllCustomers();
	foreach ($customer_detail as $customer_value)
	{
		$data = array();
		$data['key'] = Configuration::get('Sendin_Api_Key');
		$data['webaction'] = 'USERS-STATUS';
		$data['email'] = $customer_value['email'];
		$sendin->curlRequest($data);
		$user_status = Tools::jsonDecode($sendin->curlRequest($data), true);

		if ($user_status['result'] != '')
		{
			$orders = Order::getCustomerOrders($customer_value['id_customer']);

			foreach ($orders as $orders_data)
			{
				if (version_compare(_PS_VERSION_, '1.5', '>='))
					$order_id = $orders_data['reference'];
				else
				$order_id = $orders_data['id_order'];

				$order_price = Tools::safeOutput($orders_data['total_paid']);
				$tracking = $sendin->trackingResult();
				$site_id = Tools::safeOutput($tracking->result->tracking_data->site_id);
				$date_value = $sendin->getApiConfigValue();

				if ($date_value->date_format == 'dd-mm-yyyy')
					$date = date('d-m-Y', strtotime($orders_data['date_add']));
				else
					$date = date('m-d-Y', strtotime($orders_data['date_add']));

				$list = str_replace('|', ',', Configuration::get('Sendin_Selected_List_Data'));
					if (preg_match('/^[0-9,]+$/', $list))
						$list = $list;
					else
						$list = '';
								
				$data = array();
				$data['key'] = Configuration::get('Sendin_Api_Key');
				$data['webaction'] = 'USERCREADIT';
				$data['email'] = $customer_value['email'];
				$data['blacklisted'] = '';
				$data['attributes_name'] = 'ID|FIRST_NAME|LAST_NAME|ORDER_ID|ORDER_PRICE|ORDER_DATE';        
				$data['attributes_value'] = $site_id.'|'.$customer_value['firstname'].'|'.$customer_value['lastname'].'|'.$order_id.'|'.$order_price.'|'.$date;
				$data['listid'] = $list;
				$sendin->curlRequest($data);

			}
		}
	}
	Configuration::updateValue('Sendin_order_tracking_Status', 1);
}