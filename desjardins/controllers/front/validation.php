<?php
/**
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
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

class DesjardinsValidationModuleFrontController extends ModuleFrontController
{
	private $desjardins;

	public function initContent()
	{
		parent::initContent();
		$this->desjardins = new Desjardins();

		if (Tools::getValue('MAC'))
			$this->validation();
		else
			die('version=2'."\n".'cdr=1');
	}

	private function validation()
	{
		$params = array('TPE' => Tools::getValue('TPE'), 'date' => Tools::getValue('date'), 'montant' => Tools::getValue('montant'),
			'reference' => Tools::getValue('reference'), 'texte-libre' => Tools::getValue('texte-libre'),
			'version' => Configuration::get('DESJARDINS_VERSION'), 'code-retour' => Tools::getValue('code-retour'),
			'motifrefus' => Tools::getValue('motifrefus'), 'cvx' => Tools::getValue('cvx'), 'vld' => Tools::getValue('vld'),
			'brand' => Tools::getValue('brand'), 'status3ds' => Tools::getValue('status3ds'), 'numauto' => Tools::getValue('numauto'),
			'originecb' => Tools::getValue('originecb'), 'bincb' => Tools::getValue('bincb'), 'hpancb' => Tools::getValue('hpancb'),
			'ipclient' => Tools::getValue('ipclient'), 'originetr' => Tools::getValue('originetr'), 'veres' => Tools::getValue('veres'),
			'pares' => Tools::getValue('pares'), 'modepaiement' => Tools::getValue('modepaiement'));

		if ($this->generateHash($params) == Tools::getValue('MAC'))
		{
			$cart = new Cart($params['reference']);
			$customer = new Customer((int)$cart->id_customer);

			// Payment approved (or TEST mode is on)
			if (in_array(Tools::getValue('code-retour'), array('paiement', 'payetest')))
			{
				$order_result = array('status' => (int)Configuration::get('PS_OS_PAYMENT'), 'code' => 'VALID');
				if (Tools::getValue('code-retour') == 'paiement')
					Configuration::updateValue('DESJARDINS_CONFIGURATION_OK', true);
			}
			// Payment declined
			else
				$order_result = array('status' => (int)Configuration::get('PS_OS_ERROR'), 'code' => 'NOT VALID');

			$amount = Tools::substr(Tools::getValue('montant'), 0, -3);
			if ($this->desjardins->validateOrder((int)$cart->id, (int)$order_result['status'], (float)$amount,
				$this->desjardins->displayName, null, array(), null, false, $customer->secure_key))

			die('version=2'."\n".'cdr=0');
		}
		else
			die('version=2'."\n".'cdr=1');
	}

	private function generateHash($params)
	{
		return Tools::strtoupper(hash_hmac('sha1', $params['TPE'].'*'.$params['date'].'*'.$params['montant'].'*'.$params['reference'].'*'.
			$params['texte-libre'].'*'.$params['version'].'*'.$params['code-retour'].'*'.$params['cvx'].'*'.$params['vld'].'*'.
			$params['brand'].'*'.$params['status3ds'].'*'.$params['numauto'].'*'.$params['motifrefus'].'*'.$params['originecb'].'*'.
			$params['bincb'].'*'.$params['hpancb'].'*'.$params['ipclient'].'*'.$params['originetr'].'*'.$params['veres'].'*'.
			$params['pares'].'*', $this->desjardins->getKey()));
	}
}