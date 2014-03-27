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
 *  @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once dirname(__FILE__).'/../../../config/config.inc.php';
include_once dirname(__FILE__).'/../../../init.php';
include_once dirname(__FILE__).'/../ebay.php';

$ebay = new Ebay();

if (!Configuration::get('EBAY_SECURITY_TOKEN') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
	return Tools::safeOutput(Tools::getValue('not_logged_str'));

$category_list = $ebay->getChildCategories(Category::getCategories(Tools::getValue('id_lang')), version_compare(_PS_VERSION_, '1.5', '>') ? 1 : 0);

$ebay_category_list = Db::getInstance()->executeS('SELECT *
	FROM `'._DB_PREFIX_.'ebay_category`
	WHERE `id_category_ref` = `id_category_ref_parent`');

if (version_compare(_PS_VERSION_, '1.5', '>'))
{
	$rq_get_cat_in_stock = '
		SELECT SUM(s.`quantity`) AS instockProduct, p.`id_category_default`
		FROM `'._DB_PREFIX_.'product` AS p
		INNER JOIN `'._DB_PREFIX_.'stock_available` AS s ON p.`id_product` = s.`id_product`
		WHERE 1 '.$ebay->addSqlRestrictionOnLang('s').'
		GROUP BY p.`id_category_default`';
}
else
{
	$rq_get_cat_in_stock = 'SELECT SUM(`quantity`) AS instockProduct, `id_category_default`
		FROM `'._DB_PREFIX_.'product`
		GROUP BY `id_category_default`';
}

$get_cats_stock = Db::getInstance()->ExecuteS($rq_get_cat_in_stock);
$get_cat_in_stock = array();

foreach ($get_cats_stock as $data)
	$get_cat_in_stock[$data['id_category_default']] = $data['instockProduct'];

/* Loading categories */
$category_config_list = array();
/* init refcats */
$ref_categories = array();
/* init levels */
$levels = array();
/* init selects */
$sql = '
	SELECT *, ec.`id_ebay_category` AS id_ebay_category
	FROM `'._DB_PREFIX_.'ebay_category` AS ec
	LEFT OUTER JOIN `'._DB_PREFIX_.'ebay_category_configuration` AS ecc
	ON ec.`id_ebay_category` = ecc.`id_ebay_category`
	ORDER BY `level`';

foreach (Db::getInstance()->executeS($sql) as $category)
{
	/* Add datas */
	if (isset($category['id_category']))
		$category_config_list[$category['id_category']] = $category;

	/* add refcats */
	if (!isset($ref_categories[$category['id_category_ref']]))
	{
		$ref_categories[$category['id_category_ref']] = $category;
		/* Create children in refcats */
		if ($category['id_category_ref'] != $category['id_category_ref_parent'])
		{
			if (!isset($ref_categories[$category['id_category_ref_parent']]['children']))
				$ref_categories[$category['id_category_ref_parent']]['children'] = array();
			if (!in_array($category['id_category_ref'], $ref_categories[$category['id_category_ref_parent']]['children']))
				$ref_categories[$category['id_category_ref_parent']]['children'][] = $category['id_category_ref'];
		}
	}
}

foreach ($category_config_list as &$category) {
	$category['var'] = getSelectors($ref_categories, $category['id_category_ref'], $category['id_category'], $category['level'], $ebay).'<input type="hidden" name="category['.(int)$category['id_category'].']" value="'.(int)$category['id_ebay_category'].'" />';
	if ($category['percent']) {
		preg_match("#^([-|+]{0,1})([0-9]{0,3})([\%]{0,1})$#is", $category['percent'], $temp);
		$category['percent'] = array('sign' => $temp[1], 'value' => $temp[2], 'type' => $temp[3]);
	} else {
		$category['percent'] = array('sign' => '', 'value' => '', 'type' => '');
	}
}

$smarty =  Context::getContext()->smarty;
$id_currency = Context::getContext()->cookie->id_currency;
$currency = new Currency((int) $id_currency);


/* Smarty datas */
$template_vars = array(
	'tabHelp' => '&id_tab=7',
	'_path' => $ebay->getPath(),
	'categoryList' => $category_list,
	'eBayCategoryList' => $ebay_category_list,
	'getCatInStock' => $get_cat_in_stock,
	'categoryConfigList' => $category_config_list,
	'request_uri' => $_SERVER['REQUEST_URI'],
	'noCatSelected' => Tools::getValue('ch_cat_str'),
	'noCatFound' => Tools::getValue('ch_no_cat_str'),
	'currencySign' => $currency->sign
);

$smarty->assign($template_vars);
echo $ebay->display(realpath(dirname(__FILE__).'/../'), '/views/templates/hook/table_categories.tpl');

/**
 * Create selectors
 *
 * @param array $ref_categories
 * @param int $id_category_ref
 * @param int $id_category
 * @param int $level
 * @param object $ebay
 *
 * @return string
 */
function getSelectors($ref_categories, $id_category_ref, $id_category, $level, $ebay)
{
	$var = null;
	
	if ($level > 1)
	{
		foreach ($ref_categories as $ref_id_category_ref => $category)
		{
			if ($ref_id_category_ref == $id_category_ref)
			{
				if (isset($ref_categories[$category['id_category_ref_parent']]['children']))
				{
					if ((int)$category['id_category_ref'] != (int)$category['id_category_ref_parent'])
						$var .= getSelectors($ref_categories, (int)$category['id_category_ref_parent'], (int)$id_category, (int)($level - 1), $ebay);
					
					$var .= '<select name="category['.(int)$id_category.']" id="categoryLevel'.(int)($category['level']).'-'.(int)$id_category.'" rel="'.(int)$id_category.'" style="font-size: 12px; width: 160px;" OnChange="changeCategoryMatch('.(int)($category['level']).', '.(int)$id_category.');">';
					
					foreach ($ref_categories[$category['id_category_ref_parent']]['children'] as $child)
						$var .= '<option value="'.(int)$ref_categories[$child]['id_ebay_category'].'"'.((int)$category['id_category_ref'] == (int)$child ? ' selected' : '').'>'.Tools::safeOutput($ref_categories[$child]['name']).'</option>';
					
					$var .= '</select>';
				}
			}
		}
	}
	else
	{
		$var .= '<select name="category['.(int)$id_category.']" id="categoryLevel'.(int)$level.'-'.(int)$id_category.'" rel="'.(int)$id_category.'" style="font-size: 12px; width: 160px;" OnChange="changeCategoryMatch('.(int)$level.', '.(int)$id_category.');">
			<option value="0">'.Tools::safeOutput(Tools::getValue('ch_cat_str')).'</option>';

		foreach ($ref_categories as $ref_id_category_ref => $category)
			if (isset($category['id_category_ref']) && (int)$category['id_category_ref'] == (int)$category['id_category_ref_parent'] && !empty($category['id_ebay_category']))
				$var .= '<option value="'. (int)$category['id_ebay_category'].'"'.((int)$category['id_category_ref'] == (int)$id_category_ref ? ' selected' : '').'>'.Tools::safeOutput($category['name']).'</option>';

		$var .= '</select>';
	}

	return $var;
}

