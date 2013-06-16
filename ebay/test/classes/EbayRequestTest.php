<?php
define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../classes/EbayRequest.php';

// requires the module to be registered on eBay for the tests to work

class EbayRequestTest extends PHPUnit_Framework_TestCase
{
	private $ebay_request;
	
  public function setUp() 
	{
		$this->ebay_request = new EbayRequest();
  }
	
	/*
	public function testLogin()
	{
		$this->ebay_request->login();
		$this->assertEquals(strlen($this->ebay_request->session), 40);
	}

	public function testFetchToken()
	{ 
		$session_id = $this->ebay_request->login();
		$this->ebay_request->fetchToken('testuser_prestab', $session_id);
	}
	
	public function testGetUserProfile()
	{
		$res = $this->ebay_request->getUserProfile('testuser_prestab');
		$this->assertTrue(is_array($res));
		$this->assertEquals(3, count($res));
	}
	
	public function testGetCategories()
	{
		$res = $this->ebay_request->getCategories();
		$this->assertTrue(is_array($res));
		$this->assertTrue(count($res) > 0);
	}
		
	public function testGetSkuCompliantCategories()
	{
		$res = $this->ebay_request->getSkuCompliantCategories();
		$this->assertTrue(is_array($res));
		$this->assertTrue(count($res) > 0);		
	}
	*/
	
	public function testGetCategoryFeatures()
	{
		$res = $this->ebay_request->getCategoryFeatures(73839);
		print_r($res);
	}
	
	/*
	public function testGetCategorySpecifics()
	{
		$res = $this->ebay_request->getCategorySpecifics(53159);
		print_r($res);
	}

	public function testGetSuggestedCategory()
	{
		$res = $this->ebay_request->getSuggestedCategory('test');
		$this->assertTrue(is_int($res));
		$this->assertTrue($res > 0);
	}
	
	public function testGetReturnsPolicies()
	{
		$res = $this->ebay_request->getReturnsPolicies();
		$this->assertTrue(is_array($res));
		$this->assertTrue(count($res) > 0);
		$this->assertTrue(array_key_exists('value', $res[0]));	
		$this->assertTrue(array_key_exists('description', $res[0]));
	}

	public function testGetInternationalShippingLocations()
	{
		$res = $this->ebay_request->getInternationalShippingLocations();
		$this->assertTrue(is_array($res));
		$this->assertTrue(count($res) > 0);
		$this->assertTrue(array_key_exists('location', $res[0]));	
		$this->assertTrue(array_key_exists('description', $res[0]));
	}
	
	public function testGetExcludeShippingLocations()
	{
		$res = $this->ebay_request->getExcludeShippingLocations();
		$this->assertTrue(is_array($res));
		$this->assertTrue(count($res) > 0);
		$this->assertTrue(array_key_exists('location', $res[0]));	
		$this->assertTrue(array_key_exists('region', $res[0]));
		$this->assertTrue(array_key_exists('description', $res[0]));
	}

	public function testGetCarriers()
	{
		$res = $this->ebay_request->getCarriers();
		$this->assertTrue(is_array($res));
		$this->assertTrue(count($res) > 0);
		$this->assertTrue(array_key_exists('description', $res[0]));
		$this->assertTrue(array_key_exists('shippingService', $res[0]));
		$this->assertTrue(array_key_exists('shippingServiceID', $res[0]));
		$this->assertTrue(array_key_exists('ServiceType', $res[0]));
		$this->assertTrue(array_key_exists('InternationalService', $res[0]));
	}		
	
	public function testGetDeliveryTimeOptions()
	{
		$res = $this->ebay_request->getDeliveryTimeOptions();
		$this->assertTrue(is_array($res));
		$this->assertTrue(count($res) > 0);
		$this->assertTrue(array_key_exists('description', $res[0]));
		$this->assertTrue(array_key_exists('DispatchTimeMax', $res[0]));
	}
	
	public function testAddFixedPriceItem()
	{
		
		Configuration::updateValue('EBAY_RETURNS_DESCRIPTION', '', false, 0, 0);
		Configuration::updateValue('EBAY_RETURNS_ACCEPTED_OPTION', 'ReturnsAccepted', false, 0, 0);
		Configuration::updateValue('EBAY_SHOP_POSTALCODE', '75019', false, 0, 0);
				
		$data = array(
			'id_product' => '1-12',
			'reference' => 'demo_1',
			'name' 			=> 'iPod Nano Bleu',
			'brand' 		=> 'Apple Computer, Inc',
			'description' => 'This is the description
					iPod Nano

					189,05 € 

					Disponibilité: en stock
					<p><span style="font-size: small;"><strong>Des courbes avantageuses.</strong></span></p>',
			'description_short' => '<p>Nouveau design. Nouvelles fonctionnalités. Désormais en 8 et 16 Go. iPod nano, plus rock que jamais.</p>',
			'price' => '189.05',
		  'quantity' => 10,
		  'categoryId' => 1281,
			'pictures' => array(
				'http://raphaelarbuz.com/ebay_testing/p/15-large_default.jpg',
		    'http://raphaelarbuz.com/ebay_testing/p/1/6/16-large_default.jpg',
			),
			'picturesMedium' => array(
				'http://raphaelarbuz.com/ebay_testing/p/1/5/15-medium_default.jpg',
				'http://raphaelarbuz.com/ebay_testing/p/1/6/16-medium_default.jpg',
			),
			'picturesLarge' => array(
				'http://raphaelarbuz.com/ebay_testing/p/1/5/15-large_default.jpg',
				'http://raphaelarbuz.com/ebay_testing/p/1/6/16-large_default.jpg',
			),
			'condition' => 1000,
			'shipping' => array(
				'excludedZone' => array(),
				'nationalShip' => array(),
				'internationalShip' => array()
			),
			'attributes' => array(
				'Couleur' => 'Bleu'
			),
			'id_attribute' => 12
		);
		
		$res = $this->ebay_request->addFixedPriceItem($data);
		print_r($this->ebay_request->error);
		print_r($this->ebay_request->errorCode);
	}
	
	public function testReviseFixedPriceItem()
	{ 
		$res = $this->ebay_request->reviseFixedPriceItem(array(
			'itemID'			=> 1,
			'id_product'  => 1,
			'name'			  => 'Test Product',
			'pictures'	  => array(),
			'description' => 'This is a test product',
			'categoryId'  => 1,
			'condition'   => 'good',
			'price'				=> 12,
			'quantity'		=> 1,
			'brand'				=> 'Test Brand',
			'shipping'		=> array(
				'excludedZone'			=> array(),
				'nationalShip' 			=> array(),
				'internationalShip' => array()
			)));
	}

	public function testEndFixedPriceItem()
	{ 
		$res = $this->ebay_request->endFixedPriceItem(array(
			'itemID'			=> 1,
			'id_product'	=> 1
				));	
	}
	
	public function testAddFixedPriceItemMultiSku()
	{
		$res = $this->ebay_request->addFixedPriceItemMultiSku(array(
			'id_product'  => 1,
			'name'			  => 'Test Product',
			'pictures'	  => array(),
			'description' => 'This is a test product',
			'categoryId'  => 1,
			'condition'   => 'good',
			'price'				=> 12,
			'quantity'		=> 1,
			'brand'				=> 'Test Brand',
			'shipping'		=> array(
				'excludedZone'			=> array(),
				'nationalShip' 			=> array(),
				'internationalShip' => array()
			)));
	}
	
	public function testRelistFixedPriceItem()
	{
		$res = $this->ebay_request->relistFixedPriceItem(1);
	}

	public function testGetOrders()
	{
		$res = $this->ebay_request->getOrders(date('Y-m-d', strtotime('10 September 2000')), date('Y-m-d'), 1);
		$this->assertTrue(is_array($res));
	}
	*/
	
}