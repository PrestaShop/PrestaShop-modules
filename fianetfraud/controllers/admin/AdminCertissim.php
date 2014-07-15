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

include_once _PS_ADMIN_DIR_.'/tabs/AdminOrders.php';

class AdminCertissimController extends AdminOrders
{

	/**
	 * displays available actions in the top of the order list
	 */
	public function displayTop()
	{
		$link_eval = 'index.php?tab=AdminCertissim&action=getAllWaitingScores&token='.Tools::getAdminTokenLite('AdminCertissim');
		$img_eval = _PS_BASE_URL_.__PS_BASE_URI__.'modules/fianetfraud/img/certissim-wait.png';
		$link_reeval = 'index.php?tab=AdminCertissim&action=getReevaluations&token='.Tools::getAdminTokenLite('AdminCertissim');
		$img_reeval = _PS_BASE_URL_.__PS_BASE_URI__.'modules/fianetfraud/img/certissim-reeval.png';
		$header = '<fieldset>
			<div id=\'header_certissim\'><div class=\'certissim_control\'>
			<a href="'.$link_eval.'"><img src="'.$img_eval.'"/>'.$this->l('Get all waiting evaluations').'</a></div>
				<div class=\'certissim_control\'><a href="'.$link_reeval.'"><img src="'.$img_reeval.'"/>'.$this->l('Get all reevaluations').'</a></div>
					</div></fieldset>';

		echo $header;
	}

	public function display()
	{
		if (Tools::isSubmit('action') && Tools::getValue('action') == 'viewLog')
		{
			//loads the log content
			$log_content = CertissimLogger::getLogContent();
			$log_txt = CertissimTools::convertEncoding($log_content, 'UTF-8');
			$url_back = 'index.php?tab=AdminModules&configure=fianetfraud&tab_module=payment_security&module_name=fianetfraud
				&token='.Tools::getAdminTokenLite('AdminModules');
			echo '<center><a style=\'padding: 3px; margin: 6px; border: 1px solid black\' 
				href="'.$url_back.'">'.$this->l('Back to configuration page').'</a></center>
				<textarea style=\'margin: 6px; cols=\'160\' rows=\'50\' readOnly\'>'.$log_txt.'</textarea><div class=\'clear\'></div>
					<center><a style=\'padding: 3px; margin: 6px; border: 1px solid black\' href="'.$url_back.'">
						'.$this->l('Back to configuration page').'</a></center>';
		}
		else
			echo parent::display();
	}

}