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

include_once 'lib/includes/includes.inc.php';

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

include_once 'fianetsceau.php';


if (_PS_VERSION_ < '1.5')
	$sceau = new Sceau();
else
	$sceau = new Sceau(Tools::getValue('id_shop'));

if (Tools::getValue('token') == Tools::getAdminToken($sceau->getSiteid().$sceau->getAuthkey().$sceau->getLogin()))
{
	$category_id = Tools::getValue('category_id');
	$elem_id = Tools::getValue('elem_id');

	$module = new FianetSceau();

	$subcategory = $module->loadFianetSubCategories($category_id);

	if ($category_id == '0')
		$select = "";
	else
	{
		$select = "<select id='fianetsceau_".$elem_id."_subcategory' name='fianetsceau_".$elem_id."_subcategory'>";
		foreach ($subcategory as $value)
			$select .= "<option value=".$value['subcategory_id'].">".$value['label']."</option>";

		$select .= "</select>";
	}

	echo $select;
}
else
	header("Location: ../");
