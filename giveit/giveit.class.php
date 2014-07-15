<?php
/**
 * 2013 Give.it
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@give.it so we can send you a copy immediately.
 *
 * @author    JSC INVERTUS www.invertus.lt <help@invertus.lt>
 * @copyright 2013 Give.it
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of Give.it
 */

class GiveItAPI
{
	public $client;
	/* giveit SDK object */

	public $product_valid = false;
	/* indicator wheather product data is acceptable to give.it  */

	private $give_it_product;
	/* instance of giveit product class */

	private static $shipping_rules = array();

	public function __construct($public_key, $data_key, $private_key)
	{
		if (!defined('GIVEIT_PUBLIC_KEY'))
			define('GIVEIT_PUBLIC_KEY', $public_key);

		if (!defined('GIVEIT_DATA_KEY'))
			define('GIVEIT_DATA_KEY', $data_key);

		if (!defined('GIVEIT_PRIVATE_KEY'))
			define('GIVEIT_PRIVATE_KEY', $private_key);

		$this->init();
	}

	public function __call($function, $args)
	{
		return call_user_func_array(array($this->client, $function), $args);
	}

	public function init()
	{
		// create an instance of the SDK
		$this->client = new GiveItSdk;
		$this->client->debug = _GIVEIT_DEBUG_MODE_;
		$this->client->setEnvironment(_GIVEIT_ENVIRONMENT_);
	}

	public function setProduct($product, $combination)
	{
		// create the product
		$context = Context::getContext();

		$this->give_it_product = new GiveItSdkProduct();
		$this->give_it_product->setCurrency($context->currency->iso_code);

		$usetax = (Product::getTaxCalculationMethod((int)$context->customer->id) != PS_TAX_EXC);

		if ($combination['id_product_attribute'] == 0)
		{
			$combination['attributes'] = '';
			$image = Product::getCover($product->id);
		}
		else
		{
			$comb = new Combination($combination['id_product_attribute']);

			if ($image = $comb->getWsImages())
			{
				$image = $image[0];
				$image['id_image'] = $image['id'];
			}
		}

		$image['id_product'] = $product->id;
		$image['id_image'] = Product::defineProductImage($image, Context::getContext()->language->id);
		$img_profile = (version_compare(_PS_VERSION_, '1.5', '<')) ? 'home' : ImageType::getFormatedName('medium');
		$image = ($image) ? $context->link->getImageLink($product->link_rewrite, $image['id_image'], $img_profile) : '';

		// first, set the product details.
		$this->give_it_product->setProductDetails(array(
														'code' => $product->id.'_'.$combination['id_product_attribute'],
														'price' => (int) (Product::getPriceStatic((int)$product->id, $usetax, $combination['id_product_attribute']) * 100),
														'name' => $product->name.($combination['attributes'] ? ' : '.$combination['attributes'] : ''),
														'image' => $image));

		$delivery = $this->setDelivery();

		// add the delivery option to the product
		$this->give_it_product->addBuyerOption($delivery);

		//We should validate this product
		$this->product_valid = $this->give_it_product->validate();
	}

	private function setDelivery()
	{
		$product_price = $this->give_it_product->data['details']['price'];
		// add a delivery option
		$delivery = new GiveItSdkOption( Array('id' => 'my_id', 'type' => 'layered_delivery', 'name' => 'Shipping', 'tax_delivery' => true));

		// loop through all countries for which shipping is defined and create a choice on this option

		$this->getShippingRules();
		$zones = Zone::getZones(false);

		foreach (self::$shipping_rules as $rule)
		{
			if (is_numeric($rule['iso_code']))
			{
				// it's a zone not a country
				foreach ($zones as $zone)
				{
					if ($zone['id_zone'] == $rule['iso_code'])
						$country_name = $zone['name'];
				}
			}
			else
			{
				$country_id = Country::getByIso($rule['iso_code']);
				$country_name = Country::getNameById(Configuration::get('PS_LANG_DEFAULT'), $country_id);
			}
			$choice = new GiveItSdkChoice( array('id' => $rule['iso_code'], 'name' => $country_name, 'choices_title' => $country_name, ));

			// look for options for this country
			foreach (self::$shipping_rules as $option)
			{
				if ($option['iso_code'] == $rule['iso_code'])
				{
					// if option has free_above and price > then display 0 price
					$option_price = $option['price'];
					if ($option['free_above'] > 0 && $product_price > $option['free_above'])
						$option_price = 0;

					$choice->addChoice(new GiveItSdkChoice( array(
																	'id' => $option['id'],
																	'name' => $option['name'],
																	'price' => $option_price,
																	'tax_percent' => (int)$option['tax_percent'], )));
				}
			}

			$delivery->addChoice($choice);
		}

		return $delivery;
	}

	public function getButton()
	{
		if ($this->product_valid !== true)
			return;

		return $this->give_it_product->getButtonHTML();
	}

	private function getShippingRules()
	{
		if (!self::$shipping_rules)
			foreach (GiveItShipping::getShippingRules() as $rule)
				self::$shipping_rules[] = Array(
												'id' => $rule['id_giveit_shipping'].'_',
												'name' => $rule['title'],
												'iso_code' => $rule['iso_code'],
												'price' => (int) (Tools::convertPrice($rule['price']) * 100),
												'free_above' => (int) (Tools::convertPrice($rule['free_above']) * 100),
												'tax_percent' => $rule['tax_percent']);
	}

}
