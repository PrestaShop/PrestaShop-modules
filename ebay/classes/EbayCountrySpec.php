<?php

/*
 * 2007-2013 PrestaShop
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
 *  @copyright  2007-2013 PrestaShop SA
 *  @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!in_array('Ebay', get_declared_classes()))
		require_once(dirname(__FILE__).'/../ebay.php');

class EbayCountrySpec
{
	public $country;
	public $accepted_isos = array('it', 'fr', 'gb', 'es');

	private $country_data = array(
		'it' => array(
			'site_id' => 101,
			'language' => 'it_IT',
			'currency' => 'EUR',
			'site_name' => 'Italy',
			'site_extension' => 'it',
			'img_stats' => null
		),
		'gb' => array(
			'site_id' => 3,
			'language' => 'en_GB',
			'currency' => 'GBP',
			'site_name' => 'UK',
			'site_extension' => 'co.uk',
			'img_stats' => null
		),
		'es' => array(
			'site_id' => 186,
			'language' => 'es_ES',
			'currency' => 'EUR',
			'site_name' => 'Spain',
			'site_extension' => 'es',
			'img_stats' => null
		),
		'fr' => array(
			'site_id' => 71,
			'language' => 'fr_FR',
			'currency' => 'EUR',
			'site_name' => 'France',
			'site_extension' => 'fr',
			'img_stats' => 'views/img/ebay_stats.png'
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
		$iso_code = strtolower($this->country->iso_code);
		if (isset($this->country_data[$iso_code]))
			return $this->country_data[$iso_code][$data];
		return $this->country_data['fr'][$data];
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
		// If eBay module has already been called once, use the default country
		if (!is_object($this->country))
			$this->country = new Country((int)Configuration::get('EBAY_COUNTRY_DEFAULT'));

		// Else use PrestaShop Default country
		if (in_array(strtolower($this->country->iso_code), $this->accepted_isos))
			return $this->country;

		$this->country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));

		if (in_array(strtolower($this->country->iso_code), $this->accepted_isos))
			Configuration::updateValue('EBAY_COUNTRY_DEFAULT', $this->country->id, false, 0, 0);
		return $this->country;
	}
}