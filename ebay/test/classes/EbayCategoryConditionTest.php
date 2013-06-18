<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../classes/EbayCategoryCondition.php';


class EbayCategoryConditionTest extends PHPUnit_Framework_TestCase
{

  public function setUp() 
	{
  }
	
  public function testLoadCategoryConditions()
	{
		$res = EbayCategoryCondition::loadCategoryConditions(73839);
  }
}