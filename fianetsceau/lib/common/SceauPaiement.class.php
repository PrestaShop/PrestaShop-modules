<?php

/**
 * Balise <paiement>
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class SceauPaiement extends SceauXMLElement
{
	const TYPE_CARTE = '1';
	const TYPE_CHEQUE = '2';
	const TYPE_REMBOURSEMENT = '3';
	const TYPE_VIREMENT = '4';
	const TYPE_CBNFOIS = '5';
	const TYPE_PAYPAL = '6';
	const TYPE_1EURO = '7';
	const TYPE_KWIXO = '8';

	public function __construct($type=null)
	{
		parent::__construct();
		$this->childType($type);
	}

}