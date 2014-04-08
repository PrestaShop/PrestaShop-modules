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
 * This class implements the response of the script sendrating.cgi
 */

class SceauSendratingResponse extends SceauDOMDocument
{

	/**
	 * returns true if the stream is valid and has correctly been received by Sceau, false otherwise
	 * 
	 * @return bool
	 */
	public function isValid()
	{
		return $this->root->getAttribute('type') == 'OK';
	}

	/**
	 * returns true if the stream encountered a fatal error, false otherwise
	 * 
	 * @return bool
	 */
	public function hasFatalError()
	{
		return $this->root->tagName == 'unluck';
	}

	/**
	 * returns the message given as an answer from Sceau. It matches with the error label if an error occured.
	 * 
	 * @return string
	 */
	public function getDetail()
	{
		if ($this->hasFatalError())
			return $this->root->nodeValue;

		return $this->getElementsByTagName('detail')->item(0)->nodeValue;
	}

}