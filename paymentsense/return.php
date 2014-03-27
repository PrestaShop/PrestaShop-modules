<?php
/*
* Prestashop PaymentSense Re-Directed Payment Module
* Copyright (C) 2013 PaymentSense.
*
* This program is free software: you can redistribute it and/or modify it under the terms
* of the AFL Academic Free License as published by the Free Software Foundation, either
* version 3 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
* without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
* See the AFL Academic Free License for more details. You should have received a copy of the
* AFL Academic Free License along with this program. If not, see <http://opensource.org/licenses/AFL-3.0/>.
*
*  @author PaymentSense <devsupport@paymentsense.com>
*  @copyright  2013 PaymentSense
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
This file is part of the Prestashop PaymentSense Re-Directed Payment Module
See paymentsense.php for Licensing and support info.
File Last Modified: 12/03/2013 - By Shaun Ponting - Opal Creations
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/paymentsense.php');

$paymentsense = new PaymentSense();
if ($paymentsense->active)
{
	if ((int)(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
		$rewrited_url = __PS_BASE_URI__;

	/*include(dirname(__FILE__).'/../../header.php');*/
	$controller=new FrontController();
	$controller->init();
	$controller->initContent();
	$controller->setMedia();
	$controller->displayHeader();
	
	
	if (!isset($link))
		$link = new Link();
	$smarty->assign('cartURL', $link->getPageLink('order.php?step=1'));
	$smarty->assign('contactURL', $link->getPageLink('contact-form.php'));

	$smarty->display(dirname(__FILE__).'/views/templates/front/return.tpl');

	$controller->displayFooter();
}