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

class SceauSendratingCommentsResponseResult extends SceauDOMDocument
{
	public function getIdComment()
	{
		return $this->getElementsByTagName('id')->item(0)->nodeValue;
	}
	public function getDateComment()
	{
		return $this->getElementsByTagName('date')->item(0)->nodeValue;
	}
	public function getNoteComment()
	{
		return $this->getElementsByTagName('note')->item(0)->nodeValue;
	}
	public function getComment()
	{
		return $this->getElementsByTagName('commentaire')->item(0)->nodeValue;
	}
	public function getGeneralNoteComment()
	{
		return $this->getElementsByTagName('noteglobale')->item(0)->nodeValue;
	}
	public function getNbComment()
	{
		return $this->getElementsByTagName('nbravis')->item(0)->nodeValue;
	}
	public function getLabelProductComment()
	{
		return $this->getElementsByTagName('produitlibelle')->item(0)->nodeValue;
	}
	public function getIdProductComment()
	{
		return $this->getElementsByTagName('produitid')->item(0)->nodeValue;
	}
	public function getCodeeanProductComment()
	{
		return $this->getElementsByTagName('produitcodeean')->item(0)->nodeValue;
	}
	public function getFirstnameComment()
	{
		return $this->getElementsByTagName('prenom')->item(0)->nodeValue;
	}
	public function getNameComment()
	{
		return $this->getElementsByTagName('nom')->item(0)->nodeValue;
	}
}