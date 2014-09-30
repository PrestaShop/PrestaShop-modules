<?php
/**
 * 2007-2013 PrestaShop
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
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once  dirname(__FILE__) . '/../../pagseguro.php';

class PagSeguroPaymentOrderPrestashop
{
    private $paymentUrlPS14 = 'modules/pagseguro/standard/front/validation.php';
    private $paymentUrl = 'index.php?fc=module&module=pagseguro&controller=validation';
    
    private $context;
    
    public function __construct()
    {
        $this->context = Context::getContext();
    }
    
    private function returnUrlPaymentForVersionModule()
    {
        return version_compare(_PS_VERSION_, '1.5.0.3', '<=') ? $this->paymentUrlPS14 : $this->paymentUrl;
    }
    
    private function setCurrencyVariable($id_currency)
    {
        $totalOrder = $this->context->cart->getOrderTotal(true, Cart::BOTH);
        $current_currency = new Currency($this->context->cart->id_currency);
        $new_currency = new Currency($id_currency);
        $this->context->smarty->assign(
            array(
                'total_real' => Util::convertPriceFull($totalOrder, $current_currency, $new_currency),
                'currency_real' => $id_currency)
        );
    }
    
    public function setVariablesPaymentExecutionView()
    {
        
        $id_currency = PagSeguro::returnIdCurrency();
        
        if ($this->context->cart->id_currency != $id_currency && ! is_null($id_currency)) {
            $this->setCurrencyVariable($id_currency);
        }

        if ( version_compare(_PS_VERSION_, '1.5.0.2', '>=') && version_compare(_PS_VERSION_, '1.6.0.1', '<') ){
             $center_column = '757px';
        } if ( version_compare(_PS_VERSION_, '1.6.0.1', '>=') ) {
             $center_column = '100%';
        } else {
            $center_column = '535px';
        }
        
        $this->context->smarty->assign(
            array(
                'version' => _PS_VERSION_,
                'width_center_column' => $center_column,
                'image_payment' => __PS_BASE_URI__ . 'modules/pagseguro/assets/images/logops_86x49.png',
                'nbProducts' => $this->context->cart->nbProducts(),
                'current_currency_id' => $this->context->currency->id,
                'current_currency_name' => $this->context->currency->name,
                'cust_currency' => $this->context->cart->id_currency,
                'total' => $this->context->cart->getOrderTotal(true, Cart::BOTH),
                'isocode' => $this->context->language->iso_code,
                'this_path' => __PS_BASE_URI__,
                'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/pagseguro/',
                'action_url' => _PS_BASE_URL_ . __PS_BASE_URI__ .$this->returnUrlPaymentForVersionModule(),
                'checkout' => Configuration::get('PAGSEGURO_CHECKOUT'))
        );
    }
}
