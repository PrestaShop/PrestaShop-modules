<?php
/**
* 2007-2011 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2011 PrestaShop SA
*  @version   Release: $Revision: 7732 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class   SyspayInstallTools {
	public static function setOrderState()
	{
		$tab_os = array();
		$tab_os[] = array('key' => 'PS_OS_SYSPAY_AUTHORIZED', 'fr' => 'Paiement authorisé',
			'en' => 'Authorized Payment');
		$tab_os[] = array('key' => 'PS_OS_SYSPAY_CB', 'fr' => 'Chargebacked',
			'en' => 'Chargebacked');
		$tab_os[] = array('key' => 'PS_OS_SYSPAY_CB_DELIVERED', 'fr' => 'Chargeback et livré',
			'en' => 'Chargebacked and delivered');
		$tab_os[] = array('key' => 'PS_OS_SYSPAY_CB_PIP', 'fr' => 'Chargeback et préparation en cours',
			'en' => 'Chargebacked and preparation in progress');
		$tab_os[] = array('key' => 'PS_OS_SYSPAY_CB_SHIPPED', 'fr' => 'Chargebacked et en cours de livraison',
			'en' => 'Chargebacked and shipped');
		$tab_os[] = array('key' => 'PS_OS_SYSPAY_REFUND_DELIVERED', 'fr' => 'Livré et remboursé partiellement',
			'en' => 'Delivered and partially refunded');
		$tab_os[] = array('key' => 'PS_OS_SYSPAY_REFUND_PIP', 'fr' => 'Préparation en cours et remboursé partiellement',
			'en' => 'Preparation in progress and partially refunded');
		$tab_os[] = array('key' => 'PS_OS_SYSPAY_REFUND_SHIPPED', 'fr' => 'En cours de livraison et remboursé partiellement',
			'en' => 'Shipped and partially refunded');
		foreach ($tab_os as $t)
		{
			if (!Configuration::get($t['key']))
			{
				$order_state = new OrderState();
				$order_state->name = array();
				foreach (Language::getLanguages() as $language)
				{
					if ($language['iso_code'] == 'fr')
						$order_state->name[$language['id_lang']] = $t['fr'];
					else
						$order_state->name[$language['id_lang']] = $t['en'];
				}
				$order_state->send_email = false;
				$order_state->color      = '#7DB3E2';
				$order_state->unremovable = false;
				$order_state->hidden     = false;
				$order_state->delivery   = false;
				$order_state->logable    = false;
				$order_state->invoice    = false;
				if ($order_state->add())
				{
					Configuration::updateValue($t['key'], (int)$order_state->id);
					$source = dirname(__FILE__).'/../img/syspay_os.gif';
					$destination = dirname(__FILE__).'/../../../img/os/'.(int)$order_state->id.'.gif';
					copy($source, $destination);
				}
				else
					return false;
			}
		}
		return true;
	}

	public static function deleteOrderState()
	{
		$tab_os = array();
		$tab_os[] = 'PS_OS_SYSPAY_AUTHORIZED';
		$tab_os[] = 'PS_OS_SYSPAY_CB';
		$tab_os[] = 'PS_OS_SYSPAY_CB_DELIVERED';
		$tab_os[] = 'PS_OS_SYSPAY_CB_PIP';
		$tab_os[] = 'PS_OS_SYSPAY_CB_SHIPPED';
		$tab_os[] = 'PS_OS_SYSPAY_REFUND_DELIVERED';
		$tab_os[] = 'PS_OS_SYSPAY_REFUND_PIP';
		$tab_os[] = 'PS_OS_SYSPAY_REFUND_SHIPPED';
		foreach ($tab_os as $key)
		{
			$id = Configuration::get($key);
			if ($id)
			{
				Configuration::deleteByName($key);
				$os = new OrderState($id);
				$os->delete();
			}
		}
	}

	public static function setEmployee()
	{
		Configuration::updateValue('SYSPAY_EMPLOYEE', 0);
		return true;
	}

	public static function setConfigurationValue()
	{
		Configuration::updateValue('SYSPAY_LIVE_MID', '');
		Configuration::updateValue('SYSPAY_LIVE_SHA1_PRIVATE', '');
		Configuration::updateValue('SYSPAY_TEST_MID', '');
		Configuration::updateValue('SYSPAY_TEST_SHA1_PRIVATE', '');
		Configuration::updateValue('SYSPAY_MODE', 0);
		Configuration::updateValue('SYSPAY_LAYOUT', 'FULL');
		Configuration::updateValue('SYSPAY_METHOD_RETURN', 'POST');
		Configuration::updateValue('SYSPAY_ERRORS', '1');
		Configuration::updateValue('SYSPAY_REBILL', '0');
		Configuration::updateValue('SYSPAY_CAPTURE_OS', '0');
		Configuration::updateValue('SYSPAY_AUTHORIZED_PAYMENT', '1');
		Configuration::updateValue('SYSPAY_WEBSITE_ID', '');
	}

	public static function deleteConfigurationValue()
	{
		Configuration::deleteByName('SYSPAY_LIVE_MID');
		Configuration::deleteByName('SYSPAY_LIVE_SHA1_PRIVATE');
		Configuration::deleteByName('SYSPAY_TEST_MID');
		Configuration::deleteByName('SYSPAY_TEST_SHA1_PRIVATE');
		Configuration::deleteByName('SYSPAY_MODE');
		Configuration::deleteByName('SYSPAY_LAYOUT');
		Configuration::deleteByName('SYSPAY_METHOD_RETURN');
		Configuration::deleteByName('SYSPAY_ERRORS');
		Configuration::deleteByName('SYSPAY_REBILL');
		Configuration::deleteByName('SYSPAY_CAPTURE_OS');
		Configuration::deleteByName('SYSPAY_AUTHORIZED_PAYMENT');
		Configuration::deleteByName('SYSPAY_EMPLOYEE');
		Configuration::deleteByName('SYSPAY_WEBSITE_ID');
	}

	public static function setDb()
	{
		if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_."syspay_payment` (
					`id_syspay_payment` INT(10) NOT NULL,
					`id_cart` INT(10) NULL DEFAULT NULL,
					`order_ref` VARCHAR(25) NULL DEFAULT NULL,
					`redirect_url` VARCHAR(512) NULL DEFAULT NULL,
					`type` VARCHAR(25) NULL DEFAULT NULL,
					PRIMARY KEY (`id_syspay_payment`)
				)
				COLLATE='utf8_general_ci'
				ENGINE="._MYSQL_ENGINE_))
			return false;

		if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_."syspay_refund` (
					`id_syspay_refund` INT(10) NOT NULL,
					`id_order` INT(10) NOT NULL,
					PRIMARY KEY (`id_syspay_refund`)
				)
				COLLATE='utf8_general_ci'
				ENGINE="._MYSQL_ENGINE_))
			return false;

		if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_."syspay_rebill` (
				`id_billing_agreement` INT(10) NOT NULL,
				`id_customer` INT(10) NULL DEFAULT NULL,
				PRIMARY KEY (`id_billing_agreement`)
				)
				COLLATE='utf8_general_ci'
				ENGINE="._MYSQL_ENGINE_))
			return false;

		return true;
	}

	public static function setRestrictions($id)
	{
		$countries = CountryCore::getCountries(Context::getContext()->language->id);
		if (version_compare(_PS_VERSION_, '1.5', '>='))
			$restrictions = Db::getInstance()->executeS('SELECT id_country FROM '._DB_PREFIX_.'module_country WHERE id_module='.$id.' 
				AND id_shop='.Context::getContext()->shop->id);
		else
			$restrictions = Db::getInstance()->executeS('SELECT id_country FROM '._DB_PREFIX_.'module_country WHERE id_module='.$id);
		$rlist = array();
		foreach ($restrictions as $r)
			$rlist[] = $r['id_country'];
		$values = array();
		while (($val = current($countries)) !== false)
		{
			if (!in_array(key($val), $rlist))
			{
				if (version_compare(_PS_VERSION_, '1.5', '>='))
					$values[] = '('.(int)$id.', '.(int)Context::getContext()->shop->id.', '.(int)key($val).')';
				else
					$values[] = '('.(int)$id.', '.(int)key($val).')';
			}
			next($countries);
		}
		if (!empty($values))
		{
			if (version_compare(_PS_VERSION_, '1.5', '>='))
			{
				Db::getInstance()->execute('
						INSERT IGNORE INTO `'._DB_PREFIX_.'module_country`
						(`id_module`, `id_shop`, `id_country`)
						VALUES '.implode(',', $values));
			}
		}
		return true;
	}
}
