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

include_once(dirname(__FILE__).'/../../../config/config.inc.php');

class Call
{
	private $_name;
	private $_siteId;
	private $_key;
	private $_transportFile;
	private $_file = array(
		'transport' => array(
			'test' => 'https://staging.merchantware.net/transportweb4/transportService.asmx?WSDL',
			'prod' => 'https://transport.merchantware.net/v4/transportService.asmx?WSDL'),
		'report' => array(
			'test' => 'https://staging.merchantware.net/Merchantware/ws/TransactionHistory/v4/Reporting.asmx?WSDL',
			'prod' => 'https://ps1.merchantware.net/Merchantware/ws/TransactionHistory/v4/Reporting.asmx?WSDL'),
		'void' => array(
			'test' => 'https://staging.merchantware.net/Merchantware/ws/RetailTransaction/v4/Credit.asmx?WSDL',
			'prod' => 'https://ps1.merchantware.net/Merchantware/ws/RetailTransaction/v4/Credit.asmx?WSDL'),
		'refund' => array(
			'test' => 'https://staging.merchantware.net/Merchantware/ws/RetailTransaction/v4/Credit.asmx?WSDL',
			'prod' => 'https://ps1.merchantware.net/Merchantware/ws/RetailTransaction/v4/Credit.asmx?WSDL')
	);

	public function __construct()
	{
		$this->_name = Configuration::get('MERCHANTWARE_MERCHANT_NAME');
		$this->_siteId = Configuration::get('MERCHANTWARE_SITE_ID');
		$this->_key = Configuration::get('MERCHANTWARE_KEY');
	}

	public function createTransaction($params)
	{
		$client = new SoapClient($this->_file['transport'][Configuration::get('MERCHANT_WARE_MODE')], array('features' => SOAP_SINGLE_ELEMENT_ARRAYS));
		return $client->__soapCall(
			'CreateTransaction', array(
				'CreateTransaction' => array(
					'merchantName' => $this->_name,
					'merchantSiteId' => $this->_siteId,
					'merchantKey' => $this->_key,
					'request' => array(
						'TransactionType' => 'SALE',
            'Amount' => $params['amount'],
            'ClerkId' => $params['customer_id'],
            'OrderNumber' => $params['cart_id'],
            'Dba' => substr($params['store_name'], 0, 50),
            'SoftwareName' => 'prestashopmerchantwaremodule',
            'SoftwareVersion' => 1.0,
            'AddressLine1' => $params['customer_address'],
            'Zip' => $params['customer_zipcode'],
						'Cardholder' => $params['customer_lastname'],
            'LogoLocation' => $params['logo'],
            'RedirectLocation' => $params['validation_link'],
            'TransactionId' => $params['cart_id'],
            'ForceDuplicate' => true,
            'TaxAmount' => 0.00,
						'EntryMode' => 'Undefined',
            'DisplayColors' => array(
							'ScreenBackgroundColor' => $params['layout']['ScreenBackgroundColor'],
							'ContainerBackgroundColor' => $params['layout']['ContainerBackgroundColor'],
							'ContainerFontColor' => $params['layout']['ContainerFontColor'],
							'ContainerHelpFontColor' => $params['layout']['ContainerHelpFontColor'],
							'ContainerBorderColor' => $params['layout']['ContainerBorderColor'],
							'LogoBackgroundColor' => $params['layout']['LogoBackgroundColor'],
							'LogoBorderColor' => $params['layout']['LogoBorderColor'],
							'TooltipBackgroundColor' => $params['layout']['TooltipBackgroundColor'],
							'TooltipBorderColor' => $params['layout']['TooltipBorderColor'],
							'TooltipFontColor' => $params['layout']['TooltipFontColor'],
							'TextboxBackgroundColor' => $params['layout']['TextboxBackgroundColor'],
							'TextboxBorderColor' => $params['layout']['TextboxBorderColor'],
							'TextboxFocusBackgroundColor' => $params['layout']['TextboxFocusBackgroundColor'],
							'TextboxFocusBorderColor' => $params['layout']['TextboxFocusBorderColor'],
							'TextboxFontColor' => $params['layout']['TextboxFontColor']),
            'DisplayOptions' => array(
							'AlignLeft' => false,
							'NoCardNumberMask' => false,
							'HideDetails' => false,
							'HideDowngradeMessage' => false,
							'HideMessage' => false,
							'HideTooltips' => false,
							'UseNativeButtons' => false)
					))));
	}

	public function getTransaction($token)
	{
		$client = new SoapClient($this->_file['report'][Configuration::get('MERCHANT_WARE_MODE')]);
		return $client->__soapCall(
			'TransactionsByReference', array(
				'TransactionsByReference' => array(
					'merchantName' => $this->_name,
					'merchantSiteId' => $this->_siteId,
					'merchantKey' => $this->_key,
					'token' => Tools::safeOutput($token)
				)
			)
		);
	}

	public function voidTransaction($token)
	{
		$client = new SoapClient($this->_file['void'][Configuration::get('MERCHANT_WARE_MODE')]);
		return $client->__soapCall('Void',
			array(
				'Void' => array(
					'merchantName' => $this->_name,
					'merchantSiteId' => $this->_siteId,
					'merchantKey' => $this->_key,
					'token' => Tools::safeOutput($token)
				)
			)
		);
	}

	public function refundTransaction($token, $amount)
	{
		$client = new SoapClient($this->_file['refund'][Configuration::get('MERCHANT_WARE_MODE')]);
		return $client->__soapCall('Refund',
			array(
				'Refund' => array(
					'merchantName' => $this->_name,
					'merchantSiteId' => $this->_siteId,
					'merchantKey' => $this->_key,
					'token' => Tools::safeOutput($token),
					'overrideAmount' => (float)$amount
				)
			));
	}
}
