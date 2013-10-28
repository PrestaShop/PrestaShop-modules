<?php
/*
* 2013 BluePay
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
*  @author BluePay Processing, LLC
*  @copyright  2013 BluePay Processing, LLC
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_'))
	exit;

class BluePayUninstall
{
	/**
	 * Delete all BluePay configuration rows from ps_configuration table
	 */
	public function deleteConfiguration()
	{
		Configuration::deleteByName('BP_ACCOUNT_ID');
		Configuration::deleteByName('BP_SECRET_KEY');
		Configuration::deleteByName('BP_TRANSACTION_MODE');
		Configuration::deleteByName('BP_TRANSACTION_TYPE');
		Configuration::deleteByName('BP_PAYMENT_TYPE');
		Configuration::deleteByName('BP_CARD_TYPES_VISA');
		Configuration::deleteByName('BP_CARD_TYPES_MC');
		Configuration::deleteByName('BP_CARD_TYPES_AMEX');
		Configuration::deleteByName('BP_CARD_TYPES_DISC');
		Configuration::deleteByName('BP_ALLOW_STORED_PAYMENTS');
		Configuration::deleteByName('BP_REQUIRE_CVV2');
		Configuration::deleteByName('BP_CHECKOUT_IFRAME');
		Configuration::deleteByName('BP_DISPLAY_LOGO');
	}
}
