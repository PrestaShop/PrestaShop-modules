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
	include_once 'controllers/admin/AdminSceau.php';
else
	include_once 'controllers/admin/AdminSceauController.php';

/**
 * Description of AdminSceau
 *
 * @author ycyrille
 */
class AdminSceau extends AdminSceauController
{

	public function __construct()
	{
		parent::__construct();

		//specific instruction for PS 1.5 and greater
		if (_PS_VERSION_ >= '1.5')
		{
			$this->tpl_folder = AdminModulesController::getController('AdminOrdersController')->tpl_folder;
			$this->override_folder = AdminModulesController::getController('AdminOrdersController')->override_folder;
			$link = new Link();

			//sets the redirection according to the action
			switch (Tools::getValue('action'))
			{
				//if sendOrder, redirection to the admin order page
				case 'ResendOrder':
					$this->redirect_after = $link->getAdminLink('AdminSceau')."&id_order=".Tools::getValue('id_order').'&vieworder';
					break;

				//if unknown action
				default:
					break;
			}
		}

		//build sql query which added to sql query of AdminOrders class
		$this->_select .= ', fs.`label` as `fs_label`';
		$this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'fianetsceau_order` fo ON a.`id_order` = fo.`id_order`';
		$this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'fianetsceau_state` fs ON fo.`id_fianetsceau_state` = fs.`id_fianetsceau_state`';

		//icons management
		if (_PS_VERSION_ >= '1.5')
			$icons = array(
				'sent' => array('src' => "../../modules/fianetsceau/img/sent.gif", 'alt' => 'Commande envoyée'),
				'waiting payment' => array('src' => "../../modules/fianetsceau/img/waiting.gif", 'alt' => 'Commande en attente de paiement'),
				'error' => array('src' => "../../modules/fianetsceau/img/not_concerned.png", 'alt' => 'Commande en erreur'),
				'default' => "../../modules/fianetsceau/img/error.gif");
		else
			$icons = array(
				'sent' => '../../modules/fianetsceau/img/sent.gif',
				'waiting payment' => '../../modules/fianetsceau/img/waiting.gif',
				'error' => '../../modules/fianetsceau/img/not_concerned.png',
				'default' => "../../modules/fianetsceau/img/error.gif");

		//personalize new column added in new order tab
		$column_definition = array(
			'title' => $this->l('Sceau state'), //column name
			'width' => 50,
			'icon' => $icons);

		if (_PS_VERSION_ >= '1.5')
			$this->fields_list['fs_label'] = $column_definition;
		else
			$this->fieldsDisplay['fs_label'] = $column_definition;

		$this->module = Module::getInstanceByName('fianetsceau');
	}

	public function postProcess()
	{

		switch (Tools::getValue('action'))
		{
			//resends all orders that would have been sent
			case 'ResendOrders':
				//gets the list of all orders to send
				$orders = $this->getFianetSceauOrdersToResend();
				//sends orders
				foreach ($orders as $order)
					$this->module->sendXML($order['id_order']);

				break;


			//resends an order that would have been sent
			case 'ResendOrder':
				//sends the order given in param
				$this->module->sendXML(Tools::getValue('id_order'));
				if (_PS_VERSION_ < '1.5')
				{
					$admin_dir_tokens = explode('\\', _PS_ADMIN_DIR_);
					$admin_dir = $admin_dir_tokens[count($admin_dir_tokens) - 1];
					$url = $admin_dir.'/index.php?tab=AdminSceau&id_order='.Tools::getValue('id_order').'&vieworder&token='.Tools::getAdminTokenLite('AdminSceau');
					Tools::redirect($url);
				}
				break;

			default:
				break;
		}

		parent::postProcess();
	}

	/**
	 * get all orders with status 3 : error
	 * 
	 * @return Array 
	 */
	public function getFianetSceauOrdersToResend()
	{

		$sql = "SELECT `id_order` FROM `"._DB_PREFIX_.FianetSceau::SCEAU_ORDER_TABLE_NAME."` WHERE `id_fianetsceau_state` = '3'";
		$query_result = Db::getInstance()->executeS($sql);

		return($query_result);
	}

}