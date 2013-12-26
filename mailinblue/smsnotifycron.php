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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright  2007-2013 PrestaShop SA
* @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/mailinblue.php');

if (Tools::getValue('token') != Tools::encrypt(Configuration::get('PS_SHOP_NAME')))
die('Error: Invalid Token');
$mailin = new Mailinblue();
if ($mailin->getSmsCredit() <= Configuration::get('Mailin_Notify_Value') && Configuration::get('Mailin_Api_Sms_Credit') == 1)
{
	if (Configuration::get('Mailin_Notify_Cron_Executed') == 0)
	{
		$id_lang = Tools::getValue('lang');
		$mailin->sendNotifySms(Configuration::get('Mailin_Notify_Email'), $id_lang);
		Configuration::updateValue('Mailin_Notify_Cron_Executed', 1);
	}
}
else
	Configuration::updateValue('Mailin_Notify_Cron_Executed', 0);
	echo 'Cron executed successfully';