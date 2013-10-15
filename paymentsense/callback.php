<?php
/*
* Prestashop PaymentSense Re-Directed Payment Module
* Copyright (C) 2013 PaymentSense.
*
* This program is free software: you can redistribute it and/or modify it under the terms
* of the AFL Academic Free License as published by the Free Software Foundation, either
* version 3 of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
* without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
* See the AFL Academic Free License for more details. You should have received a copy of the
* AFL Academic Free License along with this program. If not, see <http://opensource.org/licenses/AFL-3.0/>.
*
*  @author PaymentSense <devsupport@paymentsense.com>
*  @copyright  2013 PaymentSense
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
This file is part of the Prestashop PaymentSense Re-Directed Payment Module
See paymentsense.php for Licensing and support info.
File Last Modified: 12/03/2013 - By Shaun Ponting - Opal Creations
File Last Modified: 16/07/2013 - By Lewis Ayres-Stephens - PaymentSense - Callback Issue Fixed
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/paymentsense.php');

$paymentsense = new PaymentSense();
if ($paymentsense->active)
{
	if (trim(Configuration::get('PAYMENTSENSE_PSK') == '') || !Configuration::get('PAYMENTSENSE_PSK') || trim(Configuration::get('PAYMENTSENSE_GATEWAYPASS') == '') || !Configuration::get('PAYMENTSENSE_GATEWAYPASS'))
		die('PS GATEWAY NOT CONFIGURED. NO CALLBACK ALLOWED');

	if ((int)Configuration::get('PS_REWRITING_SETTINGS') == 1)
		$rewrited_url = __PS_BASE_URI__;

		function addStringToStringList($szExistingStringList, $szStringToAdd)
		{
			$szReturnString = '';
			$szCommaString = '';

			if (Tools::strlen($szStringToAdd) == 0)
				$szReturnString = $szExistingStringList;
			else
			{
				if (Tools::strlen($szExistingStringList) != 0)
					$szCommaString = ', ';
				$szReturnString = $szExistingStringList.$szCommaString.$szStringToAdd;
			}

			return ($szReturnString);
		}

		$szHashDigest = '';
		$szOutputMessage = '';
		$boErrorOccurred = false;
		$nStatusCode = 30;
		$szMessage = '';
		$nPreviousStatusCode = 0;
		$szPreviousMessage = '';
		$szCrossReference = '';
		$szCardType = '';
		$szCardClass = '';
		$szCardIssuer = '';
		$szCardIssuerCountryCode = '';
		$szAddressNumericCheckResult = '';
		$szPostCodeCheckResult = '';
		$szCV2CheckResult = '';
		$szThreeDSecureAuthenticationCheckResult = '';
		$nAmount = 0;
		$nCurrencyCode = 0;
		$szOrderID = '';
		$szTransactionType = '';
		$szTransactionDateTime = '';
		$szOrderDescription = '';
		$szCustomerName = '';
		$szAddress1 = '';
		$szAddress2 = '';
		$szAddress3 = '';
		$szAddress4 = '';
		$szCity = '';
		$szState = '';
		$szPostCode = '';
		$nCountryCode = '';
		$szEmailAddress = '';
		$szPhoneNumber = '';

		try
		{
			/* Assign Hash digest */
			if (Tools::isSubmit('HashDigest'))
				$szHashDigest = Tools::getValue('HashDigest');

			/* Transaction status code */
			if (!Tools::isSubmit('StatusCode'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [StatusCode] not received');
				$boErrorOccurred = true;
			}
			else
			{
				if (Tools::getValue('StatusCode') == '')
					$nStatusCode = null;
				else
					$nStatusCode = (int)Tools::getValue('StatusCode');
			}

			/* transaction message */
			if (!Tools::isSubmit('Message'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [Message] not received');
				$boErrorOccurred = true;
			}
			else
				$szMessage = Tools::getValue('Message');

			/* status code of original transaction if this transaction was deemed a duplicate */
			if (!Tools::isSubmit('PreviousStatusCode'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [PreviousStatusCode] not received');
				$boErrorOccurred = true;
			}
			else
			{
				if (Tools::getValue('PreviousStatusCode') == '')
					$nPreviousStatusCode = null;
				else
					$nPreviousStatusCode = (int)(Tools::getValue('PreviousStatusCode'));
			}

			/* status code of original transaction if this transaction was deemed a duplicate */
			if (!Tools::isSubmit('PreviousMessage'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [PreviousMessage] not received');
				$boErrorOccurred = true;
			}
			else
				$szPreviousMessage = Tools::getValue('PreviousMessage');

			/* cross reference of transaction */
			if (!Tools::isSubmit('CrossReference'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [CrossReference] not received');
				$boErrorOccurred = true;
			}
			else
				$szCrossReference = Tools::getValue('CrossReference');

			/* card type */
			if (!Tools::isSubmit('CardType'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [CardType] not received');
				$boErrorOccurred = true;
			}
			else
				$szCardType = Tools::getValue('CardType');

			/* card class */
			if (!Tools::isSubmit('CardClass'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [CardClass] not received');
				$boErrorOccurred = true;
			}
			else
				$szCardClass = Tools::getValue('CardClass');

				/* card issuer */
			if (!Tools::isSubmit('CardIssuer'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [CardIssuer] not received');
				$boErrorOccurred = true;
			}
			else
				$szCardIssuer = Tools::getValue('CardIssuer');

			/* card issuer */
			if (!Tools::isSubmit('CardIssuerCountryCode'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [CardIssuerCountryCode] not received');
				$boErrorOccurred = true;
			}
			else
				$szCardIssuerCountryCode = Tools::getValue('CardIssuerCountryCode');

			/* address numeric check */
			if (!Tools::isSubmit('AddressNumericCheckResult'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [AddressNumericCheckResult] not received');
				$boErrorOccurred = true;
			}
			else
				$szAddressNumericCheckResult = Tools::getValue('AddressNumericCheckResult');

			/* post code check */
			if (!Tools::isSubmit('PostCodeCheckResult'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [PostCodeCheckResult] not received');
				$boErrorOccurred = true;
			}
			else
				$szPostCodeCheckResult = Tools::getValue('PostCodeCheckResult');

			/* CV2 check */
			if (!Tools::isSubmit('CV2CheckResult'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [CV2CheckResult] not received');
				$boErrorOccurred = true;
			}
			else
				$szCV2CheckResult = Tools::getValue('CV2CheckResult');

			/* 3DS check */
			if (!Tools::isSubmit('ThreeDSecureAuthenticationCheckResult'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [ThreeDSecureAuthenticationCheckResult] not received');
				$boErrorOccurred = true;
			}
			else
				$szThreeDSecureAuthenticationCheckResult = Tools::getValue('ThreeDSecureAuthenticationCheckResult');

			/* amount (same as value passed into payment form - echoed back out by payment form) */
			if (!Tools::isSubmit('Amount'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [Amount] not received');
				$boErrorOccurred = true;
			}
			else
			{
				if (Tools::getValue('Amount') == null)
					$nAmount = null;
				else
					$nAmount = (int)Tools::getValue('Amount');
			}

			/* currency code (same as value passed into payment form - echoed back out by payment form) */
			if (!Tools::isSubmit('CurrencyCode'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [CurrencyCode] not received');
				$boErrorOccurred = true;
			}
			else
			{
				if (Tools::getValue('CurrencyCode') == null)
					$nCurrencyCode = null;
				else
					$nCurrencyCode = (int)Tools::getValue('CurrencyCode');
			}

			/* order ID (same as value passed into payment form - echoed back out by payment form) */
			if (!Tools::isSubmit('OrderID'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [OrderID] not received');
				$boErrorOccurred = true;
			}
			else
				$szOrderID = Tools::getValue('OrderID');

			/* transaction type (same as value passed into payment form - echoed back out by payment form) */
			if (!Tools::isSubmit('TransactionType'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [TransactionType] not received');
				$boErrorOccurred = true;
			}
			else
				$szTransactionType = Tools::getValue('TransactionType');

			/* transaction date/time (same as value passed into payment form - echoed back out by payment form) */
			if (!Tools::isSubmit('TransactionDateTime'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [TransactionDateTime] not received');
				$boErrorOccurred = true;
			}
			else
				$szTransactionDateTime = Tools::getValue('TransactionDateTime');

			/* order description (same as value passed into payment form - echoed back out by payment form) */
			if (!Tools::isSubmit('OrderDescription'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [OrderDescription] not received');
				$boErrorOccurred = true;
			}
			else
				$szOrderDescription = Tools::getValue('OrderDescription');

			/* customer name (not necessarily the same as value passed into payment form - as the customer can change it on the form) */
			if (!Tools::isSubmit('CustomerName'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [CustomerName] not received');
				$boErrorOccurred = true;
			}
			else
				$szCustomerName = Tools::getValue('CustomerName');

			/* address1 (not necessarily the same as value passed into payment form - as the customer can change it on the form) */
			if (!Tools::isSubmit('Address1'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [Address1] not received');
				$boErrorOccurred = true;
			}
			else
				$szAddress1 = Tools::getValue('Address1');

			/* address2 (not necessarily the same as value passed into payment form - as the customer can change it on the form) */
			if (!Tools::isSubmit('Address2'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [Address2] not received');
				$boErrorOccurred = true;
			}
			else
				$szAddress2 = Tools::getValue('Address2');

			/* address3 (not necessarily the same as value passed into payment form - as the customer can change it on the form) */
			if (!Tools::isSubmit('Address3'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [Address3] not received');
				$boErrorOccurred = true;
			}
			else
				$szAddress3 = Tools::getValue('Address3');

			/* address4 (not necessarily the same as value passed into payment form - as the customer can change it on the form) */
			if (!Tools::isSubmit('Address4'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [Address4] not received');
				$boErrorOccurred = true;
			}
			else
				$szAddress4 = Tools::getValue('Address4');

			/* city (not necessarily the same as value passed into payment form - as the customer can change it on the form) */
			if (!Tools::isSubmit('City'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [City] not received');
				$boErrorOccurred = true;
			}
			else
				$szCity = Tools::getValue('City');

			/* state (not necessarily the same as value passed into payment form - as the customer can change it on the form) */
			if (!Tools::isSubmit('State'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [State] not received');
				$boErrorOccurred = true;
			}
			else
				$szState = Tools::getValue('State');

			/* post code (not necessarily the same as value passed into payment form - as the customer can change it on the form) */
			if (!Tools::isSubmit('PostCode'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [PostCode] not received');
				$boErrorOccurred = true;
			}
			else
				$szPostCode = Tools::getValue('PostCode');

			/* country code (not necessarily the same as value passed into payment form - as the customer can change it on the form) */
			if (!Tools::isSubmit('CountryCode'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [CountryCode] not received');
				$boErrorOccurred = true;
			}
			else
			{
				if (Tools::getValue('CountryCode') == '')
					$nCountryCode = null;
				else
					$nCountryCode = (int)(Tools::getValue('CountryCode'));
			}
			/* email address */
			if (!Tools::isSubmit('EmailAddress'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [EmailAddress] not received');
				$boErrorOccurred = true;
			}
			else
				$szEmailAddress = Tools::getValue('EmailAddress');

			/* phone number */
			if (!Tools::isSubmit('PhoneNumber'))
			{
				$szOutputMessage = addStringToStringList($szOutputMessage, 'Expected variable [PhoneNumber] not received');
				$boErrorOccurred = true;
			}
			else
				$szPhoneNumber = Tools::getValue('PhoneNumber');

		}
		catch (Exception $e)
		{
			$boErrorOccurred = true;
			$szOutputMessage = 'Error';
			if (Tools::isSubmit('Message'))
				$szOutputMessage = Tools::getValue('Message');
		}

		/* The nOutputProcessedOK should return 0 except if there has been an error talking to the gateway or updating the website order system.
		Any other process status shown to the gateway will prompt the gateway to send an email to the merchant stating the error.
		The customer will also be shown a message on the hosted payment form detailing the error and will not return to the merchants website.*/
		$nOutputProcessedOK = 0;

		if (is_null($nStatusCode))
			$nOutputProcessedOK = 30;

		if ($boErrorOccurred == true)
			$nOutputProcessedOK = 30;

		function createhash($PreSharedKey, $Password)
		{
			$hashcode = 'PreSharedKey='.$PreSharedKey;
			$hashcode = $hashcode.'&MerchantID='.Tools::getValue('MerchantID');
			$hashcode = $hashcode.'&Password='.$Password;
			$hashcode = $hashcode.'&StatusCode='.Tools::getValue('StatusCode');
			$hashcode = $hashcode.'&Message='.Tools::getValue('Message');
			$hashcode = $hashcode.'&PreviousStatusCode='.Tools::getValue('PreviousStatusCode');
			$hashcode = $hashcode.'&PreviousMessage='.Tools::getValue('PreviousMessage');
			$hashcode = $hashcode.'&CrossReference='.Tools::getValue('CrossReference');
			$hashcode = $hashcode.'&AddressNumericCheckResult='.Tools::getValue('AddressNumericCheckResult');
			$hashcode = $hashcode.'&PostCodeCheckResult='.Tools::getValue('PostCodeCheckResult');
			$hashcode = $hashcode.'&CV2CheckResult='.Tools::getValue('CV2CheckResult');
			$hashcode = $hashcode.'&ThreeDSecureAuthenticationCheckResult='.Tools::getValue('ThreeDSecureAuthenticationCheckResult');
			$hashcode = $hashcode.'&CardType='.Tools::getValue('CardType');
			$hashcode = $hashcode.'&CardClass='.Tools::getValue('CardClass');
			$hashcode = $hashcode.'&CardIssuer='.Tools::getValue('CardIssuer');
			$hashcode = $hashcode.'&CardIssuerCountryCode='.Tools::getValue('CardIssuerCountryCode');
			$hashcode = $hashcode.'&Amount='.Tools::getValue('Amount');
			$hashcode = $hashcode.'&CurrencyCode='.Tools::getValue('CurrencyCode');
			$hashcode = $hashcode.'&OrderID='.Tools::getValue('OrderID');
			$hashcode = $hashcode.'&TransactionType='.Tools::getValue('TransactionType');
			$hashcode = $hashcode.'&TransactionDateTime='.Tools::getValue('TransactionDateTime');
			$hashcode = $hashcode.'&OrderDescription='.Tools::getValue('OrderDescription');
			$hashcode = $hashcode.'&CustomerName='.Tools::getValue('CustomerName');
			$hashcode = $hashcode.'&Address1='.Tools::getValue('Address1');
			$hashcode = $hashcode.'&Address2='.Tools::getValue('Address2');
			$hashcode = $hashcode.'&Address3='.Tools::getValue('Address3');
			$hashcode = $hashcode.'&Address4='.Tools::getValue('Address4');
			$hashcode = $hashcode.'&City='.Tools::getValue('City');
			$hashcode = $hashcode.'&State='.Tools::getValue('State');
			$hashcode = $hashcode.'&PostCode='.Tools::getValue('PostCode');
			$hashcode = $hashcode.'&CountryCode='.Tools::getValue('CountryCode');
			$hashcode = $hashcode.'&EmailAddress='.Tools::getValue('EmailAddress');
			$hashcode = $hashcode.'&PhoneNumber='.Tools::getValue('PhoneNumber');

			return sha1($hashcode);
		}

		/* Check the passed HashDigest against our own to check the values passed are legitimate.*/
		$str1 = Tools::getValue('HashDigest');
		$hashcode = createhash(Configuration::get('PAYMENTSENSE_PSK'), Configuration::get('PAYMENTSENSE_GATEWAYPASS'));
		if ($hashcode != $str1)
		{
			$nOutputProcessedOK = 30;
			$szOutputMessage .= 'Hashes did not match';
		}

		/* You should put your code that does any post transaction tasks (e.g. updates the order object, sends the customer an email etc) in this section */
		if ($nOutputProcessedOK != 30)
		{
			$nOutputProcessedOK = 0;
			/* Alter this line once you've implemented the code. */
			$szOutputMessage = $szMessage;
			try
			{
				switch ($nStatusCode)
				{
					/* transaction authorised */
					case 0:
						$orderState = 2;
						break;
					/* card referred (treat as decline) */
					case 4:
						$orderState = 8;
						break;
					/* transaction declined */
					case 5:
						$orderState = 8;
						break;
					/* duplicate transaction */
					case 20:
						/* need to look at the previous status code to see if the transaction was successful*/
						if ($nPreviousStatusCode == 0)
							$orderState = 2;
						else
							$orderState = 8;
						break;
					/* error occurred */
					case 30:
						$orderState = 8;
						break;
					default:
						$orderState = 8;
						break;
				}

				$db = Db::getInstance();
				$OrderID = Tools::substr(Tools::getValue('OrderID'), strpos(Tools::getValue('OrderID'), '~') + 1);
				$orderTotal = Tools::getValue('orderTotal');

				$paymentsense = new PaymentSense();
				$cart = new Cart((int)$OrderID);
				$customer = new Customer((int)$cart->id_customer);

				$AdditionalDetails = array('CrossReference' => $szCrossReference, 'Address Check' => $szAddressNumericCheckResult, 'Postcode Check' => $szPostCodeCheckResult,
				'CV2 Check' => $szCV2CheckResult, '3DS Check' => $szThreeDSecureAuthenticationCheckResult);

				/* Update order */
				$paymentsense->validateOrder((int)$OrderID, $orderState, $orderTotal, $paymentsense->displayName, $szMessage, $AdditionalDetails, null, false, $customer->secure_key);
			}
			catch (Exception $e)
			{
				$nOutputProcessedOK = 30;
				$szOutputMessage = 'Error updating website system, please ask the developer to check code '.$e;
			}
		}

		if ($nOutputProcessedOK != 0 && $szOutputMessage == '')
			$szOutputMessage = 'Unknown error';

		/* output the status code and message letting the payment form know whether the transaction result was processed successfully */
		echo 'StatusCode='.$nOutputProcessedOK.'&Message='.Tools::safeOutput($szOutputMessage);
}