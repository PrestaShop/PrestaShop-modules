<?php
/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future.If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

$hook_list = array(
	'header',
	'backOfficeHeader',
	'addproduct',
	'updateproduct',
	'deleteproduct',
	'updateProductAttribute',
	'cart',
	'leftColumn',
	'rightColumn',
	'productfooter',
	'shoppingCart',
	'home'
);

if (version_compare(_PS_VERSION_, '1.4', '>='))
{
	array_push($hook_list, 'categoryAddition');
	array_push($hook_list, 'categoryUpdate');
	array_push($hook_list, 'categoryDeletion');
}

if (version_compare(_PS_VERSION_, '1.5', '>='))
{
	array_push($hook_list, 'authentication');
	array_push($hook_list, 'actionObjectOrderAddAfter');
}

