<?php
/*
* 2013 TextMaster
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@textmaster.com so we can send you a copy immediately.
*
* @author JSC INVERTUS www.invertus.lt <help@invertus.lt>
* @copyright 2013 TextMaster
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of TextMaster
*/
include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/textmaster.php'); // module core

$module_instance = new TextMaster;

if (!Tools::isSubmit('token') || (Tools::isSubmit('token')) && Tools::getValue('token') != sha1(_COOKIE_KEY_.$module_instance->name)) exit;

if (Tools::isSubmit('add_project'))
{
    Tools::safePostVars();
    $args = $module_instance->formatProjectArguments();
    
    $result = $module_instance->saveProject($args, false, Tools::getValue('id_product'));
    
    if ($result !== false)
        die(Tools::jsonEncode(array('success' => true)));
    else
        die(Tools::jsonEncode(array('error' => $module_instance->error)));
}
elseif(Tools::isSubmit('quote_project'))
{
    Tools::safePostVars();
    $args = $module_instance->formatProjectArguments();
    
    $id_product = Tools::getValue('id_product');
    $price = $module_instance->quoteProject($args, false, $id_product);
    die(Tools::jsonEncode(array('success' => true, 'project_price' => $price)));
}
elseif(isset($_POST['get_document']))
{
    $messages = '';
    $id_document = Tools::getValue('id_document');
    $document = new TextMasterDocument($id_document);

    if (in_array($document->getStatus(), array('in_progress', 'in_review', 'incomplete')))
    {
        Context::getContext()->smarty->assign('messages', $document->getComments());
        $messages = Context::getContext()->smarty->fetch(TEXTMASTER_TPL_DIR . 'admin/project/messages.tpl');
    }
    
    die(Tools::jsonEncode(array('api' => $document->getApiData(), 'id_document' => $id_document, 'comments' => $messages)));
}
elseif (isset($_POST['submitComment']))
{
    $id_document = (int)Tools::getValue('id_document');
    if(!$message = pSQL(Tools::getValue('message')))
    {
        $result = $module_instance->l('Comment cannot be empty', 'textmaster.ajax');
    }
    else
    {
        $document = new TextMasterDocument($id_document);       
        $result = $document->comment($message);
    }
    if ($result === true)
    {
        Context::getContext()->smarty->assign('messages', $document->getComments());
        $messages = Context::getContext()->smarty->fetch(TEXTMASTER_TPL_DIR . 'admin/project/messages.tpl');
        
        die(Tools::jsonEncode(array('errors' => false, 'messages' => $messages)));
    }
    else
        die(Tools::jsonEncode(array('errors' => true,
                                    'error' => $result)));
}
elseif (Tools::isSubmit('getProductList'))
{
    $id_category = (int) Tools::getValue('id_category');
    $order_link = Tools::getValue('order_url');
    $filter = Tools::getValue('filtering');
    $orderBy = '';
    $orderWay = '';
    
    $pagination = (int)Tools::getValue('pagination', '20');
    $page = (int)Tools::getValue('current_page', '1');
    $start = ($pagination * $page) - $pagination;
    
    $_POST['pagination'] = $pagination;
    $_POST['submitFilterproductsList'] = $page;
    
    if ($order_link)
    {
        $order_link = explode('&', $order_link);
        foreach ($order_link AS $item)
        {
            if (strpos($item,'productsListOrderby') !== false)
            {
                $pieces = explode('=', $item);
                $orderBy = $pieces[1];
            }
            if (strpos($item,'productsListOrderway') !== false)
            {
                $pieces = explode('=', $item);
                $orderWay = $pieces[1];
            }
        }
    }
    
    $products = $module_instance->getProductsByCategory((int) $id_category, false, $orderBy, $orderWay, $filter, $start, $pagination);
    $list_total = count($module_instance->getProductsByCategory((int) $id_category, true));
    $pagination = array(20, 50, 100, 300);
    
    $page = (int)Tools::getValue('submitFilterproject');
    if (!$page)
        $page = 1;
    
    $total_pages = ceil($list_total / Tools::getValue('pagination', 50));

    if (!$total_pages) 
        $total_pages = 1;
    $selected_pagination = Tools::getValue(
        'pagination',
        isset(Context::getContext()->cookie->{'project_pagination'}) ? Context::getContext()->cookie->{'project_pagination'} : null
    );
    
    Context::getContext()->smarty->assign(array(
        'products' => $products,
        'list_total' => $list_total,
        'page' => $page,
        'menu' => 'new_project',
        'selected_pagination' => $selected_pagination,
        'pagination' => $pagination,
        'total_pages' => $total_pages,
        'cookie_order_by' => $orderBy,
        'cookie_order_way' => $orderWay
    ));
    
    $fields = array('id_product', 'name', 'reference', 'category', 'price', 'final_price', 'quantity', 'active');
		foreach ($fields AS $key => $values)
			if (Context::getContext()->cookie->__isset('productsListFilter_'.$values))
				Context::getContext()->smarty->assign('cookie_productsListFilter_'.$values, Context::getContext()->cookie->{'productsListFilter_'.$values});
    
    $html = Context::getContext()->smarty->fetch(_PS_MODULE_DIR_.$module_instance->name.'/views/templates/admin/project/products_selection_table.tpl');
    die($html);
}
elseif (Tools::isSubmit('getSelectedProducts'))
{
    $selected_products_ids = Tools::getValue('selected_ids');
    
    $pagination = (int)Tools::getValue('pagination', '20');
    $page = (int)Tools::getValue('current_page', '1');
    $start = ($pagination * $page) - $pagination;
    
    $_POST['pagination'] = $pagination;
    $_POST['submitFilterproductsList'] = $page;
    
    $selected_products_data = $module_instance->getSelectedProducts($selected_products_ids, $start, $pagination);
    $list_total = count($module_instance->getSelectedProducts($selected_products_ids));
    $pagination = array(20, 50, 100, 300);
    
    $page = (int)Tools::getValue('submitFilterproject');
    if (!$page)
        $page = 1;
    
    $total_pages = ceil($list_total / Tools::getValue('pagination', 50));

    if (!$total_pages) 
        $total_pages = 1;
    $selected_pagination = Tools::getValue(
        'pagination',
        isset(Context::getContext()->cookie->{'project_pagination'}) ? $selected_products_data->cookie->{'project_pagination'} : null
    );
    
    Context::getContext()->smarty->assign(array(
        'products' => $selected_products_data,
        'list_total' => $list_total,
        'page' => $page,
        'menu' => 'new_project',
        'selected_pagination' => $selected_pagination,
        'pagination' => $pagination,
        'total_pages' => $total_pages
    ));
    
    $html = Context::getContext()->smarty->fetch(_PS_MODULE_DIR_.$module_instance->name.'/views/templates/admin/project/products_to_select.tpl');
    die($html);
}