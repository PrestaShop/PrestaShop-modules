<?php

/**
 * Object XML <transaction> contained indise get_alert get_validation and get_validstack responses
 *
 * @author ESPIAU Nicolas
 */
class CertissimTransactionResponse extends CertissimXMLResult
{

	/**
	 * returns the value of the element <detail>
	 *
	 * @return string
	 */
	public function getDetail()
	{
		$detail = array_pop($this->getChildrenByName('detail'));
		return $detail->getValue();
	}

	/**
	 * returns the value of the attribute $name of the element <eval> of existing, null otherwise
	 *
	 * @param string $name
	 * @return string
	 */
	private function getEvalItem($name)
	{
		$evals = $this->getChildrenByName('eval');
		$eval = array_pop($evals);
		$xml_eval = new CertissimXMLResult($eval->getXML());

		$funcname = "return$name";
		return $xml_eval->$funcname();
	}

	/**
	 * returns the value of the element <eval>
	 *
	 * @return CertissimXMLElement
	 */
	public function getEval()
	{
		$evals = $this->getChildrenByName('eval');
		$eval = array_pop($evals);
		return $eval->getValue();
	}

	/**
	 * returns the value of the attribute id of the element <classement>
	 *
	 * @return int
	 */
	public function getClassementID()
	{
		$classements = $this->getChildrenByName('classement');
		$classement = array_pop($classements);

		return $classement->getAttribute('id');
	}

	/**
	 * returns the value of the element <classement>
	 *
	 * @return string
	 */
	public function getClassementLabel()
	{
		$classements = $this->getChildrenByName('classement');
		$classement = array_pop($classements);

		return $classement->getValue();
	}

	/**
	 * adds a magic method to the CertissimTransactionResponse objects
	 * 
	 * @param string $name name of the called method
	 * @param array $params params given to the method called
	 * @return mixed
	 */
	public function __call($name, array $params)
	{
		//getEvalItem returns the value of the attribute Item in the element <eval> if it exists, null otherwise
		if (preg_match('#^getEval.+$#', $name))
		{
			$elementname = strtolower(preg_replace('#^getEval(.+)$#', '$1', $name));
			return $this->getEvalItem($elementname);
		}

		return parent::__call($name, $params);
	}

}