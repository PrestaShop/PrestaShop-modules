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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class PagSeguroPaymentModuleFrontController extends ModuleFrontController
{    
	public $ssl = true;

	public function initContent()
	{
		$this->display_column_left = false;
		parent::initContent();

		if (!$this->module->checkCurrency($this->context->cart))
			Tools::redirect('index.php?controller=order');

		$this->context->smarty->assign(array(
			'image' => $this->module->getPathUri().'assets/images/logops_86x49.png',
			'nbProducts' => $this->context->cart->nbProducts(),
			'cust_currency' => $this->context->cart->id_currency,
			'currencies' => $this->module->getCurrency((int) $this->context->cart->id_currency),
			'total' => $this->context->cart->getOrderTotal(true, Cart::BOTH),
			'isocode' => $this->context->language->iso_code,
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'));

		$this->setTemplate('payment_execution.tpl');
	}
}
