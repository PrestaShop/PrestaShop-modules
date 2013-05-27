<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../ajax/changeCategoryMatch.php';


class EbayChangeCategoryMatchTest extends PHPUnit_Framework_TestCase
{
	private $ebay_ajax;
	
  public function setUp() 
	{
		$this->ebay_ajax = new EbayAjax();
  }
	
}
