<?php

/**
 * Sceau class with her method and scripts
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class Sceau extends SceauService
{
	const PRODUCT_NAME = 'sceau';

	/**
	 * sendrating send xml with sendrating.cgi script
	 *
	 * @param SceauXMLElement $xml xml transaction
	 * @param mixed $paracallback return params
	 */
	public function sendSendrating(SceauXMLElement &$xml)
	{
		$this->addCrypt($xml);

		$data = array(
			'SiteID' => $this->getSiteId(),
			'XMLInfo' => $xml->getXML(),
			'CheckSum' => md5($xml->getXML()),
		);

		$con = new SceauFianetSocket($this->getUrlsendrating(), 'POST', $data);
		$res = $con->send();

		return $res;
	}

	/**
	 * build crypt value
	 *
	 * @param SceauXMLElement $order
	 * @return string
	 */
	public function generateCrypt(SceauXMLElement $order)
	{

		$refid = $order->getChildByName('refid')->getValue();
		$timestamp = $order->getChildByName('ip')->getAttribute('timestamp');
		$userfact = $order->getChildByName('utilisateur');
		$email = strtolower($userfact->getChildByName('email')->getValue());

		return md5($this->getAuthkey().'_'.$refid.'+'.$timestamp.'='.$email);
	}

	/**
	 * add crypt to xml
	 *
	 * @param SceauXMLElement $order 
	 */
	public function addCrypt(SceauXMLElement &$order)
	{
		if (!$this->findCrypt($order))
		{
			$crypt = $this->generateCrypt($order);

			$order->childCrypt($crypt);
		}
	}

	/**
	 * return true if order already have a crypt
	 *
	 * @param SceauXMLElement $order
	 * @return boolean
	 */
	public function findCrypt(SceauXMLElement $order)
	{
		$cryptelement = $order->getChildrenByName('crypt');
		return count($cryptelement) > 0;
	}

}