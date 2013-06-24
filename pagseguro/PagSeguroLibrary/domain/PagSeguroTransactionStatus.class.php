<?php if (!defined('PAGSEGURO_LIBRARY')) { die('No direct script access allowed'); }
/*
************************************************************************
Copyright [2011] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

/**
 * Defines a list of known transaction statuses.
 * This class is not an enum to enable the introduction of new shipping types
 * without breaking this version of the library.
 */	
class PagSeguroTransactionStatus {
	
	private static $statusList = array(
		'INITIATED' => 0,
		'WAITING_PAYMENT' => 1,
		'IN_ANALYSIS' => 2,
		'PAID' => 3,
		'AVAILABLE' => 4,
		'IN_DISPUTE' => 5,
		'REFUNDED' => 6,
		'CANCELLED' => 7
	);
	
	/**
	 * the value of the transaction status
	 * Example: 3
	 */
	private $value;
	
	public function __construct($value = null){
		if ($value) {
			$this->value = $value;
		}
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function setByType($type) {
		if (isset(self::$statusList[$type])) {
			$this->value = self::$statusList[$type];
		} else {
			throw new Exception("undefined index $type");
		}
	}
	
	/**
	 * @return the status value.
	 */
	public function getValue(){
		return $this->value;
	}
	
	/**
	 * @param value
	 * @return the transaction status corresponding to the informed status value
	 */
	public function getTypeFromValue($value = null) {
		$value = ($value == null ? $this->value : $value);
		return array_search($this->value, self::$statusList);
	}

	/**
	 * Get status list
	 * @return array
	 */
	public static function getStatusList(){
		return self::$statusList;
	}
}

?>