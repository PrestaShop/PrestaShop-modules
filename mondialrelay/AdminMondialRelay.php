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

require_once(dirname(__FILE__).'/classes/MRCreateTickets.php');
require_once(dirname(__FILE__).'/mondialrelay.php');

class AdminMondialRelay extends AdminTab
{
	private $mondialrelay = NULL;

	public $post_errors = array();

	public function __construct()
	{
		$this->mondialrelay = new MondialRelay();

		$this->table = 'mr_selected';
	
		parent::__construct();
		$this->context = Context::getContext();
	}

	private function displayOrdersTable()
	{
		$order_state = new OrderState((int)(Configuration::get('MONDIAL_RELAY_ORDER_STATE')), $this->context->language->id);
		$orders = MondialRelay::getOrders(array(), MondialRelay::NO_FILTER, $this->mondialrelay->account_shop['MR_WEIGHT_COEFFICIENT']);

		// Simulate a ticket generation
		$MRCreateTicket = new MRCreateTickets(array(
			'orderIdList' => NULL,
			'totalOrder' => NULL,
			'weightList' => NULL
			),
			$this->mondialrelay
		);

		foreach($orders as &$order)
		{
			$order['display_total_price'] = Tools::displayPrice($order['total'], new Currency($order['id_currency']));
			$order['display_shipping_price'] = Tools::displayPrice($order['shipping'], new Currency($order['id_currency']));
			$order['display_date'] = Tools::displayDate($order['date'], $order['id_lang']);
			$order['weight'] = (!empty($order['mr_weight']) && $order['mr_weight'] > 0) ? $order['mr_weight'] : $order['order_weight'];
		}
		
		$controller = (_PS_VERSION_ < '1.5') ? 'AdminContact' : 'AdminStores';

		$this->context->smarty->assign(array(
				'MR_token_admin_module' => Tools::getAdminToken('AdminModules'.(int)(Tab::getIdFromClassName('AdminModules')).(int)$this->context->employee->id),
				'MR_token_admin_contact' => array(
					'controller_name' => $controller, 
					'token' => Tools::getAdminToken($controller.(int)(Tab::getIdFromClassName($controller)).(int)$this->context->employee->id)),
				'MR_token_admin_orders' => Tools::getAdminToken('AdminOrders'.(int)(Tab::getIdFromClassName('AdminOrders')).(int)$this->context->employee->id),
				'MR_order_state_name' => $order_state->name,
				'MR_orders' => $orders,
				'MR_PS_IMG_DIR_' => _PS_IMG_DIR_,
				'MR_errors_type' => $MRCreateTicket->checkPreValidation())
		);

		unset($order_state);
		echo $this->mondialrelay->fetchTemplate('/tpl/admintab/', 'generate_tickets');
	}

	public function displayhistoriqueForm()
	{
		$query = "SELECT * FROM `"._DB_PREFIX_ ."mr_history` ORDER BY `id` DESC ;";

		$this->context->smarty->assign(array(
			'MR_histories' => Db::getInstance()->executeS($query))
		);
		echo $this->mondialrelay->fetchTemplate('/tpl/admintab/', 'history');
	}

	public function displaySettings($post_action)
	{
		$curr_order_state = new OrderState((int)$this->mondialrelay->account_shop['MR_ORDER_STATE']);
		$order_state = array(
			'id_order_state' => $this->mondialrelay->account_shop['MR_ORDER_STATE'],
			'name' => $curr_order_state->name[$this->context->language->id]
		);

		$this->context->smarty->assign(array(
			'MR_token_admin_mondialrelay' => Tools::getAdminToken('AdminMondialRelay'.(int)(Tab::getIdFromClassName('AdminMondialRelay')).(int)$this->context->employee->id),
			'MR_account_set' => MondialRelay::isAccountSet(),
			'MR_order_state' =>  $order_state,
			'MR_orders_states_list' => OrderState::getOrderStates($this->context->language->id),
			'MR_form_action' => $post_action,
			'MR_error_list' => $this->post_errors
		));

		echo $this->mondialrelay->fetchTemplate('/tpl/admintab/', 'settings');
	}

	public function postProcess()
	{
		$post_action = array(
			'type' => Tools::getValue('MR_action_name'),
			'message_success' => $this->l('Action Succeed'),
			'had_errors' => false
		);

		parent::postProcess();

		if (Tools::isSubmit('submit_order_state'))
			if (($order_state = (int)Tools::getValue('id_order_state')))
			{
				$this->mondialrelay->account_shop['MR_ORDER_STATE'] = $order_state;

				if ($this->mondialrelay->updateAccountShop())
					$post_action['message_success'] = $this->l('Order State has been updated');
				else
					$this->post_errors[] = $this->l('Cannot Update the account shop');
			}

		if (count($this->post_errors))
			$post_action['had_errors'] = true;

		return $post_action;
	}

	public function display()
	{
		$post_action = count($_POST) ? $this->postProcess() : NULL;

		$this->displaySettings($post_action);
		if (MondialRelay::isAccountSet() && (int)$this->mondialrelay->account_shop['MR_ORDER_STATE'])
		{
			$this->displayOrdersTable();
			$this->displayhistoriqueForm();
		}
	}
}
