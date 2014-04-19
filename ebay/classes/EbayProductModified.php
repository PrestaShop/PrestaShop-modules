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

class EbayProductModified extends ObjectModel
{
	public $id_product;
	public $id_ebay_profile;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition;

    public function __construct($id = null, $id_lang = null, $id_shop = null) {
        if (version_compare(_PS_VERSION_, '1.5', '>'))
            $definition = array(
            		'table' => 'ebay_product_modified',
            		'primary' => 'id_ebay_product_modified',
            		'fields' => array(
            			'id_product' =>		array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            			'id_ebay_profile' =>		array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            		),
            	);
        return parent::__construct($id, $id_lang, $id_shop); 
    }    
	
	public static function addProduct($id_ebay_profile, $id_product)
	{
		$product_modified = new EbayProductModified();
        $product_modified->id_product = (int)$id_product;
        $product_modified->id_ebay_profile = (int)$id_ebay_profile;

        return $product_modified->save();
	}
    
    public static function getAll()
    {
        $sql = 'SELECT `id_ebay_profile`, `id_product`
            FROM '._DB_PREFIX.'ebay_product_modified';
        return Db::getInstance()->executeS($sql);
    }
    
    public static function truncate()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'ebay_product_modified');
    }

}