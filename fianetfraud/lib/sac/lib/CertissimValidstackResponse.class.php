<?php
/**
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Objet CertissimXMLElement <validstack> got from the response of the script staking.cgi or stackfast.cgi
 *
 * @author ESPIAU Nicolas
 */
class CertissimValidstackResponse extends CertissimXMLResult
{

	const ROOT_NAME = 'validstack';

	public function __construct($data)
	{
		$data = preg_replace('#\"#', '\'', $data);
		parent::__construct($data);

		if ($this->getName() != self::ROOT_NAME)
		{
			$msg = 'Element racine non valide : '.$this->getName().' trouve, '.self::ROOT_NAME.' attendu.';
			CertissimLogger::insertLog(__FILE__.' - __construct()', $msg);
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
			$results[] = new CertissimValidstackResultResponse($result->getXML());

		return $results;
	}

	public function getResultCount()
	{
		return count($this->getChildrenByName('result'));
	}

}