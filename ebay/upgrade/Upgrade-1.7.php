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
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Updates the template image links since the image files have moved
 *
 */

function upgrade_module_1_7($module)
{
	include(dirname(__FILE__).'/sql/sql-upgrade-1-7.php');

	if (!empty($sql) && is_array($sql))
	{
		foreach ($sql as $request)
			if (!Db::getInstance()->execute($request))
			{
				$this->_errors[] = DB::getInstance()->getMsgError();
				return false;
			}
	}
	
	// create default profile(s)
	$is_multishop = (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive());
	$id_shop_default = (int)Configuration::get('PS_SHOP_DEFAULT');

	// handle default returns policy	
	if (!($id_returns_policy_configuration = EbayReturnsPolicyConfiguration::getDefaultObjectId()))
	{
		$returns_policy_configuration = new EbayReturnsPolicyConfiguration();
		$returns_policy_configuration->save();		
		$id_returns_policy_configuration = $returns_policy_configuration->id;
	}

	// handle default profile
	$id_shops = Shop::getShops(false, null, false);
	foreach(array_keys($id_shops) as $id_shop)
	{
		if (!($profile = EbayProfile::getOneByIdShop($id_shop)))
		{
			$profile = new EbayProfile();
			$profile->id_shop = $id_shop;
		}
		$profile->ebay_user_identifier = Configuration::get('EBAY_IDENTIFIER');
		$ebay_country = EbayCountrySpec::getInstanceByKey(Configuration::get('EBAY_COUNTRY_DEFAULT'));
		$profile->ebay_site = $ebay_country->getSiteExtension();
		if ($id_shop_default == $id_shop)
			$profile->id_ebay_returns_policy_configuration = $id_returns_policy_configuration;
        else
        {
    		$returns_policy_configuration = new EbayReturnsPolicyConfiguration();
    		$returns_policy_configuration->save();		
    		$profile->id_ebay_returns_policy_configuration = $returns_policy_configuration->id;            
        }
		$profile->save();
		
		if ($id_shop_default == $id_shop)
			$id_default_ebay_profile = $profile->id;
	}
	
	$configurations_to_update = array(
		'EBAY_COUNTRY_DEFAULT',
		'EBAY_PRODUCT_TEMPLATE',
		'EBAY_ORDER_LAST_UPDATE',
		'EBAY_DELIVERY_TIME',
		'EBAY_PICTURE_SIZE_DEFAULT',
		'EBAY_PICTURE_SIZE_SMALL',
		'EBAY_PICTURE_SIZE_BIG',
		'EBAY_LISTING_DURATION',
		'EBAY_AUTOMATICALLY_RELIST',
		'EBAY_LAST_RELIST',
		'EBAY_RETURNS_DESCRIPTION',
		'EBAY_RETURNS_ACCEPTED_OPTION',
		'EBAY_RETURNS_WITHIN',
		'EBAY_RETURNS_WHO_PAYS',
		'EBAY_SYNC_PRODUCTS_MODE',
		'EBAY_ZONE_NATIONAL',
		'EBAY_ZONE_INTERNATIONAL',
		'EBAY_SHOP',
		'EBAY_SHOP_POSTALCODE',
		'EBAY_SYNC_OPTION_RESYNC',
		'EBAY_SYNC_MODE',
		'EBAY_SYNC_LAST_PRODUCT',
        'EBAY_SPECIFICS_LAST_UPDATE'
	);
	EbayConfiguration::PSConfigurationsToEbayConfigurations($id_default_ebay_profile, $configurations_to_update);
	foreach($configurations_to_update as $name)
		Configuration::deleteByName($name);
	
	// ebay_category_configuration table
	$tables = array(
		'ebay_category_configuration',
		'ebay_shipping_zone_excluded',
        'ebay_shipping_international_zone',
		'ebay_category_condition',
		'ebay_category_condition_configuration',
		'ebay_shipping',
	);
	foreach ($tables as $table)
		Db::getInstance()->update($table, array('id_ebay_profile' => $id_default_ebay_profile));
	
	$sql = 'SELECT `id_ebay_order`, `id_order`
			FROM `'._DB_PREFIX_.'ebay_order`';
	$res = Db::getInstance()->executeS($sql);
	foreach ($res as $row) 
	{
		$data = array(
			'id_ebay_order' => $row['id_ebay_order'],
			'id_order'      => $row['id_order'],
		);
		Db::getInstance()->insert('ebay_order_order', $data, false, true, Db::REPLACE);
	}
	
	// TODO: at some point we need to remove the id_order column of ebay_order which becomes useless
	// but we cannot do it before the data have moved
	
	return true;
}