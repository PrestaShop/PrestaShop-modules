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

include_once 'kwixo.php';

$id_order = Tools::getValue('id_order');
$order = new Order((int) $id_order);

if (_PS_VERSION_ < '1.5')
	$kwixo = new KwixoPayment();
else
	$kwixo = new KwixoPayment($order->id_shop);

//token security
if (Tools::getValue('token') == Tools::getAdminToken($kwixo->getSiteid().$kwixo->getAuthkey().$kwixo->getLogin()))
{
	$module = new Kwixo();

	$res = $kwixo->getTagline($order->id_cart, Tools::getValue('tid'));
	$tag = new KwixoTaglineResponse($res);
	$module->manageKwixoTagline($tag, $order, Tools::getValue('tid'));
	$info_order = $module->getInfoKwixoOrder($id_order);

	foreach ($info_order as $info)
	{
		$kwixo_tagline_state = $info['kwixo_tagline_state'];
		$date_tagline = $info['date_tagline'];
	}

	echo $tag." => ".$module->_kwixo_order_statuses[$kwixo_tagline_state]."__".$date_tagline;
} else
	header("Location: ../");


