<?php
/*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 1.7.4 $
*
*  International Registered Trademark & Property of PrestaShop SA
*/

class Cart extends CartCore
{
	public function getPackageShippingCost($id_carrier = null, $use_tax = true, Country $default_country = null, $product_list = null, $id_zone = null)
	{
		include_once(dirname(__FILE__).'/../../modules/gointerpay/gointerpay.php');
		$interpay = new GoInterpay();

		if (!$interpay->active || Context::getContext()->cookie->interpay_country_code == 'US')
			return parent::getPackageShippingCost($id_carrier, $use_tax, $default_country, $product_list, $id_zone);
		else
		{
			$interpay_order = Db::getInstance()->getRow('
			SELECT orderId, shipping_orig
			FROM '._DB_PREFIX_.'gointerpay_order_id
			WHERE id_cart = '.(int)$this->id);
			
			if ($interpay_order && isset($interpay_order['orderId']) && !empty($interpay_order['orderId']))
				return (float)$interpay_order['shipping_orig'];
			else
				return 0;
		}
	}
	
	public function getTotalShippingCost($delivery_option = null, $use_tax = true, Country $default_country = null)
	{
		include_once(dirname(__FILE__).'/../../modules/gointerpay/gointerpay.php');
		$interpay = new GoInterpay();

		if (!$interpay->active || Context::getContext()->cookie->interpay_country_code == 'US')
			return parent::getTotalShippingCost($delivery_option, $use_tax, $default_country);
		else
		{
			$interpay_order = Db::getInstance()->getRow('
			SELECT orderId, shipping_orig
			FROM '._DB_PREFIX_.'gointerpay_order_id
			WHERE id_cart = '.(int)$this->id);
			
			if ($interpay_order && isset($interpay_order['orderId']) && !empty($interpay_order['orderId']))
				return (float)$interpay_order['shipping_orig'];
			else
				return 0;
		}
	}
	
	public function duplicate()
	{
		$result = parent::duplicate();

		if (isset($result['cart']))
			$result['cart']->deleteProduct((int)Configuration::get('GOINTERPAY_ID_TAXES_TDUTIES'));

		return $result;
	}
}