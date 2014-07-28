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

	public static function getPsCarrierByEbayCarrier($id_ebay_profile, $ebay_carrier)
	{
		return Db::getInstance()->getValue('SELECT `ps_carrier`
			FROM `'._DB_PREFIX_.'ebay_shipping`
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.' 
			AND `ebay_carrier` = \''.pSQL($ebay_carrier).'\'');
	}

	public static function getNationalShippings($id_ebay_profile, $id_product = null)
	{
		$shippings = Db::getInstance()->ExecuteS('SELECT *
			FROM '._DB_PREFIX_.'ebay_shipping 
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.' 
			AND international = 0');

		if ($id_product && version_compare(_PS_VERSION_, '1.5', '>'))
		{
			$shippings_product = Db::getInstance()->ExecuteS('SELECT id_carrier_reference as ps_carrier
			FROM '._DB_PREFIX_.'product_carrier WHERE id_product = '.(int)$id_product);
			if(count($shippings_product) > 0)
			{
				if(array_intersect_assoc($shippings, $shippings_product))
					$shippings = array_intersect_assoc($shippings, $shippings_product);
			}
		}

		return $shippings;
	}

	public static function internationalShippingsHaveZone($shippings)
	{
		foreach ($shippings as $shipping) {
			if(!Db::getInstance()->getValue('SELECT * FROM '._DB_PREFIX_.'ebay_shipping_international_zone WHERE id_ebay_shipping = '.(int)$shipping['id_ebay_shipping']))
				return false;
		}
		return true;
	}

	public static function getInternationalShippings($id_ebay_profile, $id_product = null)
	{
		$shippings = Db::getInstance()->ExecuteS('SELECT *
			FROM '._DB_PREFIX_.'ebay_shipping 
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.' 
			AND international = 1');
		
		if ($id_product && version_compare(_PS_VERSION_, '1.5', '>'))
		{
			$shippings_product = Db::getInstance()->ExecuteS('SELECT id_carrier_reference as ps_carrier
			FROM '._DB_PREFIX_.'product_carrier WHERE id_product = '.(int)$id_product);
			if(count($shippings_product) > 0)
			{
				if(array_intersect_assoc($shippings, $shippings_product))
					$shippings = array_intersect_assoc($shippings, $shippings_product);
			}
		}

		return $shippings;
	}

	public static function getNbNationalShippings($id_ebay_profile)
	{
		return Db::getInstance()->getValue('SELECT count(*)
			FROM '._DB_PREFIX_.'ebay_shipping 
            WHERE `international` = 0
            AND `id_ebay_profile` = '.(int)$id_ebay_profile);
	}

	public static function getNbInternationalShippings($id_ebay_profile)
	{
		return Db::getInstance()->getValue('SELECT count(*)
			FROM '._DB_PREFIX_.'ebay_shipping 
            WHERE international = 1
            AND `id_ebay_profile` = '.(int)$id_ebay_profile);
	}

	public static function insert($id_ebay_profile, $ebay_carrier, $ps_carrier, $extra_fee, $id_zone, $international = false)
	{
		$sql = 'INSERT INTO `'._DB_PREFIX_.'ebay_shipping` (
            `id_ebay_profile`, 
            `ebay_carrier`, 
            `ps_carrier`, 
            `extra_fee`,
            `international`,
            `id_zone`
            )
			VALUES(
			\''.(int)$id_ebay_profile.'\',
			\''.pSQL($ebay_carrier).'\',
			\''.(int)$ps_carrier.'\',
			\''.(float)$extra_fee.'\',
			\''.(int)$international.'\', 
			\''.(int)$id_zone.'\')';

		DB::getInstance()->Execute($sql);
	}

	public static function truncate($id_ebay_profile)
	{
		return Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'ebay_shipping
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile);
	}

	public static function getLastShippingId($id_ebay_profile)
	{
		return Db::getInstance()->getValue('SELECT id_ebay_shipping
			FROM '._DB_PREFIX_.'ebay_shipping
			WHERE `id_ebay_profile` = '.(int)$id_ebay_profile.'
			ORDER BY id_ebay_shipping DESC');
	}
}