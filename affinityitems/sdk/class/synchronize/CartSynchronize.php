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

class CartSynchronize extends AbstractModuleSynchronize {

	const ORDER = 2;

	public function __construct() { 
		parent::__construct(new ActionRepository());
	}

	public function getCountElementToSynchronize($clause) { 
		$countElement = 0;
		if($tmp = AEAdapter::countCart($clause)) {
			$countElement = (int)$tmp[0]['celement'];
		}
		return $countElement;
	}
	
	public function updateNumberElementSynchronized() { }

	public function syncNewElement() {
		/*
			Member Part
		*/
		$sql = AEAdapter::newMemberCartClause(parent::BULK_PACKAGE);
		$countElement = $this->getCountElementToSynchronize($sql[1]);
		$sql = implode($sql);
		if(!AELibrary::isNull($countElement)) {
			$countPage = ceil($countElement/parent::BULK_PACKAGE);
			for($cPage = 0; $cPage <= ($countPage - 1); $cPage++) {
				$content = $this->syncMemberCart($sql, 'addToCart');
				$request = new ActionRequest($content);
				if($request->post()) {
					$content = AELibrary::castArray($content);
					$this->getRepository()->insertCart($content);
				}
			}
		}
	}


	public function syncUpdateElement() {
		/*
			Member Part
		*/
		
		$sql = AEAdapter::updateMemberCartClause(parent::BULK_PACKAGE);
		$countElement = $this->getCountElementToSynchronize($sql[1]);
		$sql = implode($sql);
		if(!AELibrary::isNull($countElement)) {
			$countPage = ceil($countElement/parent::BULK_PACKAGE);
			for($cPage = 0; $cPage <= ($countPage - 1); $cPage++) {
				$content = $this->syncMemberCart($sql, 'updateCart');
				$request = new ActionRequest($content);
				if($request->post()) {
					$content = AELibrary::castArray($content);
					$this->getRepository()->updateCart($content);
				}
			}
		}
	}

	public function syncDeleteElement() { 
		/*
			Member Part
		*/

		$sql = AEAdapter::deleteMemberCartClause(parent::BULK_PACKAGE);
		$countElement = $this->getCountElementToSynchronize($sql[1]);
		$sql = implode($sql);
		if(!AELibrary::isNull($countElement)) {
			$countPage = ceil($countElement/parent::BULK_PACKAGE);
			for($cPage = 0; $cPage <= ($countPage - 1); $cPage++) {
				$content = $this->syncMemberCart($sql, 'removeFromCart');
				$request = new ActionRequest($content);				
				if($request->post()) {
					$content = AELibrary::castArray($content);
					$this->getRepository()->deleteCart($content);
				}
			}
		}
	}

	public function syncMemberCart($sql, $aecontext) {

		$aecarts = array();

		if (!$carts = AEAdapter::getCartList($sql)) {
				return array();
		}

		foreach ($carts as $cart) {
			$attributes = array();
			if($cart['id_product_attribute'] <> 0) {
					$attr = AEAdapter::getCartsProductAttributes((int)$cart['id_product_attribute']);
					foreach ($attr as $attribute) {
						array_push($attributes, $attribute['id_attribute']);
					}
			}

			$aecart = new stdClass();
			$aecart->context = $aecontext;
			$aecart->id = $cart['id_cart'];
			$aecart->addDate = $cart['date_add'];
	  		$aecart->memberId = $cart['id_customer'];
			$aecart->productAttributesId = $cart['id_product_attribute'];
	 
			$orderLine = new stdClass();
			$orderLine->productId = $cart['id_product'];
			$orderLine->attributeIds = $attributes;
			$orderLine->quantity = (int)$cart['quantity'];

			$aecart->orderLine = $orderLine;

			if(AEAdapter::isLastSync()) {
				if($group = AEAdapter::getCartGroup($cart['id_cart'])) {
					$aecart->group = $group;
				}
			}

	 		array_push($aecarts, $aecart);
		}

		if(sizeof($aecarts) > 1) {
			return $aecarts;
		} else {
			return $aecart;
		}

	}

	public function syncGuestCart($cartId) {
		/*
			Guest::addToCart 
		*/

		$sql = AEAdapter::newGuestCartClause($cartId);
		$content = $this->syncCart($sql, "addToCart");
		if(!AELibrary::isNull($content)) {
			$request = new ActionRequest($content);
			if($request->post()) {
				$content = AELibrary::castArray($content);
				$this->getRepository()->insertCart($content);
			} else {
				$this->getRepository()->insert(AELibrary::castArray($content));
				$this->getRepository()->insertCart(AELibrary::castArray($content));
			}
		}

		/*
			Guest::removeFromCart 
		*/

		$sql = AEAdapter::deleteGuestCartClause($cartId);
		$content = $this->syncCart($sql, "removeFromCart");
		if(!AELibrary::isNull($content)) {
			$request = new ActionRequest($content);
			if($request->post()) {
				$content = AELibrary::castArray($content);
				$this->getRepository()->deleteCart($content);
			} else {
				$this->getRepository()->insert(AELibrary::castArray($content));
				$this->getRepository()->deleteCart(AELibrary::castArray($content));
			}
		}
	}

	public function syncCart($sql, $aecontext) {
		$aecarts = array();
		$carts = AEAdapter::getCartList($sql);

		if(sizeof($carts) > 0) {
			foreach ($carts as $cart) {
				$attributes = array();
				if($cart['id_product_attribute'] <> 0) {
					$attr = AEAdapter::getCartsProductAttributes((int)$cart['id_product_attribute']);
					foreach ($attr as $attribute) {
						array_push($attributes, $attribute['id_attribute']);
					}
				}

				$aecart = new stdClass();
				$aecart->context = $aecontext;
				$aecart->id = $cart['id_cart'];
				$aecart->addDate = $cart['date_add'];
				
				$aecookie = AECookie::getInstance();
				if($aecookie->getCookie()->__isset('aeguest')) {
					$aecart->guestId = (String)$aecookie->getCookie()->__get('aeguest');
				}

				$aecart->productAttributesId = $cart['id_product_attribute'];

				$orderLine = new stdClass();
				$orderLine->productId = $cart['id_product'];
				$orderLine->attributeIds = $attributes;
				$orderLine->quantity = (int)$cart['quantity'];

				$aecart->orderLine = $orderLine;

				if(AEAdapter::isLastSync()) {
					$person = new AEGuest($aecart->guestId);
					if($group = $person->getGroup()) {
						$aecart->group = $group;
					}
					unset($person);
				}

				array_push($aecarts, $aecart);
			}

			if(sizeof($aecarts) > 1) {
				return $aecarts;
			} else {
				return $aecart;
			}
		}
	}
}

?>