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

class EbayStat
{
    function __construct($ebay_profile)
    {
        $data = array(
          'id' => sha1($this->_getDefaultShopUrl()),
          'profile' => $ebay_profile->id,
          'ebay_username' => Configuration::get('EBAY_IDENTIFIER'),
          'ebay_site' => $ebay_profile->getConfiguration('EBAY_SHOP'),
          'is_multishop' => (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive()),
          'install_date' => Configuration::get('EBAY_INSTALL_DATE'),
          'nb_listings' => EbayProduct::getNbProducts(),
          'percent_of_catalog' => EbayProduct:getPercentOfCatalog($ebay_profile),
          'nb_prestashop_categories' => EbayCategoryConfiguration::getNbCategories($ebay_profile->id),
          'nb_ebay_categories' => EbayCategoryConfiguration::getNbEbayCategories($ebay_profile->id),
          'nb_optional_item_specifics' => EbayCategorySpecific::getNbOptionalItemSpecifics($ebay_profile->id),
          'nb_national_shipping_services' => EbayShipping::getNbNationalShippings($ebay_profile->id),
          'nb_international_shipping_services' => EbayShipping::getNbInternationalShippings($ebay_profile->id),
          'date_add' => date('Y-m-d H:i:s'),
          'Configuration' => EbayConfiguration::getJson($id_ebay_profile),
          
        );
    }
    
    private function _getDefaultShopUrl()
    {
        $shop = new Shop(Configuration::get('PS_SHOP_DEFAULT'));
        return $shop->getBaseURL();
    }
    
}