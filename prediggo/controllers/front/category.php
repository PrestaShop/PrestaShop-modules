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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoConfig.php');
require_once(_PS_MODULE_DIR_.'prediggo/classes/PrediggoCall.php');

class PrediggoCategoryModuleFrontController extends ModuleFrontController
{
    /** @var PrediggoSearchConfig Object PrediggoSearchConfig */
    private $oPrediggoConfig;
    /** @var PrediggoCall Object PrediggoCall */
    private $oPrediggoCall;
    /** @var string Search query */
    private $sQuery;
    /** @var string Prediggo refine option */
    private $sRefineOption;
    /** @var string path of the log repository */
    private $sRepositoryPath;

    /**
     * Initialise the object variables
     */
    public function __construct()
    {
        parent::__construct();

        $this->oPrediggoConfig = new PrediggoConfig($this->context);
        if(!$this->oPrediggoConfig->category_active)
            Tools::redirect('index.php');

        $this->sRepositoryPath = _PS_MODULE_DIR_.'prediggo/logs/';

        $this->oPrediggoCall = new PrediggoCall($this->oPrediggoConfig->web_site_id, $this->oPrediggoConfig->server_url_recommendations);
        $this->sQuery = Tools::getValue('q');
        $this->sRefineOption = Tools::getValue('refineOption');
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        if($oPrediggoResult = $this->getSearch($this->getParams()))
        {
            if(isset($this->context->cookie->id_compare))
                $this->context->smarty->assign('compareProducts', CompareProduct::getCompareProducts((int)$this->context->cookie->id_compare));

            $catName = $this->getCategoryName((int)Context::getContext()->cookie->id_lang);
            $assign =
                array(
                    'page_name' 					=> 'categoryNameComplement',
                    'sPrediggoQuery' 				=> (String)$catName[0]['name'],
                    'aPrediggoProducts' 			=> $this->oPrediggoCall->getProducts($oPrediggoResult, (int)Context::getContext()->cookie->id_lang),
                    'aDidYouMeanWords' 				=> $oPrediggoResult->getDidYouMeanWords(),
                    'aSortingOptions' 				=> $oPrediggoResult->getSortingOptions(),
                    'aCancellableFiltersGroups' 	=> $oPrediggoResult->getCancellableFiltersGroups(),
                    'aDrillDownGroups' 				=> $oPrediggoResult->getDrillDownGroups(),
                    'aChangePageLinks' 				=> $oPrediggoResult->getChangePageLinks(),
                    'oSearchStatistics' 			=> $oPrediggoResult->getSearchStatistics(),
                    'bSearchandizingActive' 		=> $this->oPrediggoConfig->searchandizing_active,
                    'aCustomRedirections' 			=> $oPrediggoResult->getCustomRedirections(),
                    'comparator_max_item' 			=> (int)(Configuration::get('PS_COMPARATOR_MAX_ITEM')),
                    'sImageType' 					=> (Tools::version_compare(_PS_VERSION_, '1.5.1', '>=')?'home_default':'home'),
                    'bRewriteEnabled'				=> (int)Configuration::get('PS_REWRITING_SETTINGS'),
                );

            return $assign;
        }
        parent::initContent();
        $this->setTemplate($this->oPrediggoConfig->category_0_template_name);
    }


    private function getCategoryName($id_lang){
        $sql = 'SELECT `name` FROM `'._DB_PREFIX_.'category_lang` WHERE `id_category` = '.(int)$this->id.' and `id_lang` = '.(int)$id_lang.'';
        $aQueryResult = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
        $aItems = $aQueryResult;
        unset($sql);
        unset($aRecommendedItems);
        unset($aQueryResult);
        return $aItems;
    }

    public function getParams(){

        $catName = $this->getCategoryName((int)Context::getContext()->cookie->id_lang);
        $params = array(
            'customer' 	=> Context::getContext()->customer,
            'cookie' 	=> Context::getContext()->cookie,
            'cart' 		=> Context::getContext()->cart,
            'query' 	=> (String)$catName[0]['name'],
            'nb_items' 	=> 10,
            'option' 	=> Tools::getValue('refineOption')
        );

        return $params;
    }

    public function getConfig(){

        if($this->oPrediggoConfig->category_active)
            return true;
        else
            return false;
    }

    /**
     * Set the Media (CSS / JS) of the page
     */
    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS(array(
            _THEME_CSS_DIR_.'product_list.css' => 'all'
        ));

        if (Configuration::get('PS_COMPARATOR_MAX_ITEM') > 0)
            $this->addJS(_THEME_JS_DIR_.'products-comparison.js');
    }
    /**
     * Set the search query
     *
     * @param string $sQuery Search query
     */
    function setQuery($sQuery)
    {
        $this->sQuery = $sQuery;
    }

    /**
     * Set the refine option
     *
     * @param string $sRefineOption Refine option
     */
    function setRefineOption($sRefineOption)
    {
        $this->sRefineOption = $sRefineOption;
    }

    /**
     * Get the current search products
     *
     * @return array list of products
     */
    public function getProducts($oPrediggoResult)
    {
        return $this->oPrediggoCall->getProducts($oPrediggoResult, (int)$this->context->cookie->id_lang);
    }

    // Display the categories
    public function displayCategories($params){

        if(!$this->oPrediggoConfig->web_site_id_checked)
            return false;

        if (!$this->isCached('blockcategories.tpl', $this->getCacheId()))
        {
            // Get all groups for this customer and concatenate them as a string: "1,2,3..."
            $groups = implode(', ', Customer::getGroupsStatic((int)$this->context->customer->id));
            $maxdepth = Configuration::get('BLOCK_CATEG_MAX_DEPTH');
            if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT DISTINCT c.id_parent, c.id_category, cl.name, cl.description, cl.link_rewrite
				FROM `'._DB_PREFIX_.'category` c
				INNER JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND cl.`id_lang` = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('cl').')
				INNER JOIN `'._DB_PREFIX_.'category_shop` cs ON (cs.`id_category` = c.`id_category` AND cs.`id_shop` = '.(int)$this->context->shop->id.')
				WHERE (c.`active` = 1 OR c.`id_category` = '.(int)Configuration::get('PS_HOME_CATEGORY').')
				AND c.`id_category` != '.(int)Configuration::get('PS_ROOT_CATEGORY').'
				'.((int)$maxdepth != 0 ? ' AND `level_depth` <= '.(int)$maxdepth : '').'
				AND c.id_category IN (SELECT id_category FROM `'._DB_PREFIX_.'category_group` WHERE `id_group` IN ('.pSQL($groups).'))
				ORDER BY `level_depth` ASC, '.(Configuration::get('BLOCK_CATEG_SORT') ? 'cl.`name`' : 'cs.`position`').' '.(Configuration::get('BLOCK_CATEG_SORT_WAY') ? 'DESC' : 'ASC')))
                return;

            $resultParents = array();
            $resultIds = array();
            $isDhtml = (Configuration::get('BLOCK_CATEG_DHTML') == 1 ? true : false);

            foreach ($result as &$row)
            {
                $resultParents[$row['id_parent']][] = &$row;
                $resultIds[$row['id_category']] = &$row;
            }

            $blockCategTree = $this->getTree($resultParents, $resultIds, Configuration::get('BLOCK_CATEG_MAX_DEPTH'));
            unset($resultParents, $resultIds);

            $this->smarty->assign('blockCategTree', $blockCategTree);
            $this->smarty->assign('branche_tpl_path', _PS_MODULE_DIR_.'prediggo/views/templates/front/category-tree-branch.tpl');
            $this->smarty->assign('isDhtml', $isDhtml);
        }

        $id_category = (int)Tools::getValue('id_category');
        $id_product = (int)Tools::getValue('id_product');

        if (Tools::isSubmit('id_category'))
        {
            $this->context->cookie->last_visited_category = (int)$id_category;
            $this->smarty->assign('currentCategoryId', $this->context->cookie->last_visited_category);
        }

        if (Tools::isSubmit('id_product'))
        {
            if (!isset($this->context->cookie->last_visited_category)
                || !Product::idIsOnCategoryId($id_product, array('0' => array('id_category' => $this->context->cookie->last_visited_category)))
                || !Category::inShopStatic($this->context->cookie->last_visited_category, $this->context->shop))
            {
                $product = new Product((int)$id_product);
                if (isset($product) && Validate::isLoadedObject($product))
                    $this->context->cookie->last_visited_category = (int)$product->id_category_default;
            }
            $this->smarty->assign('currentCategoryId', (int)$this->context->cookie->last_visited_category);
        }

        $display = $this->display(__FILE__, 'views/templates/front/blockcategories.tpl', $this->getCacheId());
        return $display;
    }

    //get cache ID
    protected function getCacheId($name = null)
    {
        parent::getCacheId($name);

        $groups = implode(', ', Customer::getGroupsStatic((int)$this->context->customer->id));
        $id_product = (int)Tools::getValue('id_product', 0);
        $id_category = (int)Tools::getValue('id_category', 0);
        $id_lang = (int)$this->context->language->id;
        return 'blockcategories|'.(int)Tools::usingSecureMode().'|'.$this->context->shop->id.'|'.$groups.'|'.$id_lang.'|'.$id_product.'|'.$id_category;
    }

    //get category tree
    public function getTree($resultParents, $resultIds, $maxDepth, $id_category = null, $currentDepth = 0)
    {
        if (is_null($id_category))
            $id_category = $this->context->shop->getCategory();

        $children = array();
        if (isset($resultParents[$id_category]) && count($resultParents[$id_category]) && ($maxDepth == 0 || $currentDepth < $maxDepth))
            foreach ($resultParents[$id_category] as $subcat)
                $children[] = $this->getTree($resultParents, $resultIds, $maxDepth, $subcat['id_category'], $currentDepth + 1);
        if (!isset($resultIds[$id_category]))
            return false;
        $return = array('id' => $id_category, 'link' => $this->context->link->getCategoryLink($id_category, $resultIds[$id_category]['link_rewrite']),
            'name' => $resultIds[$id_category]['name'], 'desc'=> $resultIds[$id_category]['description'],
            'children' => $children);
        return $return;
    }
}
