<?php

/**
 * @author Cédric BOURGEOIS : Croissance NET <cbourgeois@croissance-net.com>
 * @copyright Croissance NET
 * @version 1.0
 */

require_once(_PS_MODULE_DIR_.'prediggo/classes/DataExtractorToXML.php');

class OrderExtractorToXML extends DataExtractorToXML
{
	/** @var integer Number of days to define that an order can be exported since its invoice_date */
	public $nbDaysOrderValid;

	/**
	  * Initialise the object variables
	  *
	  * @param string $sRepositoryPath path of the XML repository
	  * @param array $params Specific parameters of the object
	  */
	public function __construct($sRepositoryPath, $params)
	{
		$this->sRepositoryPath = $sRepositoryPath;
		$this->_logs = array();
		$this->_errors = array();
		$this->_confirmations = array();
		$this->sEntity = 'transaction';
		$this->sFileNameBase = 'transactions';
		$this->sEntityRoot = 'transactions';
		$this->nbDaysOrderValid = (int)$params['nbDaysOrderValid'];
	}

	/**
	  * Get the list of entities by a sql result
	  *
	  * @return Object SQL Result
	  */
	public function getEntities()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT `id_order`
		FROM `'._DB_PREFIX_.'orders`
		WHERE DATE_ADD(invoice_date, INTERVAL -1 DAY) <= \''.pSQL(date('Y-m-d H:i:s')).'\' AND invoice_date >= \''.pSQL(date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-((int)$this->nbDaysOrderValid), date('Y')))).'\'
		ORDER BY invoice_date ASC', false);

	}

	/**
	  * Convert the entities data into an xml object and return the xml object as a string
	  *
	  * @param array $aEntity Entity data
	  */
	public function formatEntityToXML($aEntity)
	{
		$dom = new DOMDocument('1.0', 'utf-8');
		// Set the root of the XML
		$root = $dom->createElement($this->sEntity);
		$dom->appendChild($root);

		$oOrder = new Order((int)$aEntity['id_order']);

		$root->setAttribute("isodate", date('c', strtotime($oOrder->invoice_date)));

		$userid = $dom->createElement('userid', (int)$oOrder->id_customer);
		$root->appendChild($userid);

		$transactionid = $dom->createElement('transactionid', (int)$oOrder->id);
		$root->appendChild($transactionid);

		$aOrderProducts = $oOrder->getProducts();

		$sReturn = false;
		if(is_array($aOrderProducts) && count($aOrderProducts)>0)
		{
			foreach($aOrderProducts as $aOrderProduct)
			{
				$item = $dom->createElement('item');
				$root->appendChild($item);

				$itemid = $dom->createElement('itemid', (int)$aOrderProduct['product_id']);
				$item->appendChild($itemid);

				$profile = $dom->createElement('profile', (int)$oOrder->id_lang);
				$item->appendChild($profile);

				if ($oOrder->getTaxCalculationMethod() == PS_TAX_EXC)
					$product_price = $aOrderProduct['product_price'] + $aOrderProduct['ecotax'];
				else
					$product_price = $aOrderProduct['product_price_wt'];

				$price = $dom->createElement('price', number_format(Tools::ps_round($product_price,2), 2, '.', ''));
				$item->appendChild($price);

				$quantity = $dom->createElement('quantity', (int)$aOrderProduct['product_quantity']);
				$item->appendChild($quantity);
			}
			$sReturn = $dom->saveHTML();
		}

		unset($dom);
		unset($oOrder);
		unset($aOrderProducts);

		return $sReturn;
	}
}