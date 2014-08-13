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

class ABTesting {

	public static $types = array('A','B','Z'); 

	public function __construct() {}

	public static function init() {
		$instance = new AffinityItems();
		$person = $instance->getPerson();
		if(!$person instanceof stdClass){
			if(AELibrary::isEmpty($person->getGroup())) {
				$person->setGroup();
			}
		}	
	}

	public static function getGuestGroup($person) {		
		$aecookie = AECookie::getInstance();
		try {
			self::filter();
			if($aecookie->getCookie()->__isset('aegroup')) {
				return $aecookie->getCookie()->__get('aegroup');
			}
		} catch(Exception $e) {
			AELogger::log("[ERROR]", $e->getMessage());
		}
	}

	public static function setGuestGroup() {
		$aecookie = AECookie::getInstance();
		try {
			$rnd = (0+lcg_value()*(abs(1)));
			$group = ($rnd < (AEAdapter::getAbTestingPercentage()/100)) ? "A" : "B";
			$aecookie->getCookie()->__set('aegroup', $group);
			$aecookie->getCookie()->write();
		} catch(Exception $e) {
			AELogger::log("[ERROR]", $e->getMessage());
		}
	}

	public static function filter() {
		$aecookie = AECookie::getInstance();
		try {
			if(!AELibrary::isEmpty(AEAdapter::getBlackListIp())) {
				if(in_array(Tools::getRemoteAddr(), unserialize(AEAdapter::getBlackListIp()))) {
					$aecookie->getCookie()->__set('aegroup', 'Z');
					$aecookie->getCookie()->write();
				}
			}
		} catch (Exception $e) {
			AELogger::log("[ERROR]", $e->getMessage());
		}
	}

	public static function forceGroup($group) {
		$aecookie = AECookie::getInstance();		
		$groups = array('A','B','Z');
		if(in_array($group, $groups)) {
			AELogger::log("[INFO]", "Forcing group : " . $group);
			$aecookie->getCookie()->__set('aegroup', $group);
			$aecookie->getCookie()->write();
		}
	}

}

?>