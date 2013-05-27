<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../ajax/loadTableCategories.php';


class loadTableCategoriesTest extends PHPUnit_Framework_TestCase
{
	private $ebay_load_cat;
	
  public function setUp() 
	{
		$this->ebay_load_cat = new EbayLoadCat();
  }
	
	public function testGetTable()
	{
		$res = $this->ebay_load_cat->getTable();
	}
	
}
