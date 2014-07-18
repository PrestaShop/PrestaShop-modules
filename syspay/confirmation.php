<?php
/**
* 2007-2011 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2011 PrestaShop SA
*  @version   Release: $Revision: 7732 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
if (version_compare(_PS_VERSION_, '1.5', '<'))
	include(dirname(__FILE__).'/../../header.php');

include(dirname(__FILE__).'/syspay.php');
require_once(dirname(__FILE__).'/tools/loader.php');

	$syspay = new SysPay();

	$mode = Configuration::get('SYSPAY_MODE');
	if ($mode == 0)
		$secrets = array(
			Configuration::get('SYSPAY_TEST_MID') => Configuration::get('SYSPAY_TEST_SHA1_PRIVATE')
		);
	else
		$secrets = array(
			Configuration::get('SYSPAY_LIVE_MID') => Configuration::get('SYSPAY_LIVE_SHA1_PRIVATE')
		);

	try {
		$redirect = new Syspay_Merchant_Redirect($secrets);
		$payment = $redirect->getResult($_REQUEST);
		$id_module = (int)$syspay->id;
		$key = Db::getInstance()->getValue('SELECT secure_key FROM '._DB_PREFIX_.'customer WHERE id_customer = '.(int)Context::getContext()->customer->id);
		$id_cart = $payment->getDescription();
		$link = new Link();

		Context::getContext()->smarty->assign(array(
			'id_module' => $id_module,
			'id_cart' => $id_cart,
			'status' => $payment->getStatus(),
			'redirect' => $payment->getRedirect(),
			'key' => $key,
			'syspay_link' => (method_exists($link, 'getPageLink') ? $link->getPageLink('order-confirmation.php') : _PS_BASE_URL_.'order-confirmation.php')
		));

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			if ($payment->getStatus() == 'CANCELLED')
				echo $syspay->display(dirname(__FILE__), 'views/templates/hook/confirmation-error.tpl');
			else
				echo $syspay->display(dirname(__FILE__), 'views/templates/hook/waiting.tpl');
			include(dirname(__FILE__).'/../../footer.php');
		}
		else
		{
			$front_controller = new FrontController();
			if ($payment->getStatus() == 'CANCELLED')
				$front_controller->setTemplate(dirname(__FILE__).'/views/templates/hook/confirmation-error.tpl');
			else
				$front_controller->setTemplate(dirname(__FILE__).'/views/templates/hook/waiting.tpl');
			$front_controller->run();
		}
	} catch (Syspay_Merchant_RedirectException $e) {
		header(':', true, 500);
		printf("Something went wrong while processing the message: (%d) %s\n",
					$e->getCode(), $e->getMessage());
	}
