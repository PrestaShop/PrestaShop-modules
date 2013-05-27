<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../config/config.inc.php');
require_once __DIR__.'/../classes/EbayCountrySpec.php';


class EbayTest extends PHPUnit_Framework_TestCase
{
	private $ebay;
	
  public function setUp() 
	{
		$this->ebay = new Ebay();
		$this->ebay->install();
  }
	
	public function testHookNewOrder()
	{
		// new cart
		$this->assertFalse($this->ebay->hookNewOrder(array(
			'cart' => new Cart()
		)));
		
		// empty but existing cart	
		$cart = new Cart();
		$cart->id_currency = 1;
		$cart->add();
		
		$this->ebay->hookNewOrder(array(
			'cart' => $cart,
		));
		
		// TODO: test with a full call
	}
	
	public function testHookAddProduct()
	{
		// new productt
		$this->assertFalse($this->ebay->hookAddProduct(array(
			'product' => new Product()
		)));
		
		// existing product sync mode A	
		Configuration::updateValue('EBAY_SYNC_MODE', 'A');
		$product = new Product(1);
		EbayCategoryConfiguration::add(array(
			'id_category' 		 => $product->id_category_default,
			'id_ebay_category' => 1
		));
		
		$this->ebay->hookAddProduct(array(
			'product' => $product,
		));
	}
	
	public function testHookHeader()
	{
		Configuration::updateValue('EBAY_PAYPAL_EMAIL', null);
		Configuration::updateValue('EBAY_ORDER_LAST_UPDATE', null);

		// with no paypal email in the config
		$this->assertFalse($this->ebay->hookHeader(array()));
		
		Configuration::updateValue('EBAY_PAYPAL_EMAIL', 'test@test.com');
		
		// with no order last update
		$this->assertNull($this->ebay->hookHeader(array()));
		
		Configuration::updateValue('EBAY_ORDER_LAST_UPDATE', '2000-10-02T00:00:00');

		// with order last update
		$this->ebay->hookHeader(array());
	}
	
	public function testGetContent()
	{
		$this->ebay->getContent();
	}
	
	public function testAjaxProductSync()
	{
		$this->ebay->ajaxProductSync();
	}
	
	
	
}
