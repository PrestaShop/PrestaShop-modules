<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../classes/EbayCategory.php';


class EbayCategoryTest extends PHPUnit_Framework_TestCase
{
	private $ebay_category;
	

  public function setUp() 
	{
		$this->ebay_category = new EbayCategory(73839);
  }
	
  public function testGetItemsSpecificValues()
	{
		$res = $this->ebay_category->getItemsSpecificValues();
		print_r($res);
  }
}
