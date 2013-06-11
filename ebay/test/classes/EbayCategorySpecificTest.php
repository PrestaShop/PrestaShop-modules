<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../classes/EbayCategorySpecific.php';


class EbayCategorySpecificTest extends PHPUnit_Framework_TestCase
{

  public function setUp() 
	{
  }
	
  public function testLoadCategorySpecifics()
	{
		EbayCategorySpecific::loadCategorySpecifics(53159);
  }
}
