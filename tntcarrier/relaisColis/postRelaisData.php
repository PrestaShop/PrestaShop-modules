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

require('../../../config/config.inc.php');
require('../../../init.php');

global $cookie;
$id_cart = (int)$cookie->id_cart;

$data = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'tnt_carrier_drop_off` WHERE `id_cart` = "'.(int)$id_cart.'"');

if (Tools::getValue('due_date'))
{
	$date = pSQL(Tools::getValue('due_date'));
	$day = substr($date, 0, 2);
	$month = substr($date, 3, 2);
	$year = substr($date, 6, 4);
	$date = $year.'-'.$month.'-'.$day;

	if (count($data) > 0)
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'tnt_carrier_drop_off` SET `due_date` = "'.$date.'" WHERE `id_cart` = "'.(int)$id_cart.'"');
	else
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tnt_carrier_drop_off` (`id_cart`, `due_date`) VALUES ("'.(int)$id_cart.'", "'.$date.'")');
}
else
{
	$code = pSQL(Tools::getValue('tntRCSelectedCode'));
	$name = substr(pSQL(Tools::getValue('tntRCSelectedNom')), 0, 32);
	$address = pSQL(Tools::getValue('tntRCSelectedAdresse'));
	$zipcode = pSQL(Tools::getValue('tntRCSelectedCodePostal'));
	$city = pSQL(Tools::getValue('tntRCSelectedCommune'));
	
	if (count($data) > 0)
	{
		echo "ok";
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'tnt_carrier_drop_off` 
			SET `code` = "'.$code.'", 
			`name` = "'.$name.'",
			`address` = "'.$address.'", 
			`zipcode` = "'.$zipcode.'", 
			`city` = "'.$city.'" 
			WHERE `id_cart` = "'.(int)($id_cart).'"');
	}
	else
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tnt_carrier_drop_off` 
			(`id_cart`, `code`, `name`, `address`, `zipcode`, `city`) 
			VALUES ("'.(int)($id_cart).'", "'.$code.'", "'.$name.'", "'.$address.'", "'.$zipcode.'", "'.$city.'")');
}


?>