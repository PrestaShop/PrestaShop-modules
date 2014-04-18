<?php
/**
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/*Load the correct class version for PS 1.4 or PS 1.5*/
if (version_compare(_PS_VERSION_, '1.5', '<'))
{
	include_once 'controllers/front/MyUrlCallFrontController14.php';
	require_once(dirname(__FILE__).'/../../config/config.inc.php');
	require_once(dirname(__FILE__).'/../../init.php');
	require_once(dirname(__FILE__).'/../../header.php');
}
else
	include_once 'controllers/front/MyUrlCallFrontController15.php';

include_once 'lib/includes/includes.inc.php';
include_once 'kwixo.php';

/**
 * Urlcall push management
 * 
 */
class KwixoURLCallFrontController extends KwixoUrlcallModuleFrontController
{

	public $ssl = true;

	public static function manageUrlCall()
	{
		$payment = new Kwixo();

		if (!$payment->isInstalled('kwixo'))
		{
			KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Module Kwixo non installé, retour UrlCall échoué');
			return false;
		}
		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$cookie = new Cookie('ps');
			$cart = new Cart($cookie->id_cart);
		}
		else
		{
			$cookie = Context::getContext()->cookie;
			$cart = Context::getContext()->cart;
		}
		$errors = array();
		$payment_ok = false;
		$params = array();

		$transaction_id = Tools::getValue('TransactionID');
		$ref_id = Tools::getValue('RefID');

		//Multishop
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$kwixo = new KwixoPayment();
		else
			$kwixo = new KwixoPayment($cart->id_shop);
		if ($kwixo->getAuthKey() == '')
		{
			KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Clé privée Kwixo vide, retour UrlCall échoué');
			return false;
		}

		$md5 = new KwixoMD5();
		$waitedhash = $md5->hash($kwixo->getAuthKey().$ref_id.$transaction_id);
		$receivedhash = Tools::getValue('HashControl', '0');

		$id_order = false;

		//Hash control
		if ($waitedhash != $receivedhash)
			KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Hash control invalide (les données ne proviennent pas de Kwixo)');
		else
		{
			//check xml_params for urlcall payment
			$xml_params = $payment->checkUrlCallXMLParams();

			if ($xml_params['errors'] == 0)
			{
				$tag = Tools::getValue('Tag', false);
				$id_cart = $xml_params['id_cart'];
				$amount = $xml_params['amount'];
				$order_created = $xml_params['order_created'];
				$payment_type = $xml_params['payment_type'];

				switch ($tag)
				{
					//Give up payment or payment refused by bank -> back to cart without order creation
					case '0':
						KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'URLCall abandon paiement :
							id_cart = '.$id_cart.(!$order_created ? '' : ' / id_order = '.Order::getOrderByCartId($id_cart)).' / tag = '.$tag);
						$payment_ok = false;
						break;
					case'2':
						$errors[] = $payment->l('Your payment has been refused.');
						KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'URLCall :
							id_cart = '.$id_cart.(!$order_created ? '' : ' / id_order = '.Order::getOrderByCartId($id_cart)).' / tag = '.$tag);
						$payment_ok = false;
						break;

					//Payment accepted -> order creation with waiting payment status and back to confirmation page
					case '1':
						//order validation
						if ($order_created == false)
							$payment->validateOrder((int)$cart->id, (int)Configuration::get('KW_OS_WAITING'), $amount,
								$payment->displayName, null, '', $cart->id_currency, false, $cart->secure_key);

						$payment_ok = true;

						//get id_order to update database
						$id_order = Order::getOrderByCartId($id_cart);

						KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Paiement accepté : $order->id = '.$id_order);

						//Insert in kwixo order with urlcall method
						$payment->manageKwixoOrder($id_order, '', $transaction_id, $id_cart, $payment_type, 'urlcall');

						//cart clean
						if ($cart->id == (int)$cookie->last_id_cart)
							unset($cookie->id_cart);
						break;

					//for unknowned tag
					default:
						//error saved
						$errors[] = $payment->l('One or more error occured during the validation')."\n";
						KwixoLogger::insertLogKwixo(__METHOD__.' : '.__LINE__, 'Tag inconnu '.$tag.' recu.');

						//cart clean
						if ($cart->id == (int)$cookie->last_id_cart)
							unset($cookie->id_cart);
						$payment_ok = false;
						break;
				}
			}
			else
			{
				//error saved
				$errors[] = $payment->l('One or more error occured during the validation')."\n";
				if ($cookie->id_cart == (int)$cookie->last_id_cart)
					unset($cookie->id_cart);
			}

			$params['payment_status'] = $payment_ok;
			$params['errors'] = $errors;
			$params['id_order'] = $id_order;

			return $params;
		}
	}
}
