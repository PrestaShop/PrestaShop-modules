<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../classes/EbayRequest.php';

class EbaySynchronizerTest
{
	private $ebay_synchronizer;
	
  public function setUp() 
	{
		$this->ebay_synchronizer = new EbaySynchronizer();
  }
	
	
	public function testSyncProducts()
	{
		$this->ebay_synchronizer->syncProducts(array(
			array(
				'id_product' => 7
			)
		));
	}
}