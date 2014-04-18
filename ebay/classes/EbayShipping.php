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
 *  @copyright  2007-2014 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class EbayShipping
{

	public static function getPsCarrierByEbayCarrier($ebay_carrier)
	{
		return Db::getInstance()->getValue('SELECT ps_carrier
			FROM `'._DB_PREFIX_.'ebay_shipping`
			WHERE `ebay_carrier` = \''.pSQL($ebay_carrier).'\'');
	}

	public static function getNationalShippings()
	{
		return Db::getInstance()->ExecuteS('SELECT *
			FROM '._DB_PREFIX_.'ebay_shipping WHERE international = 0');
	}

	public static function getInternationalShippings()
	{
		return Db::getInstance()->ExecuteS('SELECT *
			FROM '._DB_PREFIX_.'ebay_shipping WHERE international = 1');
	}

	public static function insert($ebay_carrier, $ps_carrier, $extra_fee, $international = false)
	{
		$sql = 'INSERT INTO '._DB_PREFIX_.'ebay_shipping
			VALUES(\'\',
			\''.pSQL($ebay_carrier).'\',
			\''.(int)$ps_carrier.'\',
			\''.(float)$extra_fee.'\',
			\''.(int)$international.'\')';

		DB::getInstance()->Execute($sql);
	}

	public static function truncate()
	{
		return Db::getInstance()->Execute('TRUNCATE '._DB_PREFIX_.'ebay_shipping');
	}

	public static function getLastShippingId()
	{
		return Db::getInstance()->getValue('SELECT id_ebay_shipping
			FROM '._DB_PREFIX_.'ebay_shipping
			ORDER BY id_ebay_shipping DESC');
	}
}