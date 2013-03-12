<?php

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