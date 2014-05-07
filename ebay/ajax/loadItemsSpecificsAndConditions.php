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

include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../classes/EbayCategorySpecific.php');
include(dirname(__FILE__).'/../classes/EbayCategoryCondition.php');

if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	die('ERROR : INVALID TOKEN');


$id_ebay_profile = (int)Tools::getValue('profile');
$ebay_profile = new EbayProfile($id_ebay_profile);

function loadItemsMap($row)
{
	return $row['id'];
}

/* Fix for limit db sql request in time */
sleep(1);

$category = new EbayCategory((int)Tools::getValue('ebay_category'));


if (!$ebay_profile->getConfiguration('EBAY_SPECIFICS_LAST_UPDATE') || ($ebay_profile->getConfiguration('EBAY_SPECIFICS_LAST_UPDATE') < date('Y-m-d\TH:i:s', strtotime('-3 days')).'.000Z'))
{
	$time = time();
	$res = EbayCategorySpecific::loadCategorySpecifics($id_ebay_profile);
	$res &= EbayCategoryCondition::loadCategoryConditions($id_ebay_profile);
	if ($res)
		$ebay_profile->setConfiguration('EBAY_SPECIFICS_LAST_UPDATE', date('Y-m-d\TH:i:s.000\Z'), false);
}

$item_specifics = $category->getItemsSpecifics();
$item_specifics_ids = array_map('loadItemsMap', $item_specifics);

if (count($item_specifics_ids))
{
	$sql = 'SELECT `id_ebay_category_specific_value` as id, `id_ebay_category_specific` as specific_id, `value`
		FROM `'._DB_PREFIX_.'ebay_category_specific_value`
		WHERE `id_ebay_category_specific` in ('.implode(',', $item_specifics_ids).')';
	
	$item_specifics_values = DB::getInstance()->executeS($sql);
}
else
	$item_specifics_values = array();

foreach ($item_specifics as &$item_specific)
	foreach ($item_specifics_values as $value)
		if ($item_specific['id'] == $value['specific_id'])
			$item_specific['values'][$value['id']] = $value['value'];

echo Tools::jsonEncode(array(
	'specifics' => $item_specifics,
	'conditions' => $category->getConditionsWithConfiguration($id_ebay_profile),
	'is_multi_sku' => $category->isMultiSku()
));