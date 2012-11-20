<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/wexpay.php');

$wexpay = new Wexpay();

/*
$logfile     = dirname(__FILE__)."/log/logs.txt";
$fp = fopen($logfile, "a");
fwrite($fp, "Ref_cart : ".Tools::getValue('ref_order')."\n");
fwrite($fp, "amount : ".number_format((Tools::getValue('amount')/100),2)."\n");
*/


$liste_IP = array( "213.218.137.231" , "213.218.137.226", "91.188.68.69", "91.188.72.66", "91.188.72.67" );
					
   
   $remote_addr = $_SERVER['REMOTE_ADDR'];
   if( in_array($remote_addr, $liste_IP) )
   {
   		fwrite($fp, "Enregistrement de la commande \n");
		// Retrieve var wexpay post data
		$amount = number_format((Tools::getValue('amount')/100),2);
		$idCart  = intval(Tools::getValue('ref_order'));
		
		// load customer 
		$cart = new Cart($idCart);
		$customer = new Customer((int)$cart->id_customer);
		if(Validate::isLoadedObject($cart))
		{
			// Validate order
			$wexpay->validateOrder($cart->id, _PS_OS_PAYMENT_, $amount, $wexpay->displayName, Tools::getValue('ref_wexpay'),'','',false,$customer->secure_key);
			
			$order = new Order($wexpay->currentOrder);
			if(Validate::isLoadedObject($cart))
			{
				fwrite($fp, "END TRANSACTION SAVE TRANSACTION-------------------------------------------\n");
				fclose($fp);
				//die('CODEREPONSE=1');
			}
		}
		else
		{
			fwrite($fp, "ERROR TRANSACTION NO VALID CART-------------------------------------------\n");
			fclose($fp);
			die('CODEREPONSE=0');
		}
   
   } else {
		fwrite($fp, "ERROR TRANSACTION NO VALID SERVER RESPONSE-------------------------------------------\n");
		fclose($fp);
		die('CODEREPONSE=0');
   }

