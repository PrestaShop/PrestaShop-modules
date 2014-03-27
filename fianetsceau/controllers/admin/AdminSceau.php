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

include_once _PS_ADMIN_DIR_.'/tabs/AdminOrders.php';

/**
 * Description of AdminSceau
 *
 * @author ycyrille
 */
class AdminSceauController extends AdminOrders
{

	/**
	 * displays available actions in the top of the order list
	 */
	public function displayTop()
	{
		$header = "<fieldset>";
		$header .= 
			"<div id='header_sceau'>
				<div class='sceau_control'>
					<a href='index.php?tab=AdminSceau&action=ResendOrders&token=".Tools::getAdminTokenLite('AdminSceau')."'>
						<img src='"._PS_BASE_URL_.__PS_BASE_URI__."modules/fianetsceau/img/sceauresend14.png'/>".$this->l('Resend orders')."
					</a>
				</div>
			</div>";
		$header .= '</fieldset>';

		echo $header;
	}

	/**
	 * Get FIA-NET log file and show it
	 */
	public function display()
	{
		return parent::display();
	}

}