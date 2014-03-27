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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

require_once(_PS_MODULE_DIR_.'prediggo/classes/DataExtractorToXML.php');

class OrderExtractorToXML extends DataExtractorToXML
{
	/** @var array List of Prediggo configuration by shop */
	private $aPrediggoConfigs;

	/**
	  * Initialise the object variables
	  *
	  * @param string $sRepositoryPath path of the XML repository
	  * @param array $params Specific parameters of the object
	  */
	public function __construct($sRepositoryPath, $params, $bLogEnable)
	{
		$this->sRepositoryPath 	= $sRepositoryPath;
		$this->bLogEnable 		= (int)$bLogEnable;
		$this->_logs 			= array();
		$this->_errors 			= array();
		$this->_confirmations 	= array();
		$this->sEntity 			= 'transaction';
		$this->sFileNameBase 	= 'transactions';
		$this->sEntityRoot 		= 'transactions';
		
		$this->aPrediggoConfigs = $params['aPrediggoConfigs'];
	}

	/**
	  * Get the list of entities by a sql result
	  *
	  * @return Object SQL Result
	  */
	public function getEntities()
	{
		$sWhere = '';
		foreach($this->aPrediggoConfigs as $iIDShop => $oPrediggoConfig)
			$sWhere .= '(id_shop = '.(int)$iIDShop.' AND DATE_ADD(invoice_date, INTERVAL -1 DAY) <= \''.pSQL(date('Y-m-d H:i:s')).'\' AND invoice_date >= \''.pSQL(date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d')-((int)$oPrediggoConfig->nb_days_order_valide), date('Y')))).'\') OR';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT `id_order`, `id_shop`
		FROM `'._DB_PREFIX_.'orders`
		WHERE '.substr($sWhere, 0, -2).'
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

				$profile = $dom->createElement('profile', (int)$aEntity['id_shop']);
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