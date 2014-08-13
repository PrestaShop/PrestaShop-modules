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

class OrderSynchronize extends AbstractModuleSynchronize {

	const ORDER = 3;

	public function __construct() { 
		parent::__construct(new ActionRepository());
	}

	public function getCountElementToSynchronize($clause) {
		unset($clause);
		$countElement = 0;
		if($tmp = AEAdapter::countOrder()) {
			$countElement = (int)$tmp[0]['corder'];
		}
		return $countElement;
	}
	
	public function updateNumberElementSynchronized() { }

	public function syncNewElement() {
		$countOrder = $this->getCountElementToSynchronize('');
		if(!AELibrary::isNull($countOrder)) {
			$countPage = ceil($countOrder/parent::BULK_PACKAGE);
			for($cPage = 0; $cPage <= ($countPage - 1); $cPage++) {
				$content = $this->syncOrder();
				$request = new ActionRequest($content);
				if($request->post()) {
					$content = AELibrary::castArray($content);
					$this->getRepository()->insertOrder($content);
				}
			}
		}
	}

	public function syncUpdateElement() { /* There is not update for orders */ }

	public function syncDeleteElement() { /* There is not delete for orders */ }

	public function syncOrder() {
			
		$aeorders = array();

		if (!$orders = AEAdapter::getOrderList(parent::BULK_PACKAGE)) {
			return array();
		}

		foreach ($orders as $order) {

			$orderLines = $this->getOrderLines($order['id_order']);

			$aeorder = new stdClass();
			$aeorder->id = $order['id_order'];
			$aeorder->addDate = $order['date_add'];
			$aeorder->updateDate = $order['date_upd'];
			$aeorder->memberId = $order['id_customer'];
			$aeorder->amount = $order['total_paid_tax_excl'];
			$aeorder->context = "order";
			$aeorder->orderLines = $orderLines;

			if(AEAdapter::isLastSync()) {
				/*
				v1 :
				$person = new AEMember($aeorder->memberId);
				if($group = $person->getGroup()) {
					$aeorder->group = $group;
				}
				unset($person);*/
				
				/*
				v2 :
				$person = new stdClass();
				$person->personId = $order['id_customer'];
				$group = AEAdapter::getMemberGroup($person);
				if(!AELibrary::isNull($group)) {
					$aeorder->group = $group;
				}*/

				/*
				v3 :
				*/
				if($group = AEAdapter::getCartGroup($order['id_cart'])) {
					$aeorder->group = $group;
				}
			}
			
			array_push($aeorders, $aeorder);

		}

		if(sizeof($aeorders) > 1) {
			return $aeorders;
		} else {
			return $aeorder;
		}
	}

	public function getOrderLines($orderId) {
		$orderLines = array();

		if (!$lines = AEAdapter::getOrderLines($orderId)) {
			return array();
		}

		foreach ($lines as $line) {
			$attributes = array();
			if($line['product_attribute_id'] <> 0) {
				$attr = AEAdapter::getOrdersProductAttributes((int)$line['product_attribute_id']);
				foreach ($attr as $attribute) {
					array_push($attributes, $attribute['id_attribute']);
				}
			}

			$orderLine = new stdClass();
			$orderLine->productId = $line['product_id'];
			$orderLine->attributeIds = $attributes;
			$orderLine->quantity = $line['product_quantity'];
			array_push($orderLines, $orderLine);
		}

		return $orderLines;
	}


}

?>