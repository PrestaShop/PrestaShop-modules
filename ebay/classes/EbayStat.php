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
    private static $server = 'http://tracking.202-ecommerce.com';
    private static $key = 'ejf3!2kclReRZsx311212iKUj!IGer21';
    
    private $stats_version;
    private $id_ebay_profile;
    private $data;
    private $date_add;
    
    function __construct($stats_version, $ebay_profile)
    {
        $this->stats_version = $stats_version;
        $this->id_ebay_profile = (int)$ebay_profile->id;
        
        $this->data = array(
          'id' => sha1($this->_getDefaultShopUrl()),
          'profile' => $ebay_profile->id,
          'ebay_username' => sha1(Configuration::get('EBAY_IDENTIFIER')),
          'ebay_site' => $ebay_profile->getConfiguration('EBAY_SHOP'),
          'is_multishop' => (version_compare(_PS_VERSION_, '1.5', '>') && Shop::isFeatureActive()),
          'install_date' => Configuration::get('EBAY_INSTALL_DATE'),
          'nb_listings' => EbayProduct::getNbProducts($ebay_profile->id),
          'percent_of_catalog' => EbayProduct::getPercentOfCatalog($ebay_profile),
          'nb_prestashop_categories' => EbayCategoryConfiguration::getNbPrestashopCategories($ebay_profile->id),
          'nb_ebay_categories' => EbayCategoryConfiguration::getNbEbayCategories($ebay_profile->id),
          'nb_optional_item_specifics' => EbayCategorySpecific::getNbOptionalItemSpecifics($ebay_profile->id),
          'nb_national_shipping_services' => EbayShipping::getNbNationalShippings($ebay_profile->id),
          'nb_international_shipping_services' => EbayShipping::getNbInternationalShippings($ebay_profile->id),
          'date_add' => date('Y-m-d H:i:s'),
          'Configuration' => EbayConfiguration::getAll($ebay_profile->id),
          'return_policy' => ($ebay_profile->getReturnsPolicyConfiguration()->ebay_returns_description == '' ? 0 : 1)
        );
        $this->date_add = date('Y-m-d H:i:s');
    }
    
    private function _getDefaultShopUrl()
    {
        if (version_compare(_PS_VERSION_, '1.5', '>'))
        {
            $shop = new Shop(Configuration::get('PS_SHOP_DEFAULT'));
            return $shop->getBaseURL();            
        }
        else
            return __PS_BASE_URI__;
    }
    
    public function save()
    {
        $sql = 'SELECT count(*)
            FROM `'._DB_PREFIX_.'ebay_stat`
            WHERE `id_ebay_profile` = '.(int)$this->id_ebay_profile;
        $nb_rows = Db::getInstance()->getValue($sql);
        if ($nb_rows >= 2)
            return false;
        
        $data = array(
          'id_ebay_profile' => $this->id_ebay_profile,
          'version'  => $this->stats_version,
          'data'     => pSQL(json_encode($this->data)),
          'date_add' => $this->date_add  
        );
        Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_stat', $data, 'INSERT');
    }
    
    private static function _computeSignature($version, $data, $date_add)
    {
        return sha1(self::$key.$version.$data.$date_add);
    }
    
    public static function send()
    {
        $sql = 'SELECT `id_ebay_stat`, `tries`, `version`, `data`, `date_add`
            FROM `'._DB_PREFIX_.'ebay_stat';
        $res = Db::getInstance()->executeS($sql);
        
        foreach ($res as $row)
        {
            $data = array(
                'version' => $row['version'],
                'data'    => stripslashes($row['data']),
                'date'    => $row['date_add'],
                'sig'     => EbayStat::_computeSignature($row['version'], stripslashes($row['data']), $row['date_add'])
            );
            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($data)
                )
            );
            $context  = stream_context_create($opts);            
            $ret = Tools::file_get_contents(self::$server.'/stats.php', false, $context);
            
            if (($ret == 'OK') || ($row['tries'] > 0))
            { // if upload is OK or if it's the second try already
                $sql = 'DELETE FROM `'._DB_PREFIX_.'ebay_stat`
                    WHERE `id_ebay_stat` = '.(int)$row['id_ebay_stat'];
            } else {
                $sql = 'UPDATE `'._DB_PREFIX_.'ebay_stat`
                    SET `tries` = `tries` + 1
                    WHERE `id_ebay_stat` = '.(int)$row['id_ebay_stat'];
            }
            Db::getInstance()->execute($sql);            
        }
    }
    
}