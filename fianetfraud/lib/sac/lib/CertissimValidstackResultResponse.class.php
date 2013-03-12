<?php

/**
 * Object CertissimXMLElement <result> child of <validstack>, got as a response from webservice get_validstack.cgi
 *
 * @author nespiau
 */
class CertissimValidstackResultResponse extends CertissimXMLResult
{

	/**
	 * returns the value of the element <detail>
	 *
	 * @return string
	 */
	public function getDetail()
	{
		$children = $this->getChildrenByName('detail');
		$detail = array_pop($children);
		return $detail->getValue();
	}

	/**
	 * returns true if an error made the fraud screening fail, false otherwise
	 *
	 * @return bool
	 */
	public function hasError()
	{
		return !is_null($this->returnErrorid());
	}

}