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
 *  @author Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright  2007-2014 PrestaShop SA / 1997-2013 Quadra Informatique
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('../../modules/socolissimo/socolissimo.php');

/* To have context available and translation */
$socolissimo = new Socolissimo();

/* Default answer values => key */
$result = array(
	'answer' => true,
	'msg' => ''
);

/* Check Token */

if (Tools::getValue('token') != sha1('socolissimo'._COOKIE_KEY_.Context::getContext()->cart->id))
{
	$result['answer'] = false;
	$result['msg'] = $socolissimo->l('Invalid token');
}

/* If no problem with token but no delivery available */
if ($result['answer'] && !($result = $socolissimo->getDeliveryInfos(Context::getContext()->cart->id, Context::getContext()->customer->id)))
{
	$result['answer'] = false;
	$result['msg'] = $socolissimo->l('No delivery information selected');
}

header('Content-type: application/json');
echo json_encode($result);
exit(0);
