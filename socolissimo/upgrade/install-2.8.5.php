<?php

/*
 * 2007-2010 PrestaShop
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
 *  @author Prestashop SA <contact@prestashop.com>
 *  @author Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright  2007-2014 PrestaShop SA / 1997-2013 Quadra Informatique
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registred Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_2_8_5($object, $install = false)
{
	// update value so url mobile
	Configuration::updateValue('SOCOLISSIMO_COST_SELLER', false);
	// add column codereseau, cename, cefirstname in table socolissimo_delivery_info, checking exitence first (2.8.5 update)
	$query = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS
			  WHERE COLUMN_NAME= "codereseau"
			  AND TABLE_NAME=  "'._DB_PREFIX_.'socolissimo_delivery_info"
			  AND TABLE_SCHEMA = "'._DB_NAME_.'"';

	$result = Db::getInstance()->ExecuteS($query);

	// adding column codereseau
	if (!$result)
	{
		$query = 'ALTER TABLE '._DB_PREFIX_.'socolissimo_delivery_info add  `codereseau` varchar(3)';
		Db::getInstance()->Execute($query);
	}
	$query = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS
			  WHERE COLUMN_NAME= "cename"
			  AND TABLE_NAME=  "'._DB_PREFIX_.'socolissimo_delivery_info"
			  AND TABLE_SCHEMA = "'._DB_NAME_.'"';

	$result = Db::getInstance()->ExecuteS($query);

	// adding column cename
	if (!$result)
	{
		$query = 'ALTER TABLE '._DB_PREFIX_.'socolissimo_delivery_info add  `cename` varchar(64)';
		Db::getInstance()->Execute($query);
	}
	$query = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS
			  WHERE COLUMN_NAME= "cefirstname"
			  AND TABLE_NAME=  "'._DB_PREFIX_.'socolissimo_delivery_info"
			  AND TABLE_SCHEMA = "'._DB_NAME_.'"';

	$result = Db::getInstance()->ExecuteS($query);

	// adding column cefirstname
	if (!$result)
	{
		$query = 'ALTER TABLE '._DB_PREFIX_.'socolissimo_delivery_info add  `cefirstname` varchar(64)';
		Db::getInstance()->Execute($query);
	}
	Configuration::updateValue('SOCOLISSIMO_VERSION', '2.8.5');
	return true;
}
