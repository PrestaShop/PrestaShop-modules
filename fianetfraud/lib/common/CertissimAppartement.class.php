<?php

/**
 * @author ESPIAU Nicolas
 */
class CertissimAppartement extends CertissimXMLElement
{

	const FORMAT = 1;

	public function __construct(array $params = array())
	{
		parent::__construct();

		foreach ($params as $key => $value)
		{
			$funcname = "child$key";
			$this->$funcname($value);
		}
	}

}