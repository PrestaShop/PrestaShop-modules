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

class Recommendation {

	public $aecontext;

	public $pscontext;

	public $stack;

	public $render;

	public $langId;

	public $actionSynchronize;

	public $actionRepository;

	public function __construct($paecontext, $ppscontext, $pstack, $prender) {
		$this->aecontext = $paecontext;
		$this->pscontext = $ppscontext;
		$this->stack = $pstack;
		$this->render = $prender;
		$this->langId = $ppscontext->language->id;
		$this->actionSynchronize = new ActionSynchronize();
		$this->actionRepository = new ActionRepository();
	}

	public function getRecommendation() {
		$products = array();
		$productPool = array();
		$select = '';
		$tax = '';

		$instance = new AffinityItems();
		$person = $instance->getPerson();

		if($person instanceof stdClass) {
			return array();
		} else if($person instanceof AEGuest) {
			$this->aecontext->guestId = $person->personId;
		}

		if($group = $person->getGroup()) {
			$this->aecontext->group = $group;
		}

		$person->syncAction();
		$this->aecontext->person = $person;
		$request = new RecommendationRequest($this->aecontext);
		
		if(is_object($productPool = $request->post())) {
			$productPool = $productPool->recommend;
		} else {
			if($this->stack) {
				$this->actionRepository->insert(AELibrary::castArray($this->aecontext));
			}
		}

		$select = AEAdapter::getRecommendationSelect();
		$tax = AEAdapter::getRecommendationTax();

		if(!empty($productPool) && $this->render) {
			$products = AEAdapter::renderRecommendation($select, $tax, $productPool, $this->langId);
		}
		else if(!empty($productPool) && !$this->render) {
			$products = $productPool;
		}

		return $products;
	}

}

?>