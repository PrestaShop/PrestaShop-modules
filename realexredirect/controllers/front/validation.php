<?php
/*
*  @author Coccinet <web@coccinet.com>
*  @copyright  2007-2013 Coccinet
*/
class RealexRedirectValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	 */

	public function postProcess()
	{
		$link = $this->context->link;
		$realex = new RealexRedirect();
		if (Tools::isSubmit('choice_dcc'))
		{
			$xm = new SimpleXMLElement('<root/>');
			if (Tools::getValue('DCCCHOICE_yes'))
			{
				$xm->addChild('cardholderrate', Tools::getValue('DCCAUTHRATE'));
				$xm->addChild('cardholderamount', Tools::getValue('DCCAUTHCARDHOLDERAMOUNT'));
				$xm->addChild('cardholdercurrency', Tools::getValue('DCCAUTHCARDHOLDERCURRENCY'));
				$xm->addChild('dcc_choice', Tools::getValue('DCCCHOICE_yes'));
			}
			elseif (Tools::getValue('DCCCHOICE_no'))
			{
				$xm->addChild('cardholderrate', (int)1);
				$xm->addChild('cardholderamount', Tools::getValue('DCCAUTHMERCHANTAMOUNT'));
				$xm->addChild('cardholdercurrency', Tools::getValue('DCCAUTHMERCHANTCURRENCY'));
				$xm->addChild('dcc_choice', Tools::getValue('DCCCHOICE_yes'));
			}
			$xm->addChild('eci', Tools::getValue('eci'));
			$xm->addChild('cavv', Tools::getValue('cavv'));
			$xm->addChild('xid', Tools::getValue('xid'));
			$xm->addChild('dcc', Tools::getValue('DCCCCP'));
			$xm->addChild('dcc_merchant_currency', Tools::getValue('DCCAUTHMERCHANTCURRENCY'));
			$xm->addChild('dcc_merchant_amount', Tools::getValue('DCCAUTHMERCHANTAMOUNT'));
			$xm->addAttribute('timestamp', Tools::getValue('TIMESTAMP'));
			$xm->addChild('sha1hash', Tools::getValue('SHA1HASH'));
			$xm->addChild('payerref', Tools::getValue('PAYER_REF'));
			$xm->addChild('paymentmethod', Tools::getValue('PMT_REF'));
			$xm->addChild('account', Tools::getValue('ACCOUNT'));
			$xm->addChild('orderid', Tools::getValue('ORDER_ID'));
			$xm->addChild('currency', Tools::getValue('CURRENCY'));
			$xm->addChild('amount', Tools::getValue('AMOUNT'));
			$cvn = Tools::getValue('cvn');
			if ($cvn)
				$xm->addChild('cvn', Tools::getValue('cvn'));
			$xm->addChild('billing_code', Tools::getValue('BILLING_CODE'));
			$xm->addChild('billing_country', Tools::getValue('BILLING_CO'));
			$xm->addChild('shipping_code', Tools::getValue('SHIPPING_CODE'));
			$xm->addChild('shipping_country', Tools::getValue('SHIPPING_CO'));
			$xm->addChild('autosettle', Tools::getValue('AUTO_SETTLE_FLAG'));
			$xm = $realex->requestRealvaultReceiptIn($xm, false, true);
			$realex->manageOrder($xm);
		}
		elseif (Tools::isSubmit('PaRes'))
		{
			$xm 		= $realex->requestRealvault3dsVerifysig($_POST);
			$result		= (string)$xm->result;
			$status		= (string)$xm->threedsecure->status;
			$eci		= (string)$xm->threedsecure->eci;
			$cavv		= (string)$xm->threedsecure->cavv;
			$xid		= (string)$xm->threedsecure->xid;
			$xm->addChild('cavv', $cavv);
			$xm->addChild('xid', $xid);
			$md64 				= base64_decode(Tools::getValue('MD'));
			$blow 				= new BlowfishCore($realex->shared_secret, $realex->shared_secret);
			$decrypt 			= $blow->decrypt($md64);
			$infos 				= explode('$', $decrypt);
			if ($status == 'N' || $status == 'U' || $result != '00')
			{
				unset($xm->threedsecure->eci);
				unset($xm->eci);
				if ($infos[17] == 'VISA')
					$xm->addChild('eci', '7');
				elseif ($infos[17] == 'MC')
					$xm->addChild('eci', '0');
			}
			else
				$xm->addChild('eci', $eci);
			if ($result == '520')
			{
				$xm->addChild('orderid', $infos[1]);
				$xm->addChild('account', $infos[8]);
			}
			if ((($status == 'N' || $status == 'U' || $result != '00') && $realex->liability))
			{
				if ($result == '110')
				{
					unset($xm->threedsecure->eci);
					unset($xm->eci);
					if ($infos[17] == 'VISA')
						$xm->addChild('eci', '7');
					elseif ($infos[17] == 'MC')
						$xm->addChild('eci', '0');
				}
				$realex->manageOrder($xm, true, true);
			}
			else
			{
				$xm = $realex->requestRealvaultReceiptIn($xm);
				$realex->manageOrder($xm);
			}
		}
		elseif (Tools::isSubmit('submit_registered'))
		{
			$type_card = Tools::getValue('TYPE_CARD');
			if ($realex->cvn && !Tools::getValue('cvn')
					|| ($realex->cvn && (!is_numeric(Tools::getValue('cvn'))
					|| (Tools::strlen(Tools::getValue('cvn')) != 3 && $type_card != 'AMEX')
					|| (Tools::strlen(Tools::getValue('cvn')) != 4 && $type_card == 'AMEX')))
				)
			{
				Tools::redirect($link->getModuleLink('realexredirect', 'payment?error=cvn', array(), true));
				exit;
			}
			if (Tools::getValue('THREEDS') == 1)
			{
				$xm = $realex->requestRealvault3dsVerifyenrolled($_POST);
				$result 							= $xm->result;
				$enrolled							= (string)$xm->enrolled;
				if ($result == '00')
				{
					$timestamp						= (string)$xm->attributes()->timestamp;
					$orderid						= (string)$xm->orderid;
					$currency						= (string)$xm->currency;
					$amount							= (string)$xm->amount;
					$payerref						= (string)$xm->payerref;
					$paymentmethod					= (string)$xm->paymentmethod;
					$url_redirect					= (string)$xm->url;
					$pareq							= (string)$xm->pareq;
					$message						= (string)$xm->message;
					$authcode						= (string)$xm->authcode;
					$pasref							= (string)$xm->pasref;
					$account						= (string)$xm->account;
					$autosettle						= (string)$xm->autosettle;
					$autosettle						= (string)$xm->autosettle;
					$md								= (string)$timestamp.'$'.
						$orderid.'$'.
						$currency.'$'.
						$amount.'$'.
						$payerref.'$'.
						$paymentmethod.'$'.
						$result.'$'.
						$message.'$'.
						$account.'$'.
						Tools::getValue('cvn').'$'.
						Tools::getValue('DCC').'$'.
						Tools::getValue('DCC_CHOICE').'$'.
						$autosettle.'$'.
						Tools::getValue('BILLING_ZIP').'|'.Tools::getValue('BILLING_STREETNUMBER').'$'.
						Tools::getValue('BILLING_CO').'$'.Tools::getValue('SHIPPING_ZIP').'|'.Tools::getValue('SHIPPING_STREETNUMBER').'$'.
						Tools::getValue('SHIPPING_CO').'$'.
						Tools::getValue('TYPE_CARD');
					$blow = new BlowfishCore($realex->shared_secret, $realex->shared_secret);
					$crypt = $blow->encrypt($md);
					$md64 = base64_encode($crypt);
					?>
					<HTML>
					<HEAD>
					<TITLE><?php echo $realex->l('3D Secure verification')?></TITLE>
					<SCRIPT LANGUAGE="Javascript" >
					<!--
					function OnLoadEvent() {
					document.form.submit();
					}
					//-->
					</SCRIPT>
					</HEAD>
					<BODY onLoad="OnLoadEvent()">
					<FORM NAME="form" ACTION="<?php echo $url_redirect?>" METHOD="POST">
					<INPUT TYPE="hidden" NAME="PaReq" VALUE="<?php echo $pareq?>">
					<INPUT TYPE="hidden" NAME="TermUrl"	VALUE="<?php echo $realex->url_validation?>">
					<INPUT TYPE="hidden" NAME="MD" VALUE="<?php echo $md64?>">
					<NOSCRIPT><INPUT TYPE="submit"></NOSCRIPT>
					</FORM>
					</BODY>
					</HTML>					
				<?php exit;
				}
				else
				{
					if ($enrolled == 'N')
					{
						if ($type_card == 'VISA')
							$xm->addChild('eci', '6');
						elseif ($type_card == 'MC')
							$xm->addChild('eci', '1');
					}
					else
					{
						if ($type_card == 'VISA')
							$xm->addChild('eci', '7');
						elseif ($type_card == 'MC')
							$xm->addChild('eci', '0');
					}
					if ($enrolled == 'N' || !$realex->liability)
					{
						unset($xm->dcc);
						unset($xm->cvn);
						if (Tools::getValue('cvn'))
							$xm->addChild('cvn', Tools::getValue('cvn'));
						$xm->addChild('dcc', Tools::getValue('DCC'));
						$xm->addChild('dcc_choice', Tools::getValue('DCC_CHOICE'));
						$xm->addChild('billing_code', Tools::getValue('BILLING_ZIP').'|'.Tools::getValue('BILLING_STREETNUMBER'));
						$xm->addChild('billing_country', Tools::getValue('BILLING_CO'));
						$xm->addChild('shipping_code', Tools::getValue('SHIPPING_ZIP').'|'.Tools::getValue('SHIPPING_STREETNUMBER'));
						$xm->addChild('shipping_country', Tools::getValue('SHIPPING_CO'));
						$xm = $realex->requestRealvaultReceiptIn($xm);
						$realex->manageOrder($xm);
					}
					else {
						$xm->addChild('orderid', Tools::getValue('ORDER_ID'));
						$realex->manageOrder($xm, true, true);
					}
				}
			}
			else {
				$xm = new SimpleXMLElement('<root/>');
				$xm->addAttribute('timestamp', Tools::getValue('TIMESTAMP'));
				$xm->addChild('sha1', Tools::getValue('SHA1HASH'));
				$xm->addChild('account', Tools::getValue('ACCOUNT'));
				$xm->addChild('orderid', Tools::getValue('ORDER_ID'));
				$xm->addChild('currency', Tools::getValue('CURRENCY'));
				$xm->addChild('amount', Tools::getValue('AMOUNT'));
				$xm->addChild('cvn', Tools::getValue('cvn'));
				$xm->addChild('dcc', Tools::getValue('DCC'));
				$xm->addChild('dcc_choice', Tools::getValue('DCC_CHOICE'));
				$xm->addChild('autosettle', Tools::getValue('AUTO_SETTLE_FLAG'));
				$xm->addChild('payerref', Tools::getValue('PAYER_REF'));
				$xm->addChild('paymentmethod', Tools::getValue('PMT_REF'));
				$xm->addChild('billing_code', Tools::getValue('BILLING_ZIP').'|'.Tools::getValue('BILLING_STREETNUMBER'));
				$xm->addChild('billing_country', Tools::getValue('BILLING_CO'));
				$xm->addChild('shipping_code', Tools::getValue('SHIPPING_ZIP').'|'.Tools::getValue('SHIPPING_STREETNUMBER'));
				$xm->addChild('shipping_country', Tools::getValue('SHIPPING_CO'));
				$xm = $realex->requestRealvaultReceiptIn($xm);
				$realex->manageOrder($xm);
			}
		}
		elseif (Tools::isSubmit('RESULT'))
		{
			$xm = new SimpleXMLElement('<root/>');
			$xm->addAttribute('timestamp', Tools::getValue('TIMESTAMP'));
			$xm->addChild('result', Tools::getValue('RESULT'));
			$xm->addChild('message', Tools::getValue('MESSAGE'));
			$xm->addChild('authcode', Tools::getValue('AUTHCODE'));
			$xm->addChild('pasref', Tools::getValue('PASREF'));
			$xm->addChild('sha1hash', Tools::getValue('SHA1HASH'));
			$tss = $xm->addChild('tss');
			$tss->addChild('result', Tools::getValue('TSS'));
			$xm->addChild('eci', Tools::getValue('ECI'));
			$xm->addChild('avspostcoderesponse', Tools::getValue('AVSPOSTCODERESULT'));
			$xm->addChild('avsaddressresponse', Tools::getValue('AVSADDRESSRESULT'));
			$xm->addChild('RV', Tools::getValue('REALWALLET_CHOSEN'));
			$xm->addChild('RVSavedPayerRef', Tools::getValue('SAVED_PAYER_REF'));
			$xm->addChild('RVSavedPaymentRef', Tools::getValue('SAVED_PMT_REF'));
			$xm->addChild('RVSavedPaymentType', Tools::getValue('SAVED_PMT_TYPE'));
			$xm->addChild('RVPmtResponse', Tools::getValue('PMT_SETUP'));
			$xm->addChild('RVPmtDigits', Tools::getValue('SAVED_PMT_DIGITS'));
			$rvpmt_exp 		= Tools::getValue('SAVED_PMT_EXPDATE');
			$xm->addChild('RVPmtExpFormat', $rvpmt_exp[0].$rvpmt_exp[1].'/'.$rvpmt_exp[2].$rvpmt_exp[3]);
			$xm->addChild('account', Tools::getValue('ACCOUNT'));
			$xm->addChild('orderid', Tools::getValue('ORDER_ID'));
			$xm->addChild('currency', Tools::getValue('CURRENCY'));
			$xm->addChild('amount', Tools::getValue('AMOUNT'));
			$xm->addChild('cvn', Tools::getValue('cvn'));
			$xm->addChild('dcc', Tools::getValue('DCCCCP'));
			$xm->addChild('dcc_choice', Tools::getValue('DCCCHOICE'));
			$xm->addChild('dcc_rate', Tools::getValue('DCCAUTHRATE'));
			$xm->addChild('dcc_cardholder_amount', Tools::getValue('DCCAUTHCARDHOLDERAMOUNT'));
			$xm->addChild('dcc_cardholder_currency', Tools::getValue('DCCAUTHCARDHOLDERCURRENCY'));
			$xm->addChild('dcc_merchant_currency', Tools::getValue('DCCAUTHMERCHANTCURRENCY'));
			$xm->addChild('dcc_merchant_amount', Tools::getValue('DCCAUTHMERCHANTAMOUNT'));
			$xm->addChild('autosettle', Tools::getValue('AUTO_SETTLE_FLAG'));
			$tmp = Tools::getValue('TIMESTAMP');
			$tmp .= '.'.$realex->merchant_id;
			$tmp .= '.'.Tools::getValue('ORDER_ID');
			$tmp .= '.'.Tools::getValue('RESULT');
			$tmp .= '.'.Tools::getValue('MESSAGE');
			$tmp .= '.'.Tools::getValue('PASREF');
			$tmp .= '.'.Tools::getValue('AUTHCODE');
			$sha1hash = sha1($tmp);
			$tmp = $sha1hash.'.'.$realex->shared_secret;
			$sha1hash = sha1($tmp);
			if ($sha1hash != Tools::getValue('SHA1HASH'))
				die($this->l("hashes don't match - response not authenticated!", 'validation'));
			else
				$realex->manageOrder($xm, false);
		}
	}
}
