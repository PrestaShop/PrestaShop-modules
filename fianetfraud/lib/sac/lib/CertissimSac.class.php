<?php

/**
 * Certissim main class: gives all the methods to access the Certissim webservices
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class CertissimSac extends CertissimService
{

	const PRODUCT_NAME = 'sac';
	const IDSEPARATOR = '^'; //séparateur des refid pour la méthode getvalidstack
	const CONSULT_MODE_MINI = 'mini';
	const CONSULT_MODE_FULL = 'full';

	/**
	 * sends an order to Certissim singet.cgi webservice
	 *
	 * @param XMLElement $xml control stream
	 * @return string script response
	 */
	public function sendSinget(CertissimXMLElement $xml)
	{
		$data = array(
			'siteid' => $this->getSiteId(),
			'controlcallback' => $xml->getXML(),
		);
		$con = new CertissimFianetSocket($this->getUrlsinget(), 'POST', $data);
		$res = $con->send();
		return $res;
	}

	/**
	 * sends a stack of transactions to Certissim stacking.cgi WS
	 *
	 * @param XMLElement $stack
	 * @return string script response
	 */
	public function sendStacking(CertissimXMLElement $stack)
	{
		$data = array(
			'siteid' => $this->getSiteId(),
			'controlcallback' => $stack->getXML(),
		);
		$con = new CertissimFianetSocket($this->getUrlstacking(), 'POST', $data);
		$result = $con->send();

		$xmlresult = ($result !== false ? new CertissimXMLElement($result) : false);

		return $xmlresult;
	}

	/**
	 * sends a stack of transactions to Certissim stackfast.cgi WS
	 *
	 * @param XMLElement $stack
	 * @return string script response
	 */
	public function sendStackfast(CertissimXMLElement $stack)
	{
		$data = array(
			'siteid' => $this->getSiteId(),
			'controlcallback' => $stack->getXML(),
		);
		$con = new CertissimFianetSocket($this->getUrlstacking(), 'POST', $data);
		return new CertissimXMLElement($con->send());
	}

	/**
	 * download the result of the fraud screening for the order referenced by $refid
	 *
	 * @param string $refid order ref
	 * @param string $mode answer mode (mini, full)
	 * @param bool $repFT display FT answer or not
	 * @return string script response
	 */
	public function getValidation($refid, $mode = 'mini', $repFT = '0')
	{
		$data = array(
			'SiteID' => $this->getSiteId(),
			'Pwd' => $this->getPassword(),
			'RefID' => $refid,
			'Mode' => $mode,
			'RepFT' => $repFT
		);
		$con = new CertissimFianetSocket($this->getUrlgetvalidation(), 'POST', $data);
		return new CertissimXMLElement($con->send());
	}

	/**
	 * download the result of the fraud screening for the order referenced by $refid and sends the response to $urlback in POST
	 *
	 * @param string $refid order ref
	 * @param string $mode answer mode
	 * @param bool $repFT display FT answer or not
	 * @param string $urlback URL whereto send the response
	 * @return string script response
	 */
	public function getRedirectValidation($refid, $mode = Sac::CONSULT_MODE_MINI, $urlback = null, $repFT = '0')
	{
		$data = array(
			'SiteID' => $this->getSiteId(),
			'Pwd' => $this->getPassword(),
			'RefID' => $refid,
			'Mode' => $mode,
			'RepFT' => $repFT,
			'urlBack' => (!is_null($urlback) ? $urlback : $this->getUrldefaultredirectvaildationurlback()),
		);
		$con = new CertissimFianetSocket($this->getUrlredirectvalidation(), 'POST', $data);
		return new CertissimXMLElement($con->send());
	}

	/**
	 * returns the evaluations list for orders referenced by the reflist given in parameter
	 *
	 * @param array $listId orders ref list
	 * @param string $mode answer mode
	 * @param bool $repFT display FT answer or not
	 * @return string script response
	 */
	public function getValidstackByReflist(array $listId, $mode = Sac::CONSULT_MODE_MINI, $repFT = '0')
	{
		$list = implode(CertissimSac::IDSEPARATOR, $listId);

		$data = array(
			'SiteID' => $this->getSiteId(),
			'Pwd' => $this->getPassword(),
			'Mode' => $mode,
			'RepFT' => $repFT,
			'ListID' => $list,
			'Separ' => CertissimSac::IDSEPARATOR
		);
		return $this->getValidstack($data);
	}

	/**
	 * returns the evaluations list for all the orders made the date $date
	 *
	 * @param string $date
	 * @param int $numpage page number to read
	 * @param string $mode answer mode
	 * @param bool $repFT display FT answer or not
	 * @return string script response
	 */
	public function getValidstackByDate($date, $numpage, $mode = Sac::CONSULT_MODE_MINI, $repFT = '0')
	{
		//checks the date format
		if (!preg_match('#^[0-9]{2}/[0-1][0-9]/[0-9]{4}$#', $date))
		{
			$msg = "La date '$date' n'est pas au bon format. Format attendu : dd/mm/YYYY";
			CertissimLogger::insertLog(__METHOD__." : ".__LINE__, $msg);
			throw new Exception($msg);
		}

		$data = array(
			'SiteID' => $this->getSiteId(),
			'Pwd' => $this->getPassword(),
			'Mode' => $mode,
			'RepFT' => $repFT,
			'DtStack' => $date,
			'Ind' => $numpage
		);
		return $this->getValidstack($data);
	}

	/**
	 * make a call to Certissim get_validstack.cgi WS
	 *
	 * @param array $param params to send to WS
	 * @return string script response
	 */
	private function getValidstack($param)
	{
		$con = new CertissimFianetSocket($this->getUrlgetvalidstack(), 'POST', $param);
		return new CertissimXMLElement($con->send());
	}

	/**
	 * gets all the reevaluations
	 * 
	 * @param string $mode call mode (all, new, old)
	 * @param string $output answer mode
	 * @param int $repFT display FT answer or not
	 * @return CertissimXMLElement
	 */
	public function getAlert($mode = 'new', $output = 'mini', $repFT = 0)
	{
		$data = array(
			'SiteID' => $this->getSiteId(),
			'Pwd' => $this->getPassword(),
			'Mode' => $mode,
			'Output' => $output,
			'RepFT' => $repFT,
		);
		$con = new CertissimFianetSocket($this->getUrlgetalert(), 'POST', $data);
		return new CertissimXMLElement($con->send());
	}

	/**
	 * returns the URL to order detail page on Fia-Net's portal for the order referenced by $rid
	 *
	 * @param string $rid order ref
	 * @return string
	 */
	public function getVisuCheckUrl($rid)
	{
		$url = $this->getUrlvisucheckdetail();
		$url .= '?sid='.$this->getSiteid().'&log='.$this->getLogin().'&pwd='.$this->getPasswordurlencoded()."&rid=$rid";

		return $url;
	}

}