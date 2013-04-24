<?php

/*
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$configPath = '../../../config/config.inc.php';
if (file_exists($configPath))
{
	include('../../../config/config.inc.php');
	if (!Tools::getValue('token') || Tools::getValue('token') != Configuration::get('EBAY_SECURITY_TOKEN'))
		die('ERROR :X');

	if (file_exists(dirname(__FILE__).'/../eBayRequest.php'))
	{
		include(dirname(__FILE__).'/../eBayRequest.php');

		$ebay = new eBayRequest();
		$ebay->session = Configuration::get('EBAY_API_SESSION');
		$ebay->username = Configuration::get('EBAY_API_USERNAME');
		$ebay->fetchToken();
		if (!empty($ebay->token))
		{
			if(version_compare(_PS_VERSION_,'1.5','>'))
				Configuration::updateValue('EBAY_API_TOKEN', $ebay->token, false, 0, 0);
			else
				Configuration::updateValue('EBAY_API_TOKEN', $ebay->token);
			echo 'OK';
		}
		else
			echo 'KO';
	}
	else
		echo 'ERROR02';
}
else
	echo 'ERROR01';

