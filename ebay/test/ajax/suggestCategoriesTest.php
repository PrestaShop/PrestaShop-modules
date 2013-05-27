<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../ajax/suggestCategories.php';


class suggestCategoriesTest extends PHPUnit_Framework_TestCase
{
	private $ebay_suggest_categories;
	
  public function setUp() 
	{
		$this->ebay_suggest_categories = new EbaySuggestCategories();
  }
	
	public function testGetSuggest()
	{
		$res = $this->ebay_suggest_categories->getSuggest();
		$this->assertTrue(is_string($res));
		$this->assertTrue($res != '');
	}
	
}
