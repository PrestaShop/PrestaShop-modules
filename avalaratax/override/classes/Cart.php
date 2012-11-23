<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Cart extends CartCore
{
	/**
	 * @brief Calculate the shipping cost.
	 *
	 * Override it in order to calculate and add the correct tax amount.
	 *
	 * @note This method is only called in PrestaShop < 1.5
	 * @see  Cart::geTotalShippingCost() for PrestaShop > 1.5
	 *
	 * @param integrer $id_carrier      Id of the selected carrier
	 * @param boolean  $use_tax         Do we want the taxes?
	 * @param Country  $default_country Unused 1.5 only, for inheritance compatibility
	 * @param Array    $product_list    Unused 1.5 only, for inheritance compatibility
	 *
	 * @return float Price of the shipping.
	 */
	public function getOrderShippingCost($id_carrier = null, $use_tax = true, Country $default_country = null, $product_list = null)
	{
		include_once(_PS_ROOT_DIR_.'/modules/avalaratax/avalaratax.php');

		/* Instanciate the Avalara module and check if active */
		$avalara = new AvalaraTax();
		if (!$avalara->active)
			return parent::getOrderShippingCost((int)$id_carrier, $use_tax, $default_country, $product_list);

		/* Retrieve the original carrier fee tax excluded */
		$tax_excluded_cost = parent::getOrderShippingCost((int)$id_carrier, false, $default_country, $product_list);

		/* If we want price without tax or if this carrier is tax free, return this price */
		if (!(int)$this->{Configuration::get('PS_TAX_ADDRESS_TYPE')} || !$use_tax || Db::getInstance()->getValue('SELECT `tax_code` FROM `'._DB_PREFIX_.'avalara_carrier_cache` WHERE `id_carrier` = '.(int)$id_carrier) == 'NT')
			return $tax_excluded_cost;

		/* If there is no cache or cache expired, we regenerate it */
		if (CacheTools::checkCarrierCache($this))
			CacheTools::updateCarrierTax($avalara, $this, $this->{Configuration::get('PS_TAX_ADDRESS_TYPE')}, $use_tax);

		/* If we do already know it, then return it */
		return $tax_excluded_cost + (float)CacheTools::getCarrierTaxAmount($this);
	}

	/**
	 * @brief Calculate the shipping cost.
	 *
	 * Override it in order to calculate and add the correct tax amount.
	 *
	 * @note This method is only called in PrestaShop > 1.5
	 * @see  Cart::getOrderShippingCost() for PrestaShop < 1.5
	 *
	 * @param Array    $delivery_option Delivery options
	 * @param boolean  $use_tax         Do we want the taxes?
	 * @param Country  $default_country Default country (1.5 only)
	 *
	 * @return float Price of the shipping.
	 */
	public function getTotalShippingCost($delivery_option = null, $use_tax = true, Country $default_country = null)
	{
		include_once(_PS_ROOT_DIR_.'/modules/avalaratax/avalaratax.php');

		/* Instanciate the Avalara module and check if active */
		$avalara = new AvalaraTax();
		if (!$avalara->active)
			return parent::getTotalShippingCost($delivery_option, $use_tax, $default_country);

		/* Retrieve the original carrier fee tax excluded */
		$tax_excluded_cost = parent::getTotalShippingCost($delivery_option, false, $default_country);

		/* If we want price without tax or if this carrier is tax free, return this price */
		if (!(int)$this->{Configuration::get('PS_TAX_ADDRESS_TYPE')} || !$use_tax)
			return $tax_excluded_cost;

		/* If there is no cache or cache expired, we regenerate it */
		if (CacheTools::checkCarrierCache($this))
			CacheTools::updateCarrierTax($avalara, $this, $this->{Configuration::get('PS_TAX_ADDRESS_TYPE')});

		/* If we do already know it, then return it */
		return $tax_excluded_cost + (float)CacheTools::getCarrierTaxAmount($this);
	}
}
