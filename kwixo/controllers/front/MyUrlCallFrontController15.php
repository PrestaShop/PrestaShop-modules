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

/**
 * UrlCallFrontController class for PS 1.5
 * Manage Urlcall
 * 
 */
class KwixoUrlcallModuleFrontController extends ModuleFrontController
{

	public function initContent()
	{
		parent::initContent();

		$params = KwixoURLCallFrontController::ManageUrlCall();

		$payment_ok = $params['payment_status'];
		$errors = $params['errors'];
		$id_order = $params['id_order'];

		if ($id_order != false)
		{
			$order = new Order($id_order);
			$cart = new Cart($order->id_cart);
			$products = $cart->getProducts();
			$amount = $order->total_paid_tax_incl;
			$total_shipping = $order->total_shipping;
		} else
		{
			$products = false;
			$amount = false;
			$total_shipping = false;
		}

		$link = new Link();

		$this->context->smarty->assign('payment_ok', $payment_ok);
		$this->context->smarty->assign('errors', $errors);
		$this->context->smarty->assign('amount', $amount);
		$this->context->smarty->assign('total_shipping', $total_shipping);
		$this->context->smarty->assign('products', $products);
		$this->context->smarty->assign('path_order', $link->getPageLink('order', true));
		$this->context->smarty->assign('path_history', $link->getPageLink('history', true));
		$this->context->smarty->assign('path_contact', $link->getPageLink('contact', true));

		$this->setTemplate('urlcall.tpl');
	}

}
