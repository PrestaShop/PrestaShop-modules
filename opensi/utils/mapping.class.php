<?php
/*
 * OpenSi Connect for Prestashop
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Speedinfo SARL
 * @copyright 2003-2012 Speedinfo SARL
 * @contact contact@speedinfo.fr
 * @url http://www.speedinfo.fr
 *
 */

class Mapping {
	
	/*
	 * Mapping between civility PS<>openSi
	 * 
	 * Prestashop civility
	 * 1 => male
	 * 2 => female
	 * 3 => no gender
	 * 
	 * OpenSi gender
	 * 0 => no gender
	 * 1 => Mr.
	 * 2 => Mrs.
	 * 3 => Miss
	 * 
	 * 
	 * @param String $idCivility  is prestashop civility
	 */
	public static function psCivility2OsiCivility($idCivility) {
		if($idCivility == 1) {
			return 1;
		} else if($idCivility == 2) {
			return 2;
		} else {
			return 0;
		}
	}


	/*
	 * Mapping between state openSi<>PS
	 * 
	 * ---- OpenSi states ----
	 * Order states :
	 * 	N = not valid
	 *  T = in order (validated)
	 *  A = cancel
	 *  C = finished
	 *  
	 * Logistic states :
	 *  T = not delivered
	 *  E = delivered
	 *  
	 * Payment states :
	 *  N => not payed
	 *  P => partial payed
	 *  T => totaly payed
	 * 
	 * 
	 * ---- PS states ----
	 * payement accepted
	 * on preparation
	 * delivered
	 * canceled
	 * 
	 * 
	 * ---- mapping ----
	 * State[T] + Log[T]		=> on preparation
	 * State[T] + Log[E]		=> on delivery
	 * State[A]					=> canceled 
	 * 
	 * @param $logisticState [T; E]
	 * @param $paymentState [N; P; T]
	 * @param $state [N; T; A; C]
	 */
	public static function osiState2PsStateId($logisticState, $paymentState, $state){
		$logisticState = strtoupper($logisticState);
		$paymentState = strtoupper($paymentState);
		$state = strtoupper($state);

		if($state == 'A'){
			return GlobalConfig::getStateIdCanceled();
		} else if($state == 'T' && $logisticState == 'E'){
			return GlobalConfig::getStateIdOnDelivery();
		} else if($state == 'T' && $logisticState == 'T'){
			return GlobalConfig::getStateIdOnPreparation();	
		} else if($state == 'C' && $logisticState == 'E'){
			return GlobalConfig::getStateIdOnDelivery();   
        }
	}

}