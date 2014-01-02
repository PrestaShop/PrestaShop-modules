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

if (file_exists(dirname(__FILE__).'/EbayRequest.php'))
	require_once(dirname(__FILE__).'/EbayRequest.php');

class EbayConfiguration
{
	/**
	 * Updates Ebay API Token and stores it
	 *
	 * Returns true is sucessful, false otherwise
	 *
	 * @return boolean
	 */

	public static function updateAPIToken()
	{
		$request = new EbayRequest();

		if ($token = $request->fetchToken(Configuration::get('EBAY_API_USERNAME'), Configuration::get('EBAY_API_SESSION')))
		{
			Configuration::updateValue('EBAY_API_TOKEN', $token, false, 0, 0);
			Configuration::updateValue('EBAY_TOKEN_REGENERATE', false);

			return true;
		}

		return false;
	}
}