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

/**
 * Updates the template image links since the image files have moved
 *
 */
function update_product_template($module)
{
	if ($product_template = Configuration::get('EBAY_PRODUCT_TEMPLATE'))
	{
		// We cannot just replace "template/images/" by "views/img" since the use may have added its own images in "template/images"
		$product_template = str_replace(
			array(
				'template/images/favorite.png',
				'template/images/footer.png',
				'template/images/header.png',
				'template/images/search.png',
				'template/images/stats.png',
			),
			array(
				'views/img/favorite.png',
				'views/img/footer.png',
				'views/img/header.png',
				'views/img/search.png',
				'views/img/stats.png',
			),
			$product_template
		);
		$module->setConfiguration('EBAY_PRODUCT_TEMPLATE', $product_template, true);
	}
}

function upgrade_module_1_5($module)
{
	include(dirname(__FILE__).'/sql/sql-upgrade-1-5.php');

	if (!empty($sql) && is_array($sql))
	{
		foreach ($sql as $request)
			if (!Db::getInstance()->execute($request))
			{
				$this->_errors[] = DB::getInstance()->getMsgError();
				return false;
			}
	}

	update_product_template($module);

	$module->setConfiguration('EBAY_VERSION', $module->version);

	return true;
}