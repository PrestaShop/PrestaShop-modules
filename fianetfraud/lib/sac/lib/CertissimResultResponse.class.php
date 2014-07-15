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
 * Objet XML <result>
 *
 * @author ESPIAU Nicolas
 */
class CertissimResultResponse extends CertissimXMLResult
{

	/**
	 * returns true if the transaction has been found on fia-Net's portal, false otherwise
	 *
	 * @return bool
	 */
	public function hasBeenFound()
	{
		return $this->returnRetour() == 'trouvee';
	}

	/**
	 * returns true if an error has been encoutered, false otherwise
	 *
	 * @return bool
	 */
	public function hasError()
	{
		return in_array($this->returnRetour(), array('param_error', 'internal_error'));
	}

	/**
	 * returns an array containing every transactions got in the WS answer
	 *
	 * @return array
	 */
	public function getTransactions()
	{
		$transactions = array();

		foreach ($this->getChildrenByName('transaction') as $transac)
			$transactions[] = new CertissimTransactionResponse($transac->getXML());

		return $transactions;
	}

	/**
	 * returns the most recent transaction
	 * 
	 * @return CertissimTransactionResponse
	 */
	public function getMostRecentTransaction()
	{
		$transactions = $this->getTransactions();
		$newer = array_shift($transactions);
//		foreach ($transactions as $key => $transaction)
//		{
//			if ($transaction->returnCid() > $newer->returnCid())
//				$newer = $transaction;
//		}
		return $newer;
	}

}