<?php
/*
* Adyen Payment Module
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
*  @author Rik ter Beek <rikt@adyen.com>
*  @copyright  Copyright (c) 2013 Adyen (http://www.adyen.com)
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
class AdyenPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	
	/**
	 *
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		parent::initContent();
		
		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');
		
		$hpp_options = array ();
		if (Configuration::get('ADYEN_HPP_DISABLE') == false)
		{
			// get list of hpp options this has only the value
			$hpp_options = explode(';', Configuration::get('ADYEN_HPP_TYPES'));
		}
		
		// get all hpp options from xml
		$hpp_types_xml = $this->module->getHppTypes();
		
		// get value with name and store this in variable for front-end
		$hpp_options_key_value = array ();
		foreach ($hpp_types_xml->children() as $hpp_type)
			if (in_array($hpp_type->code, $hpp_options))
				$hpp_options_key_value[(string)$hpp_type->code] = (string)$hpp_type->name;
			
			// check environment
		if (Configuration::get('ADYEN_MODE') == 'live')
		{
			$ideal_issuers = Configuration::get('ADYEN_IDEAL_ISSUERS_LIVE');
			$ideal_issuers_all = $this->module->getBankList(true);
		} else
		{
			$ideal_issuers = Configuration::get('ADYEN_IDEAL_ISSUERS_TEST');
			$ideal_issuers_all = $this->module->getBankList(false);
		}
		
		if (count($ideal_issuers > 0))
		{
			// get list of ideal issuersthis has only the value
			$ideal_issuers_values = explode(';', $ideal_issuers);
			
			foreach ($ideal_issuers_all->children() as $ideal_issuer)
				if (in_array($ideal_issuer->bank_id, $ideal_issuers_values))
					$ideal_issuers_key_value[(string)$ideal_issuer->bank_id] = (string)$ideal_issuer->bank_name;
		}
		
		$this->context->smarty->assign(array (
				'hpp_options' => $hpp_options_key_value,
				'ideal_options' => $ideal_issuers_key_value,
				'nbProducts' => $cart->nbProducts(),
				'cust_currency' => $cart->id_currency,
				'currencies' => $this->module->getCurrency((int)$cart->id_currency),
				'total' => $cart->getOrderTotal(true, Cart::BOTH),
				'this_path' => $this->module->getPathUri(),
				'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));
		
		$this->setTemplate('payment_execution.tpl');
	}
}
