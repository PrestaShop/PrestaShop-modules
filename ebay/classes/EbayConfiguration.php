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
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (file_exists(dirname(__FILE__).'/EbayRequest.php'))
	require_once(dirname(__FILE__).'/EbayRequest.php');

class EbayConfiguration
{
	
	
	
	/**
	 * Updates Ebay API Token and stores it
	 *
	 * Returns true is sucessful, false otherwise
	 *
	 * @return boolean
	 */
	public static function updateAPIToken()
	{
		$request = new EbayRequest();
		$ebay_profile = EbayProfile::getCurrent();

		if ($token = $request->fetchToken(Configuration::get('EBAY_API_USERNAME', null, 0, 0), Configuration::get('EBAY_API_SESSION', null, 0, 0)))
		{
			Configuration::updateValue('EBAY_API_TOKEN', $token, false, 0, 0);
			Configuration::updateValue('EBAY_TOKEN_REGENERATE', false, false, 0, 0);

			return true;
		}

		return false;
	}
	
	public static function get($id_ebay_profile, $name)
	{
		return Db::getInstance()->getValue('SELECT `value` 
			FROM `'._DB_PREFIX_.'ebay_configuration` 
			WHERE `id_ebay_profile` = '.$id_ebay_profile.'
			AND `name` = "'.pSQL($name).'"');
	}
	
	public static function set($id_ebay_profile, $name, $value, $html = false)
	{
		$datas = array(
				'id_ebay_profile' => $id_ebay_profile,
				'name'						=> pSQL($name),
				'value'						=> pSQL($value, $html)
			);
		if(version_compare(_PS_VERSION_, '1.5', '<'))
		{
			return Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_configuration', $datas, 'INSERT');
		}
		else
			return Db::getInstance()->insert('ebay_configuration', $datas , false, true, Db::REPLACE);
	}	
	
    public static function getAll($id_ebay_profile)
    {
        $sql = 'SELECT `name`, `value`
            FROM `'._DB_PREFIX_.'ebay_configuration`
            WHERE `id_ebay_profile` = '.(int)$id_ebay_profile;
        return Db::getInstance()->executeS($sql);
    }    
    
	/**
	 * For upgrade: takes the values in PS Configurations and stores them in Ebay Configurations
	 *
	 * Returns true is sucessful, false otherwise
	 *
	 * @return boolean
	 */	
	public static function PSConfigurationsToEbayConfigurations($id_ebay_profile, $attributes, $attributes_html)
	{
		foreach($attributes as $name)
		{
			$ps_value = Configuration::get($name);
			$ebay_value = EbayConfiguration::get($id_ebay_profile, $name);
			if ($ps_value && !$ebay_value)
				EbayConfiguration::set($id_ebay_profile, $name, $ps_value);
		}
		foreach($attributes_html as $name)
		{
			$ps_value = Configuration::get($name);
			$ebay_value = EbayConfiguration::get($id_ebay_profile, $name);
			if ($ps_value && !$ebay_value)
				EbayConfiguration::set($id_ebay_profile, $name, $ps_value, true);
		}
	}

}