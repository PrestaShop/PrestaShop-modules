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

require_once '../../config/settings.inc.php';
require_once '../../config/defines.inc.php';



if (_PS_VERSION_ < '1.5')
{
	require_once 'KwixoUrlCallFrontController.php';
	$kwixo = new KwixoPayment();

	if (Tools::getValue('token') == Tools::getAdminToken($kwixo->getSiteid().$kwixo->getAuthkey()))
	{
		global $smarty;
		//Manage urlcall push, for PS 1.4
		$params = KwixoURLCallFrontController::ManageUrlCall();

		$payment_ok = $params['payment_status'];
		$errors = $params['errors'];
		$id_order = $params['id_order'];

		if ($id_order != false)
		{
			$order = new Order($id_order);
			$cart = new Cart($order->id_cart);
			$products = $cart->getProducts();
			$amount = $order->total_paid_real;
			$total_shipping = $order->total_shipping;
		} else
		{
			$products = false;
			$amount = false;
			$total_shipping = false;
		}

		$smarty->assign('payment_ok', $payment_ok);
		$smarty->assign('errors', $errors);
		$smarty->assign('amount', $amount);
		$smarty->assign('products', $products);
		$smarty->assign('total_shipping', $total_shipping);
		$smarty->assign('path_order', __PS_BASE_URI__.'order.php');
		$smarty->assign('path_history', __PS_BASE_URI__.'history.php');
		$smarty->assign('path_contact', __PS_BASE_URI__.'contact-form.php');

		echo $smarty->display(dirname(__FILE__).'/views/templates/front/urlcall.tpl');
		require_once(dirname(__FILE__).'/../../footer.php');
	}
	else
		header("Location: ../");
}
else
{
	header("Location: ../");
}