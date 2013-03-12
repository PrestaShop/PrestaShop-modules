<?php

/**
 * Description of PointRelai
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class CertissimPointrelais extends CertissimXMLElement
{

	public function __construct($identifiant = null, $enseigne = null, $addresse = null)
	{
		parent::__construct();

		$this->childIdentifiant($identifiant);
		$this->childEnseigne($enseigne);
		$this->childAdresse($addresse);
	}

}