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

/**
 * Implements the response from the WS Tagline
 *
 * @author ESPIAU Nicolas
 */
class KwixoTaglineResponse extends KwixoDOMDocument
{

	/**
	 * returns true if the script encoutered an error, false otherwise
	 *
	 * @return bool
	 */
	public function hasError()
	{
		return $this->root->hasAttribute('liberr');
	}

	/**
	 * returns the error code if an error occured, returns the tag value otherwise
	 * 
	 * @return int
	 */
	public function getTagValue()
	{
		return $this->root->nodeValue;
	}

	/**
	 * returns the error label if an error occured, returns an empty string otherwise
	 * 
	 * @return string
	 */
	public function getError()
	{
		return $this->root->getAttribute('liberr');
	}

	/**
	 * returns the id of the transaction in the Kwixo system
	 * 
	 * @return string
	 */
	public function getTransactionID()
	{
		return $this->root->getAttribute('transactionid');
	}

	/**
	 * returns the total amount of the transaction in cents
	 * 
	 * @return int
	 */
	public function getAmount()
	{
		return $this->root->getAttribute('montant');
	}

	/**
	 * returns the balance the customer sill has to pay, in cents
	 * 
	 * @return int
	 */
	public function getBalance()
	{
		return $this->root->getAttribute('solde');
	}

	/**
	 * returns the amount in cents of the promotion
	 * 
	 * @return int
	 */
	public function getPromotionAmount()
	{
		return $this->root->getAttribute('mtpromo');
	}

	/**
	 * returns the promotion code
	 * 
	 * @return string
	 */
	public function getPromotionCode()
	{
		return $this->root->getAttribute('codepromo');
	}

	/**
	 * returns true if the customer chose a payment in installments, false otherwise
	 * 
	 * @return bool
	 */
	public function isCredit()
	{
		return $this->root->getAttribute('credit') == 'oui';
	}

	/**
	 * returns the status of the credit request
	 * Values:
	 * - att: request pending
	 * - ok: credit granted
	 * - ref: credit refused
	 * - 40j: payment converted into payment cash after 40 days
	 * - ann: request cancelled
	 * 
	 * @return string
	 */
	public function getCreditInformation()
	{
		return $this->root->getAttribute('creditstat');
	}

	/**
	 * returns the number of installments
	 * 
	 * @return int
	 */
	public function getInstallmentCount()
	{
		return $this->root->getAttribute('nbmensualites');
	}

	/**
	 * returns the amount of one installment with insurance, in cents
	 * 
	 * @return int
	 */
	public function getInstallmentAmountWithInsurance()
	{
		return $this->root->getAttribute('montantmensualiteavecass');
	}

	/**
	 * returns the amount of one installment without insurance, in cents
	 * 
	 * @return int
	 */
	public function getInstallmentAmountWithoutInsurance()
	{
		return $this->root->getAttribute('montantmensualitesansass');
	}

	/**
	 * returns true if a risk of fraud has been detected, false otherwise
	 * 
	 * @return bool
	 */
	public function isFraudRiskDetected()
	{
		return $this->root->getAttribute('score') == 'negatif';
	}

	/**
	 * returns the value of the attribute <i>score</i>, which indicates if the transaction passed with success the fraud analysis
	 * Values:
	 * - positif: the transaction passed the fraud analysis with success, it means the system estimates it's risk free
	 * - negatif: the fraud analysis revealed a risk
	 * 
	 * @return string
	 */
	public function getScore()
	{
		return $this->root->getAttribute('score');
	}
}