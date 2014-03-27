<?php
/*
*  @author Coccinet <web@coccinet.com>
*  @copyright  2007-2013 Coccinet
*/
class RealexRedirectPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	private function getInfosForm($account = false)
	{
		$infos 					= array();
		$realex 				= new RealexRedirect();
		$cart					= $this->context->cart;
		$id_customer			= $cart->id_customer;
		$customer 				= new Customer((int)$id_customer);
		$infos['customer']		= $customer;
		$infos['iso_currency']	= $this->context->currency->iso_code;
		$date 					= new DateTime();
		$infos['timestamp'] 	= $date->format('YmdHis');
		$infos['order_id'] 		= $cart->id.'-'.$infos['timestamp'];
		$infos['settlement']	= ($realex->settlement == 'auto')?1:0;
		if ($account)
		{
			$sql = 'SELECT dcc_realex_subaccount FROM `'._DB_PREFIX_.'realex_subaccount` WHERE name_realex_subaccount = "'.pSQL($account).'"';
			$result = Db::getInstance()->getRow($sql);
			$infos['settlement']	= ($result['dcc_realex_subaccount'] || $realex->settlement == 'auto')?1:0;
		}
		if (!$customer->is_guest)
		{
			$sql 					= 'SELECT `id_realex_payerref`,`refuser_realex` FROM `'._DB_PREFIX_.'realex_payerref` WHERE `id_user_realex` = '.$id_customer;
			$payer_ref 				= Db::getInstance()->getRow($sql);
			$infos['payer_exists'] 	= (!empty($payer_ref['refuser_realex']))?1:0;
			$infos['ref_payer'] 	= (!empty($payer_ref['refuser_realex']))?$payer_ref['refuser_realex']:$id_customer.$infos['timestamp'];
			$infos['id_realex_payerref'] 	= $payer_ref['id_realex_payerref'];
		}
		$billing_adresse 				= new Address((int)$cart->id_address_invoice);
		$infos['billing_streetumber'] 	= $this->parseInt($billing_adresse->address1);
		$infos['billing_co'] 			= Country::getIsoById($billing_adresse->id_country);
		$infos['billing_postcode'] 		= $this->parseInt($billing_adresse->postcode);
		$shipping_adresse				= new Address((int)$cart->id_address_delivery);
		$infos['shipping_streetumber'] 	= $this->parseInt($shipping_adresse->address1);
		$infos['shipping_co'] 			= Country::getIsoById($shipping_adresse->id_country);
		$infos['shipping_postcode'] 	= $this->parseInt($shipping_adresse->postcode);
		$infos['cart']					= $cart;

		if ($realex->realvault == '1' && !$customer->is_guest)
		{
			$times = $infos['timestamp'];
			$chaine = $times.'.'.$realex->merchant_id.'.'.$infos['order_id'].'.'.$realex->getAmountFormat($cart->getOrderTotal(true, Cart::BOTH));
			$chaine .= '.'.$infos['iso_currency'].'.'.$infos['ref_payer'].'.'.$cart->id.$infos['timestamp'];
		}
		else
		{
			$chaine = $infos['timestamp'].'.'.$realex->merchant_id.'.'.$infos['order_id'];
			$chaine .= '.'.$realex->getAmountFormat($cart->getOrderTotal(true, Cart::BOTH)).'.'.$infos['iso_currency'];
		}
		$sha1_temp_new 	= sha1($chaine);
		$infos['sha1_new']		= sha1($sha1_temp_new.'.'.$realex->shared_secret);
		return $infos;
	}

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$link = $this->context->link;
		$realex 					= new RealexRedirect();
		$this->display_column_left 	= false;
		parent::initContent();
		if (!$this->context->customer->isLogged() && !$this->context->customer->is_guest)
			Tools::redirect('index.php?controller=order');
		$infos 						= $this->getInfosForm();
		$currency 					= $this->context->currency;
		extract($infos, EXTR_OVERWRITE);
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');
		// ---------------- REALVAULT
		$inputs_payer 		= "<input type='hidden' name='PAYER_EXIST' value='".$payer_exists."' />";
		$inputs_payer 		.= "<input type='hidden' name='PAYER_REF' value='".$ref_payer."' />";
		$inputs_pmt_registered = '';
		if ($payer_exists)
		{
			$sql 				= 'SELECT `refpayment_realex`,
			`paymentname_realex`,
			`type_card_realex`,
			rs.`name_realex_subaccount`,
			`threeds_realex_subaccount`,
			`dcc_realex_subaccount`,
			`dcc_choice_realex_subaccount`
			FROM `'._DB_PREFIX_.'realex_paymentref`
			JOIN `'._DB_PREFIX_.'realex_rel_card` rc ON `type_card_realex`=`realex_card_name`
			JOIN `'._DB_PREFIX_.'realex_subaccount` rs ON rs.`id_realex_subaccount`=rc.`id_realex_subaccount`
			WHERE `id_realex_payerref` = "'.pSQL($id_realex_payerref).'"';
			$pmt_refs 			= Db::getInstance()->ExecuteS($sql);
			if (count($pmt_refs) > 0)
			{
				foreach ($pmt_refs as $pmt_ref)
				{
					$inputs_pmt_registered .= "<form method='post' action='".$link->getModuleLink('realexredirect', 'validation', array(), true)."'>";
					$inputs_pmt_registered .= "<input type='hidden' name='PMT_REF' value='$pmt_ref[refpayment_realex]' />";
					//SHA1
					$tmp = $timestamp.'.'.$realex->merchant_id.'.'.$order_id;
					$tmp .= '.'.$realex->getAmountFormat($cart->getOrderTotal(true, Cart::BOTH)).'.'.$iso_currency.'.'.$ref_payer;
					$sha1_temp 				= sha1($tmp);
					$sha1					= sha1($sha1_temp.'.'.$realex->shared_secret);
					$inputs_pmt_registered .= "<input type='HIDDEN' name='SHA1HASH' value='$sha1'/>";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='CURRENCY' value='".$iso_currency."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='MERCHANT_ID' value='".$realex->merchant_id."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='ACCOUNT' value='".$pmt_ref['name_realex_subaccount']."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='ORDER_ID' value='".$order_id."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='AMOUNT' value='".$realex->getAmountFormat($cart->getOrderTotal(true, Cart::BOTH))."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='TIMESTAMP' value='".$timestamp."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='AUTO_SETTLE_FLAG' value='".$settlement."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='BILLING_ZIP' value='".$billing_postcode."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='BILLING_STREETNUMBER' value='".$billing_streetumber."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='BILLING_CO' value='".$billing_co."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='SHIPPING_ZIP' value='".$shipping_postcode."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='SHIPPING_STREETNUMBER' value='".$shipping_streetumber."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='SHIPPING_CO' value='".$shipping_co."' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='RETURN_TSS' value='1' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='DCC' value='$pmt_ref[dcc_realex_subaccount]' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='DCC_CHOICE' value='$pmt_ref[dcc_choice_realex_subaccount]' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='THREEDS' value='$pmt_ref[threeds_realex_subaccount]' />";
					$inputs_pmt_registered .= "<input type='HIDDEN' name='TYPE_CARD' value='$pmt_ref[type_card_realex]' />";
					$inputs_pmt_registered .= $inputs_payer;
					if ($pmt_ref['type_card_realex'] == 'MC')
						$type_card = 'MasterCard';
					elseif ($pmt_ref['type_card_realex'] == 'AMEX')
						$type_card = 'American Express';
					else
						$type_card = Tools::ucfirst(Tools::strtolower($pmt_ref['type_card_realex']));
					$inputs_pmt_registered	.= "<div class='fleft'>$pmt_ref[paymentname_realex] / $type_card<br/>";
					if ($realex->cvn)
						$inputs_pmt_registered	.= $realex->l('Security Code','payment')." : <input type='text' style='width:40px' name='cvn'  />";
					$secure_link = $link->getModuleLink('realexredirect', "payment?reg=$pmt_ref[refpayment_realex]&token=".$this->context->cart->secure_key, array(), true);
					$inputs_pmt_registered	.= "<br/><a href='".$secure_link."' class='delete' onclick='return(confirm(\"".$realex->bout_suppr."\"))'>";
					$inputs_pmt_registered	.= 'x '.$realex->l('Delete').'</a></div>';
					$inputs_pmt_registered	.= "<p class='cart_navigation'>";
					$inputs_pmt_registered	.= "<input type='submit' name='submit_registered' value='".$realex->bout_valide."' class='exclusive_large' />";
					$inputs_pmt_registered	.= "</p><div class='clear'><br/></div>";
					$inputs_pmt_registered .= '</form>';
				}
			}
		}
		$inputs_pmt_new	= "<p class='cart_navigation'><input type='submit' name='submit' value='".$realex->bout_valide."' class='exclusive_large' /></p>";
		// ---------------- VARIABLES TPL
		$this->context->smarty->assign(array(
			'nbProducts' 	=> $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'order_id' 		=> $order_id,
			'currencies' 	=> $this->module->getCurrency((int)$cart->id_currency),
			'curr'			=> $currency->iso_code,
			'total' 		=> $cart->getOrderTotal(true, Cart::BOTH),
			'amount' 		=> $realex->getAmountFormat($cart->getOrderTotal(true, Cart::BOTH)),
			'this_path' 	=> $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
			'selectAccount' => $realex->getSelectAccount(),
			'payer_exists' 	=> $payer_exists,
			'realvault'		=> $realex->realvault,
			'input_registered' => $inputs_pmt_registered,
			'input_new' => $inputs_pmt_new,
			'submit_new' => $this->context->link->getModuleLink('realexredirect', 'payment', array('token'=>$this->context->cart->secure_key ), true )
		));
		$this->setTemplate('payment_execution.tpl');
	}

	/**
	* Return only digits from a string
	* @param string $string
	* @return string
	*/
	private function parseInt($string)
	{
		$string = str_replace(' ', '', $string);
		if (preg_match('/(\d+)/', $string, $array))
			return $array[1];
		else
			return 0;
	}
	public function postProcess()
	{
		$link = $this->context->link;
		$token 		= Tools::getValue('token');
		$realex 	= new RealexRedirect();
		// DELETE A STORED CARD
		if (Tools::isSubmit('reg') && $token == $this->context->cart->secure_key)
		{
			$reg = (int)Tools::getValue('reg');
			$url = 'https://epage.payandshop.com/epage-remote-plugins.cgi';
			//CHECK CUSTOMER
			$id_customer = (int)$this->context->cookie->id_customer;
			$sql = 'SELECT py.refuser_realex, py.id_user_realex,pm.refpayment_realex FROM '._DB_PREFIX_.'realex_payerref py
			JOIN '._DB_PREFIX_.'realex_paymentref pm ON py.id_realex_payerref = pm.id_realex_payerref
			WHERE pm.refpayment_realex = "'.$reg.'"';
			$result = Db::getInstance()->getRow($sql);
			if ($result['id_user_realex'] == $id_customer)
			{
				$realex 		= new RealexRedirect();
				$date 			= new DateTime();
				$timestamp 		= $date->format('YmdHis');
				$sha1_temp_new = sha1($timestamp.'.'.$realex->merchant_id.'.'.$result['refuser_realex'].'.'.$result['refpayment_realex']);
				$sha1_new		= sha1($sha1_temp_new.'.'.$realex->shared_secret);
				$xml_delete = '
				<request timestamp="'.$timestamp.'" type="card-cancel-card">
					<merchantid>'.$realex->merchant_id.'</merchantid>
					<card>
						<ref>'.$result['refpayment_realex'].'</ref>
						<payerref>'.$result['refuser_realex'].'</payerref>
					</card>
					<sha1hash>'.$sha1_new.'</sha1hash>
				</request>';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_USERAGENT, 'payandshop.com php version 0.9');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_delete);
				$response = curl_exec ($ch);
				var_dump($response);
				curl_close ($ch);
				$xm = simplexml_load_string($response);
				$result_rep					= (string)$xm->result;
				$merchantid_rep				= (string)$xm->merchantid;
				$sha1_rep					= (string)$xm->sha1hash;
				$chaine = $timestamp.'.'.$merchantid_rep.'.'.$result['refuser_realex'].'.'.$result['refpayment_realex'];
				$sha1_temp_rep = sha1($timestamp.'.'.$merchantid_rep.'.'.$result['refuser_realex'].'.'.$result['refpayment_realex']);
				$sha1_rep		= sha1($sha1_temp_rep.'.'.$realex->shared_secret);
				if (($result_rep == '00' || $result_rep == '501') && $sha1_rep == $sha1_new)
					Db::getInstance()->delete('realex_paymentref', 'refpayment_realex = "'.$reg.'"', 1);
			}
			Tools::redirect($link->getModuleLink('realexredirect', 'payment', array(), true));
		}
		elseif ((Tools::isSubmit('ACCOUNT')) && $token == $this->context->cart->secure_key)
		{
			$realex 		= new RealexRedirect();
			$account 		= Tools::getValue('ACCOUNT');
			$infos 			= $this->getInfosForm($account);
			extract($infos, EXTR_OVERWRITE);

			?>
			<HTML>
			<HEAD>
			<SCRIPT LANGUAGE="Javascript" >
			<!--
			function OnLoadEvent() {
			document.form.submit();
			}
			//-->
			</SCRIPT>
			</HEAD>
			<BODY onLoad="OnLoadEvent()">
			<FORM NAME="form" ACTION="https://epage.payandshop.com/epage.cgi" METHOD="POST" class="form">
			<input type="HIDDEN" value="<?php echo $account ?>" name="ACCOUNT">
			<input type="HIDDEN" value="<?php echo $sha1_new ?>" name="SHA1HASH">
			<?php if (!$customer->is_guest)
			{
				echo '<input type="hidden" value="'.$cart->id.$timestamp.'" name="PMT_REF">';
				echo '<input type="hidden" value="'.$payer_exists.'" name="PAYER_EXIST">';
				echo '<input type="hidden" value="'.$ref_payer.'" name="PAYER_REF">';
}
			?>
			<input type="HIDDEN" value="<?php echo $iso_currency ?>" name="CURRENCY">			
			<input type="HIDDEN" value="<?php echo $realex->merchant_id ?>" name="MERCHANT_ID">
			<input type="HIDDEN" value="<?php echo $order_id ?>" name="ORDER_ID">
			<input type="HIDDEN" value="<?php echo $realex->getAmountFormat($cart->getOrderTotal(true, Cart::BOTH)) ?>" name="AMOUNT">
			<input type="HIDDEN" value="<?php echo $timestamp ?>" name="TIMESTAMP">
			<input type="HIDDEN" value="<?php echo $billing_postcode.'|'.$billing_streetumber ?>" name="BILLING_CODE">
			<input type="HIDDEN" value="<?php echo $billing_co ?>" name="BILLING_CO">
			<input type="HIDDEN" value="<?php echo $shipping_postcode.'|'.$shipping_streetumber ?>" name="SHIPPING_CODE">
			<input type="HIDDEN" value="<?php echo $shipping_co ?>" name="SHIPPING_CO">
			<input type="HIDDEN" value="<?php echo $settlement ?>" name="AUTO_SETTLE_FLAG">
			<input type="HIDDEN" value="1" name="RETURN_TSS">
			<?php if ($realex->realvault == '1' && !$customer->is_guest)
				echo '<input type="HIDDEN" value="1" name="OFFER_SAVE_CARD">';
			else
				echo '<input type="HIDDEN" value="0" name="OFFER_SAVE_CARD">';
			?>
			<NOSCRIPT><INPUT TYPE="submit" name="btn"></NOSCRIPT>
			</FORM>
			</BODY>
			</HTML>
		<?php exit;
		}
		//RETURN ERROR IN CASE OF CVN IS REQUIRED AND MISSING
		if (Tools::isSubmit('error') && Tools::isSubmit('error') == 'cvn')
		{
			$this->context->smarty->assign(array(
				'error' 	=> $realex->l('Please check your security code','payment')
			));
		}
	}
}
