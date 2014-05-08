<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 15821 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class KlarnaIntegration
{
	private $_klarna;
	private $_version;

	public function __construct($klarna)
	{
		$this->_klarna = $klarna;
		$this->_version = _PS_VERSION_ >= 1.5;
	}

	public function cancel($nb)
	{
		if ($this->_version)
			return $this->_klarna->cancelReservation($nb);
		return $this->_klarna->deleteInvoice($nb);
	}

	public function activate($pno, $rno, $gender, $ocr = "", $flags = KlarnaFlags::NO_FLAG, $pclass = KlarnaPClass::INVOICE, $encoding = null, $clear = true)
	{
		if ($this->_version)
			return $this->_klarna->activateReservation($pno, $rno, $gender, $ocr, $flags, $pclass, $encoding, $clear);
		return array('ok', $this->_klarna->activateInvoice($rno, $pclass));
	}

	public function reserve($pno, $gender, $amount, $flags = 0, $pclass = KlarnaPClass::INVOICE, $encoding = null, $clear = true)
	{
		if ($this->_version)
			return $this->_klarna->reserveAmount($pno, $gender, $amount, $flags, $pclass, $encoding, $clear);
		return $this->_klarna->addTransaction($pno, $gender, $flags, $pclass, $encoding, $clear);
	}

	public function updateOrderNo($rno, $cartId)
	{
		if (!$this->_version)
			return $this->_klarna->updateOrderNo($rno, $cartId);
		return $rno;
	}
}