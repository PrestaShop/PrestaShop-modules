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

$config_path = dirname(__FILE__).'/../../../config/config.inc.php';
if (file_exists($config_path))
{
	include_once $config_path;
	include_once dirname(__FILE__).'/../ebay.php';

	class EbayLoadCat extends ebay
	{
		
		public function getTable()
		{
			if (Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
				return $this->l('You are not logged in');

			$categoryList = $this->_getChildCategories(Category::getCategories(Tools::getValue('id_lang')), version_compare(_PS_VERSION_, '1.5', '>') ? 1 : 0);

			$eBayCategoryList = Db::getInstance()->executeS('SELECT * 
				FROM `'._DB_PREFIX_.'ebay_category` 
				WHERE `id_category_ref` = `id_category_ref_parent`');

			if (version_compare(_PS_VERSION_, '1.5', '>'))
			{
				$rq_getCatInStock = '
					SELECT SUM(s.`quantity`) AS instockProduct, p.`id_category_default`
					FROM `'._DB_PREFIX_.'product` AS p
					INNER JOIN `'._DB_PREFIX_.'stock_available` AS s ON p.`id_product` = s.`id_product`
					WHERE 1 '.$this->addSqlRestrictionOnLang('s').'
					GROUP BY p.`id_category_default`';
			}
			else
			{
				$rq_getCatInStock = '
					SELECT SUM(`quantity`) AS instockProduct, `id_category_default`
					FROM `'._DB_PREFIX_.'product`	
					GROUP BY `id_category_default`';
			}

			$getCatsStock = Db::getInstance()->ExecuteS($rq_getCatInStock);
			$getCatInStock = array();
			foreach ($getCatsStock as $v)
				$getCatInStock[$v['id_category_default']] = $v['instockProduct'];

			// Loading categories
			$categoryConfigList = array();
			// init refcats
			$refCats = array();
			// init levels
			$levels = array();
			// init selects
			$SQL = '
				SELECT *, ec.`id_ebay_category` AS id_ebay_category
				FROM `'._DB_PREFIX_.'ebay_category` AS ec
				LEFT OUTER JOIN `'._DB_PREFIX_.'ebay_category_configuration` AS ecc
				ON ec.`id_ebay_category` = ecc.`id_ebay_category`
				ORDER BY `level`';
			$categoryConfigListTmp = Db::getInstance()->executeS($SQL);
			foreach ($categoryConfigListTmp as $category)
			{
				// Add datas
				if (isset($category['id_category']))
					$categoryConfigList[$category['id_category']] = $category;
				
				// add refcats
				if (!isset($refCats[$category['id_category_ref']]))
				{
					$refCats[$category['id_category_ref']] = $category;
					// Create children in refcats
					if ($category['id_category_ref'] != $category['id_category_ref_parent'])
					{
						if (!isset($refCats[$category['id_category_ref_parent']]['children']))
							$refCats[$category['id_category_ref_parent']]['children'] = array();
						if (!in_array($category['id_category_ref'], $refCats[$category['id_category_ref_parent']]['children']))
							$refCats[$category['id_category_ref_parent']]['children'][] = $category['id_category_ref'];
					}
				}
			}

			foreach ($categoryConfigList as &$category)
				$category['var'] = $this->getSelectors($refCats, $category['id_category_ref'], $category['id_category'], $category['level']).'<input type="hidden" name="category'.(int)$category['id_category'].'" value="'.(int)$category['id_ebay_category'].'" />';

			// Smarty datas
			$template_vars = array(
				'tabHelp' 					 => '&id_tab=7',
				'_path' 						 => $this->_path,
				'categoryList' 			 => $categoryList,
				'eBayCategoryList' 	 => $eBayCategoryList,
				'getCatInStock' 		 => $getCatInStock,
				'categoryConfigList' => $categoryConfigList,
				'request_uri' 			 => $_SERVER['REQUEST_URI'],
				'noCatSelected' 		 => $this->l('No category selected'),
				'noCatFound'				 => $this->l('No category found')
			);

			if (version_compare(_PS_VERSION_, '1.5', '>'))
				$smarty = $this->context->smarty;
			else
				global $smarty;

			$smarty->assign($template_vars);
			return $this->display(realpath(dirname(__FILE__).'/../'), '/views/templates/hook/table_categories.tpl');
		}
		
		/**
		* Create selectors
		* @param array $cats
		* @param int $id
		* @param int $cat
		* @param int $niv
		* @return string
		*/
		private function getSelectors($cats, $id, $cat, $niv)
		{
			$var = null;
			if ($niv > 1)
			{
				foreach ($cats as $k => $v)
				{
					if ($k == $id)
					{
						if (isset($cats[$v['id_category_ref_parent']]['children']))
						{
							if ($v['id_category_ref'] != $v['id_category_ref_parent'])
								$var .= $this->getSelectors($cats, $v['id_category_ref_parent'], $cat, (int)($niv - 1));
							$var .= '
								<select name="category'.$cat.'" id="categoryLevel'.(int)($v['level']).'-'.(int)$cat.'" rel="'.(int)$cat.'" style="font-size: 12px; width: 160px;" OnChange="changeCategoryMatch('.(int)($v['level']).', '.(int)$cat.');">';
							foreach ($cats[$v['id_category_ref_parent']]['children'] as $child)
								$var .= '<option value="'.$cats[$child]['id_ebay_category'].'"'.($v['id_category_ref'] == $child ? ' selected' : '').'>'.$cats[$child]['name'].'</option>';
							$var .= '
								</select>';
						}
					}
				}
			}
			else
			{
				$var .= '
					<select name="category'.$cat.'" id="categoryLevel'.(int)$niv.'-'.(int)$cat.'" rel="'.(int)$cat.'" style="font-size: 12px; width: 160px;" OnChange="changeCategoryMatch('.(int)$niv.', '.(int)$cat.');">
							<option value="0">'.$this->l('No category selected').'</option>';
		
				foreach ($cats as $k => $v)
				{
					if (isset($v['id_category_ref']) && $v['id_category_ref'] == $v['id_category_ref_parent'] && !empty($v['id_ebay_category']))
						$var .= '<option value="'.$v['id_ebay_category'].'"'.($v['id_category_ref'] == $id ? ' selected' : '').'>'.$v['name'].'</option>';
				}
				$var .= '
					</select>';
			}
			return $var;
		}

	}

	$ebay_cats = new EbayLoadCat();
	echo $ebay_cats->getTable();
}