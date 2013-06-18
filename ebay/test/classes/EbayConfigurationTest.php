<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../classes/EbayConfiguration.php';


class EbayConfigurationTest extends PHPUnit_Framework_TestCase
{
	private $configuration;
  
	public function setUp() 
	{
		$this->configuration = new EbayConfiguration();
  }
	
	public function testUpdateAPIToken()
	{
		$res = $this->configuration->updateAPIToken();
		$this->assertTrue(is_bool($res));
	}
	
}
