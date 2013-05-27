<?php

define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../classes/EbayCountrySpec.php';


class EbayCountrySpecTest extends PHPUnit_Framework_TestCase
{
	private $italy_country_spec;
	private $france_country_spec;
	private $germany_country_spec;
	
  public function setUp() 
	{
		
		$italy = new Country();
		$italy->iso_code = 'it';
		$this->italy_country_spec = new EbayCountrySpec($italy);

		$france = new Country();
		$france->iso_code = 'fr';
		$this->france_country_spec = new EbayCountrySpec($france);

		$germany = new Country();
		$germany->iso_code = 'de';
		$this->germany_country_spec = new EbayCountrySpec($germany);

		$uk = new Country();
		$uk->iso_code = 'uk';
		$this->uk_country_spec = new EbayCountrySpec($uk);
		
  }
	
  public function testGetSiteID() 
	{
		$this->assertEquals($this->italy_country_spec->getSiteID(), 101);
		$this->assertEquals($this->france_country_spec->getSiteID(), 71);
		$this->assertEquals($this->germany_country_spec->getSiteID(), 71);
  }
	
	public function testGetLanguage() 
	{
		$this->assertEquals($this->italy_country_spec->getLanguage(), 'it_IT');
		$this->assertEquals($this->france_country_spec->getLanguage(), 'fr_FR');
		$this->assertEquals($this->germany_country_spec->getLanguage(), 'fr_FR');		
	}
	
	public function testGetCurrency() 
	{
		$this->assertEquals($this->italy_country_spec->getCurrency(), 'EUR');
		$this->assertEquals($this->france_country_spec->getCurrency(), 'EUR');
		$this->assertEquals($this->germany_country_spec->getCurrency(), 'EUR');		
	}	
	
	public function testGetSiteExtension() 
	{
		$this->assertEquals($this->italy_country_spec->getSiteExtension(), 'it');
		$this->assertEquals($this->france_country_spec->getSiteExtension(), 'fr');
		$this->assertEquals($this->germany_country_spec->getSiteExtension(), 'fr');		
	}		

	public function testGetImgStats() 
	{
		$this->assertEquals($this->italy_country_spec->getImgStats(), null);
		$this->assertEquals($this->france_country_spec->getImgStats(), 'views/img/ebay_stats.png');
		$this->assertEquals($this->germany_country_spec->getImgStats(), 'views/img/ebay_stats.png');
	}
	
	public function testGetIsoCode()
	{
		$this->assertEquals($this->italy_country_spec->getIsoCode(), 'it');
	}
	
	public function testGetIdLang()
	{
		$this->assertTrue(is_int($this->italy_country_spec->getIdLang()));
		$this->assertTrue($this->italy_country_spec->getIdLang() > 0);
		$this->assertEquals($this->uk_country_spec->getIdLang(), Configuration::get('PS_LANG_DEFAULT'));
	}	
	
	public function testCheckCountry() 
	{
		$this->assertTrue($this->italy_country_spec->checkCountry());
		$this->assertTrue($this->france_country_spec->checkCountry());
		$this->assertFalse($this->germany_country_spec->checkCountry());
	}
	
	

}
