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

abstract class AbstractModuleSynchronize {

	protected $repository;

	const BULK_PACKAGE = 300;

	public function __construct($prepository){ 
		$this->repository = $prepository;
	}

	public function getRepository() {
		return $this->repository;
	}

	public function setRepository($prepository) {
		$this->repository = $prepository;
	}

	abstract public function getCountElementToSynchronize($clause);

	abstract public function updateNumberElementSynchronized();

	public function syncElement() { 
		try {
			$this->syncNewElement();
			$this->syncUpdateElement();
			$this->syncDeleteElement();
		} catch(Exception $e) {
			AELogger::log("[ERROR]", $e->getMessage());
		}
	}

	abstract public function syncNewElement();

	abstract public function syncUpdateElement();

	abstract public function syncDeleteElement();

}

?>