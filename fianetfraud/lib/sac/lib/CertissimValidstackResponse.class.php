<?php

/**
 * Objet CertissimXMLElement <validstack> got from the response of the script staking.cgi or stackfast.cgi
 *
 * @author ESPIAU Nicolas
 */
class CertissimValidstackResponse extends CertissimXMLResult
{

	const ROOT_NAME = "validstack";

	public function __construct($data)
	{
		$data = preg_replace('#\"#', '\'', $data);
		parent::__construct($data);

		if ($this->getName() != self::ROOT_NAME)
		{
			$msg = "L'�l�ment racine n'est pas valide : ".$this->getName()." trouve, ".self::ROOT_NAME." attendu.";
			CertissimLogger::insertLog(__FILE__." - __construct()", $msg);
		}
	}

	/**
	 * returns true if the stack has been refused, false otherwise
	 *
	 * @return bool
	 */
	public function hasFatalError()
	{
		return count($this->getChildrenByName('unluck')) > 0;
	}

	/**
	 * returns the error label if <unluck> response got, false otherwise
	 *
	 * @return mixed
	 */
	public function getError()
	{
		$unluck = $this->hasFatalError() ? array_pop($this->getChildrenByName('unluck'))->getValue() : null;

		return ($unluck);
	}

	/**
	 * returns an array containing all the <result> elements as CertissimValidstackResultResponse
	 *
	 * @return array
	 */
	public function getResults()
	{
		$results = array();
		foreach ($this->getChildrenByName('result') as $result)
		{
			$results[] = new CertissimValidstackResultResponse($result->getXML());
		}

		return $results;
	}

	public function getResultCount()
	{
		return count($this->$this->getChildrenByName('result'));
	}

}