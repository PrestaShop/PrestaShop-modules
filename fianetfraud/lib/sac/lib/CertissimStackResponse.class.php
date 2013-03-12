<?php

/**
 * Objet XML reçu en réponse au script get_validstack
 *
 * @version 3.1
 * @author ESPIAU Nicolas
 */
class CertissimStackResponse extends CertissimXMLResult
{

	public function getResults()
	{
		$results = array();

		foreach ($this->getChildrenByName('result') as $result)
		{
			$results[] = new CertissimResultResponse($result->getXML());
		}

		return $results;
	}

}