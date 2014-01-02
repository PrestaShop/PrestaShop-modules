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

//Load the correct class version for PS 1.4 or PS 1.5
if (_PS_VERSION_ < '1.5')
{
	include_once 'controllers/front/MyUrlSysFrontController14.php';
	require_once(dirname(__FILE__).'/../../config/config.inc.php');
	require_once(dirname(__FILE__).'/../../init.php');
	require_once(dirname(__FILE__).'/../../header.php');
}
else
	include_once 'controllers/front/MyUrlSysFrontController15.php';

include_once 'lib/includes/includes.inc.php';
include_once 'kwixo.php';

/**
 * Urlsys push management
 * 
 */
class KwixoURLSysFrontController extends KwixoUrlSysModuleFrontController
{

	public static function ManageUrlSys()
	{
		$payment = new Kwixo();
		
		if(!$payment->isInstalled('kwixo')){
			 KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Module Kwixo non installé, retour UrlSys échoué');
			 return false;
		 }

		$transactionID = Tools::getValue('TransactionID');
		$refID = Tools::getValue('RefID');
		$tag = Tools::getValue('Tag');
		$id_cart = Tools::getValue('custom', false);
		$amount = Tools::getValue('amount', false);

		$cart = new Cart((int) $id_cart);

		//Multishop
		if (_PS_VERSION_ < '1.5')
			$kwixo = new KwixoPayment();
		else
			$kwixo = new KwixoPayment($cart->id_shop);

		
		if ($kwixo->getAuthKey() == '')
		{
			KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Clé privée Kwixo vide, retour UrlSys échoué');
			return false;
		}
		
		$md5 = new KwixoMD5();

		$waitedhash = $md5->hash($kwixo->getAuthKey().$refID.$transactionID);
		$receivedhash = Tools::getValue('HashControl', '0');

		//Hash control
		if ($waitedhash != $receivedhash)
			KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'URLSys erreur : HashControl invalide (valeur attendue = "'.$waitedhash.'", valeur reçue = "'.$receivedhash.'"). IP expediteur : '.Tools::getRemoteAddr());
		else
		{
			//if cart if empty : error and exit
			if (!$cart->id)
			{
				KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, "Le panier pour la commande $refid/$transactionid n'existe pas.");
				exit;
			}

			global $cookie;

			//Give order_id
			$id_order = Order::getOrderByCartId($cart->id);

			if ($id_order !== false)
			{
				$order = new Order((int) $id_order);
				KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'URLSys : id_cart = '.$id_cart.(!Order::getOrderByCartId($id_cart) ? '' : ' | id_order = '.Order::getOrderByCartId($id_cart)).' | tag = '.$tag);
			}
			else
				KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'URLSys : order false');

			switch ($tag)
			{
				//Give up payment, tag sent after 1 hour
				case 0:
					KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'URLSys abandon après 1h : id_cart = '.$id_cart.(!Order::getOrderByCartId($id_cart) ? '' : ' | id_order = '.Order::getOrderByCartId($id_cart)).' | tag = '.$tag);
					break;

				//Accepted payment
				case 1:
				case 13:
				case 14:
				case 10:
					//Retrieve score if present
					$score = Tools::getValue('Score', false);
					//if order current state in cancelled or waiting or under control or credit status, status updated
					if ($id_order === false || in_array($order->getCurrentState(), array((int) _PS_OS_CANCELED_, (int) Configuration::get('KW_OS_WAITING'), (int) Configuration::get('KW_OS_CREDIT'), (int) Configuration::get('KW_OS_CONTROL'))))
						if ($score == 'positif')
							$psosstatus = (int) Configuration::get('KW_OS_PAYMENT_GREEN');
						elseif ($score == 'negatif')
							$psosstatus = (int) Configuration::get('KW_OS_PAYMENT_RED');
						else
							$psosstatus = (int) _PS_OS_PAYMENT_;
					break;

				//Payment refused
				case 2:
					$psosstatus = (int) _PS_OS_CANCELED_;
					break;

				//order under control
				case 3:
					//if order current state in cancelled or waiting or credit status, status updated
					if ($id_order === false || in_array($order->getCurrentState(), array((int) _PS_OS_CANCELED_, (int) Configuration::get('KW_OS_WAITING'), (int) Configuration::get('KW_OS_CREDIT'))))
						$psosstatus = (int) Configuration::get('KW_OS_CONTROL');
					break;

				//order on waiting status
				case 4:
					if ($id_order === false)
						$psosstatus = (int) Configuration::get('KW_OS_WAITING');

					break;
				//order under credit status
				case 6:
					//if order current state in cancelled or waiting, status updated
					if ($id_order === false || in_array($order->getCurrentState(), array((int) _PS_OS_CANCELED_, (int) Configuration::get('KW_OS_WAITING'))))
						$psosstatus = (int) Configuration::get('KW_OS_CREDIT');
					break;
				//payment refused
				case 11:
				case 12:
					//if order current state in cancelled or waiting, status updated
					if ($id_order === false || in_array($order->getCurrentState(), array((int) _PS_OS_CANCELED_, (int) Configuration::get('KW_OS_WAITING'), (int) Configuration::get('KW_OS_CREDIT'), (int) Configuration::get('KW_OS_CONTROL'))))
						$psosstatus = (int) _PS_OS_CANCELED_;

					break;
				//payment cancelled
				case 101:
					$psosstatus = (int) _PS_OS_CANCELED_;
					break;

				//delivery done
				case 100:
					if ($id_order === false || !in_array($order->getCurrentState(), array((int) _PS_OS_DELIVERED_, (int) _PS_OS_PREPARATION_, (int) _PS_OS_SHIPPING_, (int) _PS_OS_PAYMENT_)))
						$psosstatus = (int) _PS_OS_PAYMENT_;
					break;

				default:
					break;

					KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Appel URLSys : id_cart = '.$id_cart.(!Order::getOrderByCartId($id_cart) ? '' : ' | id_order = '.Order::getOrderByCartId($id_cart)).' | tag = '.$tag);
			}
		}

		//Validate order and update status
		if (isset($psosstatus))
		{
			if ($id_order === false)
			{
				$feedback = 'Order Create';
				$payment->validateOrder((int) $cart->id, $psosstatus, $amount, $payment->displayName, $feedback, NULL, $cart->id_currency);
				$id_order = Order::getOrderByCartId($cart->id);
				$payment->manageKwixoOrder($id_order, $tag, $transactionID, $id_cart, 'urlsys');
				if ($cookie->id_cart == (int) $cookie->last_id_cart)
					unset($cookie->id_cart);
			}
			else
			{
				//update order history
				$order->setCurrentState($psosstatus);
			}
		}
	}

}

