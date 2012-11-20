<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(dirname(__FILE__).'/../../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../../init.php');

if (_PS_VERSION_ < '1.5')
	require_once(_PS_ROOT_DIR_.'/controllers/OrderConfirmationController.php');

class PayPalIntegralEvolutionSubmit extends OrderConfirmationControllerCore
{
	public $context;

	public function __construct()
	{
		/** Backward compatibility */
		require(_PS_MODULE_DIR_.'/paypal/backward_compatibility/backward.php');
		
		$this->context = Context::getContext();

		parent::__construct();
	}

	/*
	 * Display PayPal order confirmation page
	 */
	public function displayContent()
	{
		$order = PayPalOrder::getOrderById((int)Tools::getValue('id_order'));

		$this->context->smarty->assign(array(
			'order' => $order,
			'price' => Tools::displayPrice($order['total_paid'], $this->context->currency)
		));

		echo $this->context->smarty->fetch(_PS_MODULE_DIR_.'/paypal/views/templates/front/order-confirmation.tpl');
	}
}

if (Tools::getValue('id_module') && Tools::getValue('key') && Tools::getValue('id_cart') && Tools::getValue('id_order'))
{
	if (_PS_VERSION_ < '1.5')
	{
		$integral_evolution_submit = new PayPalIntegralEvolutionSubmit();
		$integral_evolution_submit->run();
	}
}
elseif ($id_cart = Tools::getValue('id_cart'))
{
	// Redirection
	$array = array(
		'id_cart' => (int)$id_cart,
		'id_module' => (int)Module::getInstanceByName('paypal')->id,
		'id_order' => (int)Order::getOrderByCartId((int)$id_cart),
		'key' => Context::getContext()->customer->secure_key
	);

	if (_PS_VERSION_ < '1.5')
		Tools::redirectLink(__PS_BASE_URI__ . '/modules/paypal/integral_evolution/submit.php?'.http_build_query($array, '', '&'));
	else
		Tools::redirect(Context::getContext()->link->getModuleLink('paypal', 'submit', $array));
}
else
	Tools::redirectLink(__PS_BASE_URI__);
exit;