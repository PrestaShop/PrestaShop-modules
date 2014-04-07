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

if (_PS_VERSION_ < '1.5')
	include_once(dirname(__FILE__).'/controllers/admin/AdminCertissim.php');
else
	include_once(dirname(__FILE__).'/controllers/admin/AdminCertissimController.php');

class AdminCertissim extends AdminCertissimController
{

	public function __construct()
	{
		parent::__construct();

		//specific instruction for PS 1.5 and greater
		if (_PS_VERSION_ >= '1.5')
		{
			$this->tpl_folder = AdminModulesController::getController('AdminOrdersController')->tpl_folder;
			$this->override_folder = AdminModulesController::getController('AdminOrdersController')->override_folder;
		}

		$this->calibrate();

		$this->module = Module::getInstanceByName('fianetfraud');
	}

	public function calibrate()
	{
		//specific instruction for PS 1.5 and greater
		if (_PS_VERSION_ >= '1.5')
		{
			$link = new Link();

			//sets the redirection according to the action
			switch (Tools::getValue('action'))
			{
				//if checkoutScore, redirection to the admin order page
				case 'checkoutScore':
					$this->redirect_after = $link->getAdminLink('AdminOrders').'&id_order='.Tools::getValue('id_order').'&vieworder';
					break;

				//if sendOrder, redirection to the admin order page
				case 'sendOrder':
					$this->redirect_after = $link->getAdminLink('AdminOrders').'&id_order='.Tools::getValue('id_order').'&vieworder';
					break;

				//if getAllWaitingScore, redirection to the admin orders list
				case 'getAllWaitingScores':
					$this->redirect_after = $link->getAdminLink('AdminCertissim');
					break;

				//if getReevaluations, redirection to the admin orders list
				case 'getReevaluations':
					$this->redirect_after = $link->getAdminLink('AdminCertissim');
					break;

				//if unknown action
				default:
					break;
			}
		}

		$this->_select .= ',
			IF(
				(SELECT cs.label 
				FROM `'._DB_PREFIX_.'certissim_order` c 
					LEFT JOIN `'._DB_PREFIX_.'certissim_state` cs ON c.id_certissim_state = cs.id_certissim_state 
						WHERE c.id_order = a.id_order) = "scored",
				(SELECT c2.score FROM `'._DB_PREFIX_.'certissim_order` c2 WHERE c2.id_order = a.id_order LIMIT 1),
				(
					IF(
						(SELECT count(c3.id_order) 
						FROM `'._DB_PREFIX_.'certissim_order` c3 
							WHERE c3.id_order=a.id_order) > 0,
						(SELECT cs2.label 
						FROM `'._DB_PREFIX_.'certissim_order` c3 
							LEFT JOIN `'._DB_PREFIX_.'certissim_state` cs2 ON c3.id_certissim_state = cs2.id_certissim_state 
								WHERE c3.id_order = a.id_order),
						("not concerned")
					)
				)
			)
			as score';

		$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'certissim_order` cert ON (cert.`id_order` = a.`id_order`)';

		if (_PS_VERSION_ >= '1.5')
		{
			$column_description = array(
				'title' => $this->l('Certissim Eval'),
				'width' => 50,
				'search' => true,
				'icon' => array(
					'0' => array('src' => '../../modules/fianetfraud/img/0.gif', 'alt' => 'Risque détecté'),
					'-1' => array('src' => '../../modules/fianetfraud/img/-1.gif', 'alt' => 'Pas de risque détecté'),
					'100' => array('src' => '../../modules/fianetfraud/img/100.gif', 'alt' => 'Certifiée sans risque'),
					'error' => array('src' => '../../modules/fianetfraud/img/error.gif', 'alt' => 'Erreur'),
					'sent' => array('src' => '../../modules/fianetfraud/img/sent.gif', 'alt' => 'Calcul du risque en cours'),
					'ready to send' => array('src' => '../../modules/fianetfraud/img/ready-to-send.gif', 'alt' => 'En attente du paiement'),
					'not concerned' => array('src' => '../../modules/fianetfraud/img/not-concerned.gif', 'alt' => 'Non concernée')
				),
			);
			$this->fields_list['score'] = $column_description;
		}
		else
		{
			$column_description = array(
				'title' => $this->l('Certissim Eval'),
				'width' => 50,
				'search' => true,
				'icon' => array(
					'0' => '../../modules/fianetfraud/img/0.gif',
					'-1' => '../../modules/fianetfraud/img/-1.gif',
					'100' => '../../modules/fianetfraud/img/100.gif',
					'error' => '../../modules/fianetfraud/img/error.gif',
					'sent' => '../../modules/fianetfraud/img/sent.gif',
					'ready to send' => '../../modules/fianetfraud/img/ready-to-send.gif',
					'default' => '../../modules/fianetfraud/img/not-concerned.gif'
				));
			$this->fieldsDisplay['score'] = $column_description;
		}
	}

	public function postProcess()
	{
		parent::postProcess();

		switch (Tools::getValue('action'))
		{
			//action sendOrder: sends an order to Certissim
			case 'sendOrder':
				//if no order specified: end of process
				if (!Tools::isSubmit('id_order'))
				{
					CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, 'pas de commande indiquée. Fin checkout.');
					break;
				}
				if (!is_int((int)Tools::getValue('id_order')))
				{
					CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, 'Référence incorrecte : '.Tools::getValue('id_order').'. Fin checkout.');
					break;
				}

				//sends the order to Certissim and update the Certissim order table if the order is ready to be sent, do nothing otherwise
				$this->sendAndUpdateOrder(Tools::getValue('id_order'));

				//if PS 1.4 or lower, redirect definition
				if (_PS_VERSION_ < '1.5')
				{
					//redirects the user to the admin order page
					$admin_dir = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.Tools::substr(PS_ADMIN_DIR, strrpos(PS_ADMIN_DIR, '/') + 1);
					$url = $admin_dir.'/index.php?tab=AdminCertissim&id_order='.Tools::getValue('id_order').'
						&vieworder&token='.Tools::getAdminTokenLite('AdminCertissim');
					Tools::redirect($url, '');
				}
				break;

			//action checkoutScore: checks the score of the order specified
			case 'checkoutScore':
				//if no order specified: end of process
				if (!Tools::isSubmit('id_order'))
				{
					CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, 'pas de commande indiquée. Fin checkout.');
					break;
				}
				if (!is_int((int)Tools::getValue('id_order')))
				{
					CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, 'Référence incorrecte : '.Tools::getValue('id_order').'. Fin checkout.');
					break;
				}

				CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, '----- Checkout du score order '.Tools::getValue('id_order'));
				//updating the order
				$this->module->updateOrder(Tools::getValue('id_order'));
				CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, '----- Fin checkout ----');

				//if PS 1.4 or lower, redirect definition
				if (_PS_VERSION_ < '1.5')
				{
					//redirects the user to the admin order page
					$admin_dir = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.Tools::substr(_PS_ADMIN_DIR_, strrpos(_PS_ADMIN_DIR_, '/') + 1);
					$url = $admin_dir.'/index.php?tab=AdminCertissim&id_order='.Tools::getValue('id_order').'
						&vieworder&token='.Tools::getAdminTokenLite('AdminCertissim');
					CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, 'Redirection vers '.$url);
					Tools::redirect($url, '');
				}
				break;

			//action getAllWaitingScores: calls Certissim to get the score of each order that is in state 'sent' or 'error'
			case 'getAllWaitingScores':

				fianetfraud::getEvaluations();

				//if PS 1.4 or lower, redirect definition
				if (_PS_VERSION_ < '1.5')
				{
					//redirects the user to the admin order page
					$admin_dir = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.Tools::substr(_PS_ADMIN_DIR_, strrpos(_PS_ADMIN_DIR_, '/') + 1);
					$url = $admin_dir.'/index.php?tab=AdminCertissim&token='.Tools::getAdminTokenLite('AdminCertissim');
					CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, 'Redirection vers '.$url);
					Tools::redirect($url, '');
				}
				break;
			//action getReevaluations: calls Certissim to get the list of orders that have been reevaluated, and updates them
			case 'getReevaluations':
				CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, '----- Get reevals ----'); //finlog
				//if PS 1.4 or lower: gets reevaluations for one shop (multishop does not exist)
				if (_PS_VERSION_ < '1.5' || !Shop::isFeatureActive())
					fianetfraud::getReevaluations();
				//if PS 1.5 or greater: gets the reevaluations for each shop
				else
					foreach (Shop::getShops() as $shop)
						fianetfraud::getReevaluations($shop['id_shop']);

				CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, '----- Fin reevals ----'); //finlog
				//if PS 1.4 or lower, redirect definition
				if (_PS_VERSION_ < '1.5')
				{
					//redirects the user to the admin order page
					$admin_dir = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.Tools::substr(_PS_ADMIN_DIR_, strrpos(_PS_ADMIN_DIR_, '/') + 1);
					$url = $admin_dir.'/index.php?tab=AdminCertissim&token='.Tools::getAdminTokenLite('AdminCertissim');
					CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, 'Redirection vers '.$url);
					Tools::redirect($url, '');
				}
				break;

			default:
				break;
		}
	}

	public function sendAndUpdateOrder($id_order)
	{
		$sent = $this->module->buildAndSend($id_order);
		if ($sent)
			fianetfraud::switchOrderToState($id_order, 'sent');
		else
			CertissimLogger::insertLog(__METHOD__.' : '.__LINE__, 'Envoi de la commande '.$id_order.' vers Certissim a échoué.');
	}

}