<?php

/**
 * Balise <infocommande>
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class SceauInfocommande extends SceauXMLElement
{

	public function __construct($siteid, $refid, $montant, $ip, $timestamp, $devise="EUR")
	{
		parent::__construct();

		$this->childSiteid($siteid, array());
		$this->childRefid($refid, array());
		$this->childMontant($montant, array('devise' => $devise));
		$this->childIp($ip, array('timestamp' => $timestamp));
	}

}