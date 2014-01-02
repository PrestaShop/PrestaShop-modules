<?php

/*
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!in_array('Ebay', get_declared_classes()))
		require_once(dirname(__FILE__).'/../ebay.php');

class EbayCountrySpec
{
	public $country;
	public $accepted_isos = array('it', 'gb', 'es', 'fr', 'nl', 'pl', 'be');
	protected $ebay_iso;

	private $dev;
	private static $multilang = array('be');

	private static $country_data = array(
		'it' => array(
			'site_id'        => 101,
			'documentation'  => 'it',
			'language'       => 'it_IT',
			'currency'       => 'EUR',
			'site_name'      => 'Italy',
			'site_extension' => 'it',
			'img_stats'      => null,
			'iso_code'       => 'it',
			'signin'         => 'https://signin.ebay.it/ws/eBayISAPI.dll',
			'signin_sandbox' => 'https://signin.sandbox.ebay.it/ws/eBayISAPI.dll'
		),
		'gb' => array(
			'site_id'        => 3,
			'documentation'  => 'en',
			'language'       => 'en_GB',
			'currency'       => 'GBP',
			'site_name'      => 'UK',
			'site_extension' => 'co.uk',
			'img_stats'      => null,
			'iso_code'       => 'gb',
			'signin'         => 'https://signin.ebay.co.uk/ws/eBayISAPI.dll',
			'signin_sandbox' => 'https://signin.sandbox.ebay.co.uk/ws/eBayISAPI.dll'
		),
		'es' => array(
			'site_id'        => 186,
			'documentation'  => 'es',
			'language'       => 'es_ES',
			'currency'       => 'EUR',
			'site_name'      => 'Spain',
			'site_extension' => 'es',
			'img_stats'      => null,
			'iso_code'       => 'es',
			'signin'         => 'https://signin.ebay.es/ws/eBayISAPI.dll',
			'signin_sandbox' => 'https://signin.sandbox.ebay.es/ws/eBayISAPI.dll'
		),
		'fr' => array(
			'site_id'        => 71,
			'documentation'  => 'fr',
			'language'       => 'fr_FR',
			'currency'       => 'EUR',
			'site_name'      => 'France',
			'site_extension' => 'fr',
			'img_stats'      => 'views/img/ebay_stats.png',
			'iso_code'       => 'fr',
			'signin'         => 'https://signin.ebay.fr/ws/eBayISAPI.dll',
			'signin_sandbox' => 'https://signin.sandbox.ebay.fr/ws/eBayISAPI.dll'
		),
		'nl' => array(
			'site_id'        => 146,
			'documentation'  => 'nl',
			'language'       => 'nl_NL',
			'currency'       => 'EUR',
			'site_name'      => 'Netherlands',
			'site_extension' => 'nl',
			'img_stats'      => null,
			'iso_code'       => 'nl',
			'signin'         => 'https://signin.ebay.nl/ws/eBayISAPI.dll',
			'signin_sandbox' => 'https://signin.sandbox.ebay.nl/ws/eBayISAPI.dll'
		),
		'pl' => array(
			'site_id'        => 212,
			'documentation'  => 'pl',
			'language'       => 'pl_PL',
			'currency'       => 'PLN',
			'site_name'      => 'Poland',
			'site_extension' => 'pl',
			'img_stats'      => null,
			'iso_code'       => 'pl',
			'signin'         => 'https://signin.ebay.pl/ws/eBayISAPI.dll',
			'signin_sandbox' => 'https://signin.sandbox.ebay.pl/ws/eBayISAPI.dll'
		),
		'be-fr' => array(
			'site_id'        => 23,
			'documentation'  => 'befr',
			'language'       => 'fr_BE',
			'currency'       => 'EUR',
			'site_name'      => 'Belgium_French',
			'site_extension' => 'be',
			'subdomain'      => 'befr',
			'img_stats'      => null,
			'iso_code'       => 'be',
			'signin'         => 'https://signin.befr.ebay.be/ws/eBayISAPI.dll',
			'signin_sandbox' => 'https://signin.sandbox.befr.ebay.be/ws/eBayISAPI.dll'
		),
		'be-nl' => array(
			'site_id'        => 123,
			'documentation'  => 'benl',
			'language'       => 'nl_BE',
			'currency'       => 'EUR',
			'site_name'      => 'Belgium_Dutch',
			'site_extension' => 'be',
			'subdomain'      => 'benl',
			'img_stats'      => null,
			'iso_code'       => 'be',
			'signin'         => 'https://signin.benl.ebay.be/ws/eBayISAPI.dll',
			'signin_sandbox' => 'https://signin.sandbox.benl.ebay.be/ws/eBayISAPI.dll'
		)
	);

	public function __construct(Country $country = null)
	{
		if ($country != null)
			$this->country = $country;
		else
			$this->country = $this->_getCountry();
	}

	public function getSiteID()
	{
		return $this->_getCountryData('site_id');
	}

	public function getDocumentationLang()
	{
		return $this->_getCountryDATA('documentation');
	}

	public function getLanguage()
	{
		return $this->_getCountryData('language');
	}

	public function getCurrency()
	{
		return $this->_getCountryData('currency');
	}

	public function getSiteName()
	{
		return $this->_getCountryData('site_name');
	}

	public function getSiteExtension()
	{
		return $this->_getCountryData('site_extension');
	}

	public function getSiteSubDomain()
	{
		return $this->_getCountryData('subdomain');
	}

	public function getSiteSignin()
	{
		if ($this->dev != true)
			return $this->_getCountryData('signin');
		else
			return $this->_getCountryData('signin_sandbox');
	}

	public function getImgStats()
	{
		return $this->_getCountryData('img_stats');
	}

	public function getIsoCode()
	{
		if (!$this->country)
			return null;
		return $this->country->iso_code;
	}

	public function getIdLang()
	{
		$id_lang = Language::getIdByIso($this->getIsoCode());
		if (!$id_lang) //Fix for UK
			$id_lang = Configuration::get('PS_LANG_DEFAULT');
		return (int)$id_lang;
	}

	private function _getCountryData($data)
	{
		$iso_code = $this->ebay_iso;
		if (isset(self::$country_data[$iso_code]) && isset(self::$country_data[$iso_code][$data]))
			return self::$country_data[$iso_code][$data];
		else if (isset(self::$country_data['fr'][$data]))
			return self::$country_data['fr'][$data];
		else
			return null;
	}

	/**
	* Tools Methods
	*
	* Sends back true or false
	**/
	public function checkCountry()
	{
		if (in_array(strtolower($this->country->iso_code), $this->accepted_isos))
			return true;
		return false;
	}

	/**
	* Tools Methods
	*
	* Set country
	*
	**/
	private function _getCountry()
	{
		$ebayCountry = self::getInstanceByKey(Configuration::get('EBAY_COUNTRY_DEFAULT'));

		$this->country = $ebayCountry->country;

		return $this->country;
	}

	/**
	 * Get countries
	 * @return array Countries list
	 */
	public static function getCountries($dev) {
		$countries = array();
		foreach (self::$country_data as $iso => $ctry) 
		{
			if ( isset($ctry['subdomain']) === false )
				$ctry['subdomain'] = null;

			if ($dev) {
				unset($ctry['signin']);
				$ctry['signin'] = $ctry['signin_sandbox'];
			}

			$countries[$iso] = $ctry;
		}

		ksort($countries);

		return $countries;
	}

	/**
	 * Get Instance for Ebay Country
	 * @param  string          $key Key of country
	 * @param  boolean         $dev If module work in debug
	 * @return EbayCountrySpec Ebay country
	 */
	public static function getInstanceByKey($key, $dev = false) {

		if (isset(self::$country_data[$key])) {
			$iso_code = self::$country_data[$key]['iso_code'];
			$id_country = Country::getByIso($iso_code);
		}
		else {
			$id_country = Configuration::get('PS_COUNTRY_DEFAULT');
		}

		$ebay_country = new EbayCountrySpec(new Country($id_country));
		$ebay_country->setDev($dev);
		$ebay_country->ebay_iso = is_numeric($key) ? self::getKeyForEbayCountry() : $key;

		return $ebay_country;
	}

	/**
	 * Set dev or not
	 * @param boolean $dev set dev or not
	 */
	public function setDev($dev) {
		if( is_bool($dev) )
			$this->dev = $dev;
	}

	/**
	 * Get key for iso_code tab
	 * @return string Key for iso_code tab
	 */
	public static function getKeyForEbayCountry() {

		$country = new Country((int) Configuration::get('PS_COUNTRY_DEFAULT'));

		$default_country = strtolower($country->iso_code);

		if (in_array( $default_country, EbayCountrySpec::$multilang )) {
			$lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

			$default_country .= '-' . strtolower($lang->iso_code);
		}

		return $default_country;
	}
}