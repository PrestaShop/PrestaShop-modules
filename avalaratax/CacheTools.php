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
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class CacheTools
{
	public static function checkProductCache($ids_product, $region, Cart $cart)
	{
		$result = Db::getInstance()->ExecuteS('SELECT ac.`tax_rate`, ac.`update_date`
		FROM `'._DB_PREFIX_.'avalara_product_cache` ac
		WHERE ac.`id_product` IN ('.pSQL($ids_product).')	AND ac.`region` = \''.pSQL($region).'\' AND ac.`id_address` = '.(int)$cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});

		if (count($result) == count($cart->getProducts()))
		{
			// Compare date/time
			date_default_timezone_set(@date_default_timezone_get());
			$date2 = time();
			foreach ($result as $line)
				if (abs($date2 - strtotime($line['update_date'])) > 3600)
					return true;
			return false;
		}
		return true;
	}

	/**
	 * @brief Check the Carrier Cache
	 *
	 * @param cart Cart object
	 *
	 * @return True if the cache expired, false otherwise
	 */
	public static function checkCarrierCache(Cart $cart)
	{
		$result = Db::getInstance()->getRow('SELECT `cart_hash`, `update_date` FROM `'._DB_PREFIX_.'avalara_carrier_cache`
		WHERE `id_cart` = '.(int)$cart->id.' AND `id_carrier` = '.(int)$cart->id_carrier);

		if (!$result)
			return true;

		$tmp = array('id_carrier' => (int)$cart->id_carrier, 'id_address' => (int)$cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
		foreach ($cart->getProducts() as $product)
			$tmp[] = array('id_product' => (int)$product['id_product'], 'id_product_attribute' => (int)$product['id_product_attribute'], 'quantity' => (int)$product['quantity']);

		if ($result['cart_hash'] != md5(serialize($tmp)))
			return true;

		// Compare date/time
		date_default_timezone_set(@date_default_timezone_get());
		if (abs(time() - strtotime($result['update_date'])) > 3600)
			return true;

		return false;
	}

	public static function updateProductsTax(AvalaraTax $avalaraModule, Cart $cart, $id_address, $region, $taxable = true)
	{
		$p = array();
		foreach ($cart->getProducts() as $product)
		{
			$avalaraProducts = array('id_product' => (int)$product['id_product'],
												 'name' => $product['name'],
												 'description_short' => $product['description_short'],
												 'quantity' => 1, // This is a per product, so qty is 1
												 'total' => (float)$product['price'],
												 'tax_code' => $taxable ? $avalaraModule->getProductTaxCode((int)$product['id_product']) : 'NT');

			$p[] = $avalaraProducts;

			// Call Avalara
			$getTaxResult = $avalaraModule->getTax(array($avalaraProducts), array('type' => 'SalesOrder', 'DocCode' => 1, 'cart' => $cart, 'taxable' => $taxable), $id_address);

			// Store the taxrate in cache
			// If taxrate exists (but it's outdated), then update, else insert (REPLACE INTO)
			if (isset($getTaxResult['TotalTax']) && (float)$getTaxResult['TotalTax'] >= 0 && isset($getTaxResult['TotalAmount']) && $getTaxResult['TotalAmount'])
				Db::getInstance()->Execute('REPLACE INTO `'._DB_PREFIX_.'avalara_product_cache` (`id_product`, `tax_rate`, `region`, `update_date`, `id_address`)
									VALUES ('.(int)$product['id_product'].', '.(float)($getTaxResult['TotalTax'] * 100 / $getTaxResult['TotalAmount']).',
												\''.($region ? pSQL($region) : '').'\', \''.date('Y-m-d H:i:s').'\', '.(int)$cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}.')');
		}
		return $p;
	}

	public static function updateCarrierTax(AvalaraTax $avalaraModule, Cart $cart, $id_address, $taxable = true)
	{
		/** Update cache only if it is outdated */
		if (self::checkCarrierCache($cart))
		{
			$avalaraProducts = array();
			foreach ($cart->getProducts() as $product)
				$avalaraProducts[] = array('id_product' => (int)$product['id_product'],
														 'name' => $product['name'],
														 'description_short' => $product['description_short'],
														 'quantity' => (int)$product['quantity'],
														 'total' => (float)$product['price'],
														 'tax_code' => $taxable ? $avalaraModule->getProductTaxCode((int)$product['id_product']) : 'NT');
			if (count($avalaraProducts))
			{
				// Calculate the carrier taxes
				$getTaxResult = $avalaraModule->getTax($avalaraProducts, array('cart' => $cart), (int)$id_address);
				$amount = isset($getTaxResult['TaxLines']['Shipping']['GetTax']) ? (float)$getTaxResult['TaxLines']['Shipping']['GetTax'] : 0;

				$tmp = array('id_carrier' => (int)$cart->id_carrier, 'id_address' => (int)$cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
				foreach ($cart->getProducts() as $product)
					$tmp[] = array('id_product' => (int)$product['id_product'], 'id_product_attribute' => (int)$product['id_product_attribute'], 'quantity' => (int)$product['quantity']);

				Db::getInstance()->Execute('REPLACE INTO `'._DB_PREFIX_.'avalara_carrier_cache` (`id_carrier`, `tax_rate`, `amount`, `update_date`, `id_cart`, `cart_hash`)
									VALUES ('.(int)$cart->id_carrier.',
									'.(float)($getTaxResult['TotalTax'] * 100 / $getTaxResult['TotalAmount']).',
									'.(float)$amount.',
									\''.date('Y-m-d H:i:s').'\',
								  '.(int)$cart->id.',
								  \''.md5(serialize($tmp)).'\')');
			}
		}
	}

	public static function getCarrierTaxAmount(Cart $cart)
	{
		return (float)Db::getInstance()->getValue('SELECT `amount` FROM `'._DB_PREFIX_.'avalara_carrier_cache` WHERE `id_cart` = '.(int)$cart->id.' AND `id_carrier` = '.(int)$cart->id_carrier);
	}
}
