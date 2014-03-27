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

require_once('../../config/config.inc.php');
require_once(_PS_ROOT_DIR_.'/init.php');
require_once(dirname(__FILE__).'/classes/SCFields.php');
require_once(dirname(__FILE__).'/backward_compatibility/backward.php');

$so = new SCfields('API');

$fields = $so->getFields();

/* Build back the fields list for SoColissimo, gift infos are send using the JS */
$inputs = array();
foreach ($_GET as $key => $value)
	if (in_array($key, $fields))
		$inputs[$key] = Tools::getValue($key);

$param_plus = array(
	/* Get the data set before */
	Tools::getValue('trParamPlus'),
	Tools::getValue('gift'),
	$so->replaceAccentedChars(Tools::getValue('gift_message'))
);

$inputs['trParamPlus'] = implode('|', $param_plus);
/* Add signature to get the gift and gift message in the trParamPlus */
$inputs['signature'] = $so->generateKey($inputs);

$socolissimo_url = Configuration::get('SOCOLISSIMO_URL');
Context::getContext()->smarty->assign(array(
	'inputs' => $inputs,
	'socolissimo_url' => $socolissimo_url
));

Context::getContext()->smarty->display(_PS_MODULE_DIR_.'socolissimo/views/templates/front/redirect.tpl');
