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

class ActionRepository extends AbstractRepository {

	public function __construct() { 
		parent::__construct();
	}

	public function insert($content) {
		try {
			foreach ($content as $action) {
				AEAdapter::insertAction($action);
			}
		} catch(Exception $e) {
			AELogger::log("[ERROR]", $e->getMessage());
		}
	}

	public function update($content) { 
		unset($content);
	}

	public function delete($content) {
		try {
			foreach ($content as $action) {
				AEAdapter::deleteAction($action);
			} 
		} catch(Exception $e) {
			AELogger::log("[ERROR]", $e->getMessage());
		}
	}	

	public function insertOrder($content) {
		try {
			foreach ($content as $order) {
				AEAdapter::insertOrder($order);
			}
		} catch(Exception $e) {
			AELogger::log("[ERROR]", $e->getMessage());
		}
	}

	public function insertCart($content) {
		try {
			foreach ($content as $cart) {
				AEAdapter::insertCart($cart);
			}
		} catch(Exception $e) {
			AELogger::log("[ERROR]", $e->getMessage());
		}
	}

	public function updateCart($content) {
		try {
			foreach ($content as $cart) {
				AEAdapter::updateCart($cart);
			}
		} catch(Exception $e) {
			AELogger::log("[ERROR]", $e->getMessage());
		}
	}

	public function deleteCart($content) {
		try {
			foreach ($content as $cart) {
				AEAdapter::deleteCart($cart);
			}
		} catch(Exception $e) {
			AELogger::log("[ERROR]", $e->getMessage());
		}
	}

}

?>