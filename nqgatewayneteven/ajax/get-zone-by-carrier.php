<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../init.php');
include(dirname(__FILE__).'/../classes/Gateway.php');

if (Tools::getValue('token') != Tools::encrypt(Configuration::get('PS_SHOP_NAME')))
	die(Tools::displayError());

$id_carrier = Tools::getValue('id_carrier');
$type = Tools::getValue('type');

if(empty($id_carrier) || empty($type))
	die(Tools::displayError());

$default_val = Tools::getValue('default_val');
$carrier = new Carrier($id_carrier);
$zones = $carrier->getZones();

echo '<select name="SHIPPING_ZONE_'.strtoupper($type).'" id="zone_'.$type.'">';
echo '<option value="" >---------</option>';

foreach ($zones as $zone)
	echo '<option value="'.$zone['id_zone'].'" '.(($default_val == $zone['id_zone']) ? 'selected="selected"' : '').'>'.$zone['name'].'</option>';

echo '</select>';

die();