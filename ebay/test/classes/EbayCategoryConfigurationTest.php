<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../classes/EbayCategoryConfiguration.php';


class EbayCategoryConfigurationTest extends PHPUnit_Framework_TestCase
{

  public function setUp() 
	{
  }
	
  public function testGetEbayCategories()
	{
		$res = EbayCategoryConfiguration::getEbayCategories();
		$this->assertTrue(is_array($res));
		$this->assertTrue(count($res) > 0);
		$this->assertTrue(is_numeric($res[0]));
  }
}

