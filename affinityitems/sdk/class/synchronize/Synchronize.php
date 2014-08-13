<?php
/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future. If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

class Synchronize {

	public function __construct() {
		self::setLock(1);
		self::setStartDate(time());
		AELogger::log("[INFO]", "Start synchronize [" . time() . "]");
	}

	public function __destruct() {
		self::setEndDate(time());
		self::setLock(0);
		AELogger::log("[INFO]", "End synchronize [" . time() . "]");
	}

	public function syncElement() {
		$instances = array();
		try {
			foreach(get_declared_classes() as $class){
				$pclass = new ReflectionClass($class);
				if($pclass->isSubclassOf(new ReflectionClass('AbstractModuleSynchronize'))) {
					$instance = new $class();
					$instances[(int)$instance::ORDER] = $instance;
				}
			}
			$this->launchSync($instances);
		}
		catch(Exception $e) {
			AELogger::log("[ERROR]", $e->getMessage());
		}
	}
	
	public function launchSync($instances) {
		for($i = 0; $i < sizeof($instances); $i++) {
			self::setStep($i);
			AELogger::log("[INFO]", "Synchronize step  : " . $i . " [" . time() . "]");
			$instances[$i]->syncElement();
		}
	}

	public static function getStartDate() {
		return AEAdapter::getStartDate();
	}

	public static function getEndDate() {
		return AEAdapter::getEndDate();
	}
	
	public static function getLock() {
		return AEAdapter::getLock();
	}

	public static function getStep() {
		return AEAdapter::getStep();
	}
	
	public static function setStartDate($timestamp) {
		AEAdapter::setStartDate($timestamp);
	}

	public static function setEndDate($timestamp) {
		AEAdapter::setEndDate($timestamp);
	}	

	public static function setLock($state) {
		AEAdapter::setLock($state);
	}

	public static function setStep($step) {
		AEAdapter::setStep($step);		
	}

}

?>