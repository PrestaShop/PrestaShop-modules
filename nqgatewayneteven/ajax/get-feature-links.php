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

$action = Tools::getValue('action');
$id = Tools::getValue('id');
$id_order_gateway_feature = Tools::getValue('id_order_gateway_feature');

if ($action != 'display')
	$data = array('id' => substr($id, 1), 'type' => substr($id, 0, 1));

if ($action == 'add')
{
	$result = Db::getInstance()->getValue('
				SELECT COUNT(*)
				FROM `'._DB_PREFIX_.'orders_gateway_feature_link`
				WHERE '.($data['type'] == 'A' ? '`id_attribute_group`' : '`id_feature`').' = '.(int)$data['id'].'
				AND `id_order_gateway_feature` = '.(int)$id_order_gateway_feature
			);

	if (!$result)
		Db::getInstance()->Execute('
		    INSERT INTO `'._DB_PREFIX_.'orders_gateway_feature_link`
		    ( `id_order_gateway_feature`, '.($data['type'] == 'A' ? '`id_attribute_group`' : '`id_feature`').' )
		    VALUES
		    ('.(int)$id_order_gateway_feature.', '.(int)$data['id'].')
		');
}
elseif ($action == 'delete')
	Db::getInstance()->Execute('
	    DELETE FROM `'._DB_PREFIX_.'orders_gateway_feature_link`
	    WHERE `id_order_gateway_feature` = '.(int)$id_order_gateway_feature.'
	        AND '.($data['type'] == 'A' ? '`id_attribute_group`' : '`id_feature`').' = '.(int)$data['id']
    );

$feature_links = Db::getInstance()->ExecuteS('
		SELECT ogfl.*, agl.`name` as attribute_name, fl.`name` as feature_name, ogf.`name`
		FROM `'._DB_PREFIX_.'orders_gateway_feature_link` ogfl
		LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl
		    ON (agl.`id_attribute_group` = ogfl.`id_attribute_group` AND agl.`id_lang` = '.(int)$cookie->id_lang.')
		LEFT JOIN `'._DB_PREFIX_.'feature_lang` fl
		    ON (fl.`id_feature` = ogfl.`id_feature` AND fl.`id_lang` = '.(int)$cookie->id_lang.')
		LEFT JOIN `'._DB_PREFIX_.'orders_gateway_feature` ogf
		    ON (ogf.`id_order_gateway_feature` = ogfl.`id_order_gateway_feature`)'
	);

$response = '';
foreach ($feature_links as $feature_link)
{
	if ($feature_link['id_attribute_group'])
	{
		$element_id = 'A'.$feature_link['id_attribute_group'].'-'.$feature_link['id_order_gateway_feature'];
		$element_prestashop_name = $feature_link['attribute_name'];
		$element_netven_name = $feature_link['name'];
	}
	else
	{
		$element_id = 'F'.$feature_link['id_feature'].'-'.$feature_link['id_order_gateway_feature'];
		$element_prestashop_name = $feature_link['feature_name'];
		$element_netven_name = $feature_link['name'];
	}
	
	$response .= '  <li id="'.$element_id.'" >
                        <span class="delete_link" style="cursor:pointer;">
                            <img src="../img/admin/disabled.gif" alt="X" />
                        </span>
                        <span class="prestashop_name">'.$element_prestashop_name.'</span>
                        /
                        <span class="netven_name">'.$element_netven_name.'</span>
                    </li>';

}

die($response);