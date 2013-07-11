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

// Init
$sql = array();
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_category`;';	
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_category_configuration`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_product`;';	
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_order`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_sync_history`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_sync_history_product`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_shipping`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_shipping_zone_excluded`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_shipping_international_zone`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_shipping_location`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_delivery_time_options`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_shipping_service`;';
$sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'ebay_returns_policy`;';
