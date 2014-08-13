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

class ActionSynchronize extends AbstractModuleSynchronize {

	const ORDER = 4;

	public function __construct() {
		parent::__construct(new ActionRepository());
	}

	public function getCountElementToSynchronize($clause) {
		unset($clause);
		$countElement = 0;
		if($countElement = AEAdapter::countAction()) {
			$countElement = (int)$countElement;
		}
		return $countElement;
	}

	public function updateNumberElementSynchronized() { }

	public function syncNewElement() {
		$clause = '';
		$countElement = $this->getCountElementToSynchronize($clause);
		if(!AELibrary::isNull($countElement)) {
			$countPage = ceil($countElement/parent::BULK_PACKAGE);
			for($cPage = 0; $cPage <= ($countPage - 1); $cPage++) {
				$content = $this->syncAction();
				$request = new ActionRequest($content);
				if($request->post()) {
					$content = AELibrary::castArray($content);
					$this->getRepository()->delete($content);
				}
			}
		}
	}

	public function syncUpdateElement() { /* There is not update for actions */ }

	public function syncDeleteElement() { /* There is not delete for actions */ }

	public function syncAction() {

		$aeactionList = array();

		$actionList = AEAdapter::getActionList(parent::BULK_PACKAGE);

		foreach ($actionList as $action) {
			$action = unserialize($action["action"]);
			if(count($actionList) > 1){
				array_push($aeactionList, $action);
			}
		}

		if(!empty($aeactionList)) {
			return $aeactionList;
		}
		else {
			return $action;
		}
	}

	/*public function syncMemberAction($memberId) {
		$content = array();
		$actionList = AEAdapter::getMemberActionList($memberId);
		if(!empty($actionList)) {
			foreach ($actionList as $action) {
				array_push($content, unserialize($action["action"]));
			}
			$request = new ActionRequest($content);
			if($request->post()) {
				$this->getRepository()->delete($content);
			}
		}
	}*/

	public function syncGuestAction($guestId) {
		$content = array();
		$actionList = AEAdapter::getGuestActionList($guestId);
		if(!empty($actionList)) {
			foreach ($actionList as $action) {
				array_push($content, unserialize($action["action"]));
			}
			$request = new ActionRequest($content);
			if($request->post()) {
				$this->getRepository()->delete($content);
			}
		}
	}

}

?>