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
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class SeurCashOnDeliveryValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */
	public function postProcess()
	{
		if ($this->context->cart->id_customer == 0 || $this->context->cart->id_address_delivery == 0 || $this->context->cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');
		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'seurcashondelivery')
			{
				$authorized = true;
				break;
			}
		if (!$authorized)
			die(Tools::displayError('This payment method is not available.'));

		$customer = new Customer((int)$this->context->cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');

		if (Tools::getValue('confirm'))
		{
			$customer = new Customer((int)$this->context->cart->id_customer);
            $coste = (float)(abs($this->context->cart->getOrderTotal(true, Cart::BOTH)));
            $cargo = number_format($this->module->getCargo($this->context->cart, false) , 2, '.', '');
            $vales = (float)(abs($this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS)));
            $total = $coste - $vales + $cargo;

			if(version_compare(_PS_VERSION_, "1.5", "<"))
            {
                $this->module->validateOrderFORWEBS_v4((int)$this->context->cart->id, Configuration::get('REEMBOLSO_OS_CARGO'), $total, $this->module->displayName, null, array(), null, false, $customer->secure_key);
            }
            else
            {
                $this->module->validateOrderFORWEBS_v5((int)$this->context->cart->id, Configuration::get('REEMBOLSO_OS_CARGO'), $total, $this->module->displayName, null, array(), null, false, $customer->secure_key);
            }

			Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?key='.urlencode($customer->secure_key).'&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->module->id.'&id_order='.(int)$this->module->currentOrder);
		}
	}

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		parent::initContent();

                $coste = (float)(abs($this->context->cart->getOrderTotal(true, Cart::BOTH)));
                $cargo = number_format($this->module->getCargo($this->context->cart, false) , 2, '.', '');
                $vales = (float)(abs($this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS)));

                $total = $coste - $vales + $cargo;

		$this->context->smarty->assign(array(
			'coste' => $coste,
                        'cargo' => $cargo,
                        'total' => $total,
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));


		if(version_compare(_PS_VERSION_, "1.5", "<")){
            // $this->setTemplate('views/templates/hooks/validation.tpl');
			$this->setTemplate('views/templates/hook/validation.tpl');
		}
        else
            $this->setTemplate('validation.tpl');
	}
}
