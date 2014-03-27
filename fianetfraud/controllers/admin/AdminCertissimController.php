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
require_once _PS_MODULE_DIR_.'fianetfraud/lib/includes/includes.inc.php';
require_once _PS_MODULE_DIR_.'fianetfraud/fianetfraud.php';

class AdminCertissimController extends AdminOrdersController
{

	public function initToolbar()
	{
		parent::initToolbar();
		if (is_null($this->display))
		{
			$this->toolbar_btn['cert1'] = array(
				'href' => $this->context->link->getAdminLink('AdminCertissim')."&action=getAllWaitingScores",
				'desc' => $this->l('Get waiting evalulations'),
			);
			$this->toolbar_btn['cert2'] = array(
				'href' => $this->context->link->getAdminLink('AdminCertissim')."&action=getReevaluations",
				'desc' => $this->l('Get reevalulations'),
			);
		}
	}

	public function initContent()
	{
		if (Tools::isSubmit('action') && Tools::getValue('action') == 'viewLog')
		{
			//loads the log content
			$log_content = CertissimLogger::getLogContent();
			$log_txt = htmlspecialchars($log_content, ENT_QUOTES, 'UTF-8');
			$url_back = $this->context->link->getAdminLink('AdminModules').'&configure=fianetfraud';
			$html = "<p><a href='".$url_back."'>".$this->l('Back to configuration page')."</a></p><textarea cols='180' rows='35' Readonly>$log_txt</textarea><p><a href='".$url_back."'>".$this->l('Back to configuration page')."</a></p>";
			$this->context->smarty->assign('content', $html);
		}
		else
			parent::initContent();
	}

}