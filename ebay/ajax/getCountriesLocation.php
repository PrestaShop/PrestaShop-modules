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

include(dirname(__FILE__).'/../../../config/config.inc.php');

if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	die('ERROR : INVALID TOKEN');

$sql = 'SELECT * FROM '._DB_PREFIX_.'ebay_shipping_zone_excluded WHERE region = \''.pSQL(Tools::getValue('region')).'\'';
$countries = Db::getInstance()->ExecuteS($sql);

if (count($countries))
{
	$string = '';

	foreach ($countries as $country)
	{
		$string .= '<div class="excludeCountry">
			<input type="checkbox" name="excludeLocation['.Tools::safeOutput($country['location']).']" ';

		if ($country['excluded'] == 1)
			$string .= ' checked="checked" ';

		$string .= '/>'.Tools::safeOutput($country['description']).'</div>';
	}
	
	echo $string;
}
else
	echo 'No countries were found for this region';
