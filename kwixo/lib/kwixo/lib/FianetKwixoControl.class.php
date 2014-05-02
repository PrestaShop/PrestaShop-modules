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

class FianetKwixoControl extends KwixoControl
{

	public function createWallet($datecom, $datelivr)
	{
		$wallet = $this->root->appendChild(new KwixoWallet());
		$wallet->addAttribute('version', KwixoWallet::WALLET_VERSION);
		$wallet->createChild('datecom', $datecom);
		$wallet->createChild('datelivr', $datelivr);
		return $wallet;
	}

	public function createPaymentOptions($type, $rnp = null, $rnp_offered = null)
	{
		$attributes = array(
			'type' => $type,
		);
		if (!is_null($rnp))
			$attributes['comptant-rnp'] = $rnp;
		if (!is_null($rnp_offered))
			$attributes['comptant-rnp-offert'] = $rnp_offered;

		$options = $this->root->appendChild(new KwixoXMLElement('options-paiement', ' '));
		foreach ($attributes as $key => $value)
			$options->setAttribute($key, $value);
		return $options;
	}

}