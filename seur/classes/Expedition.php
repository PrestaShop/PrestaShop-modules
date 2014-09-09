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
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Expedition
{
	public static function getExpeditions($expedition_data = null)
	{
		$response = false;

		try
		{
			$sc_options = array(
				'connection_timeout' => 30
			);

			$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_E'), $sc_options);

			if (!isset($expedition_data['start_date']) || !isset($expedition_data['end_date']))
				$new_date = strtotime('-15 days', strtotime(date('Y-m-d')));

			$data = array(
				'in0' => 'S',
				'in1' => (isset($expedition_data['expedition_number']) ? $expedition_data['expedition_number'] : ''),
				'in2' => '',
				'in3' => (isset($expedition_data['reference_number']) ? $expedition_data['reference_number'] : ''),
				'in4' => SeurLib::getMerchantField('ccc').'-'.SeurLib::getMerchantField('franchise'),
				'in5' => (!isset($expedition_data['start_date']) ? date('d-m-Y', $new_date) : $expedition_data['start_date']),
				'in6' => (!isset($expedition_data['end_date']) ? date('d-m-Y') : $expedition_data['end_date']),
				'in7' => (isset($expedition_data['order_state']) ? $expedition_data['order_state'] : ''),
				'in8' => '',
				'in9' => '',
				'in10' => '',
				'in11' => '0',
				'in12' => Configuration::get('SEUR_WS_USERNAME'),
				'in13' => Configuration::get('SEUR_WS_PASSWORD'),
				'in14' => 'N'
			);
			
			$response = $soap_client->consultaListadoExpedicionesStr($data);

			if (empty($response->out))
				return false;
		}
		catch (PrestaShopException $e)
		{
			$e->displayMessage();
		}

		return $response;
	}
}