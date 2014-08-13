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

abstract class AbstractRequest {
	
	protected $path;

	protected $content; 

	protected $curl;

	public function __construct($ppath , $pcontent) {
       	$this->path = $ppath;
        $this->content = $pcontent;
        $this->curl = new Curl(false);
	}

	public function getPath() {
		return $this->path;
	}

	public function setPath($ppath) {
		$this->path = $ppath;
	}

	public function getContent() {
		return $this->content;
	}

	public function setContent($pcontent) {
		$this->content = $pcontent;
	}

	public function getCurl() {
		return $this->curl;
	}

	public function setCurl($pcurl) {
		$this->curl = $pcurl;
	}

	public function post() {
		if($response = $this->curl->post($this->path, $this->content)) {
			return $response;
		}
		return false;
	}

	public function put() {
		if($response = $this->curl->put($this->path, $this->content)) {
			return $response;
		}
		return false;
	}

	public function delete() {
		if($response = $this->curl->delete($this->path, $this->content)) {
			return $response;
		}
		return false;
	}	

	public function get() {
		if($response = $this->curl->get($this->path)) {
			return $response;
		}
		return false;
	}

	public function enableReturnErrors() {
		$this->setCurl(new Curl(true));
	}
	
}

?>