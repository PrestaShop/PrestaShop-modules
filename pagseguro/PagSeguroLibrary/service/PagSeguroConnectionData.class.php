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

class PagSeguroConnectionData{
	
	private $serviceName;
	private $credentials;
	private $resources;
	private $environment;
	private $webserviceUrl;
	private $servicePath;
	private $serviceTimeout;
	private $charset;
	
	public function __construct(PagSeguroCredentials $credentials, $serviceName) {
	
		$this->credentials = $credentials;
		$this->serviceName = $serviceName;
	
		$this->setEnvironment(PagSeguroConfig::getEnvironment());
		$this->setWebserviceUrl(PagSeguroResources::getWebserviceUrl($this->getEnvironment()));
		$this->setCharset(PagSeguroConfig::getApplicationCharset());
	
		$this->resources = PagSeguroResources::getData($this->serviceName);
		if (isset($this->resources['servicePath'])) {
			$this->setServicePath($this->resources['servicePath']);
		}
		if (isset($this->resources['serviceTimeout'])) {
			$this->setServiceTimeout($this->resources['serviceTimeout']);
		}
	
	}
	
	public function getCredentials() {
		return $this->credentials;
	}
	public function setCredentials(PagSeguroCredentials $credentials) {
		$this->credentials = $credentials;
	}
	
	public function getCredentialsUrlQuery() {
		return http_build_query($this->credentials->getAttributesMap(), '', '&');
	}
	
	public function getEnvironment(){
		return $this->environment;
	}
	public function setEnvironment($environment){
		$this->environment = $environment;
	}
	
	public function getWebserviceUrl(){
		return $this->webserviceUrl;
	}
	public function setWebserviceUrl($webserviceUrl){
		$this->webserviceUrl = $webserviceUrl;
	}
	
	public function getServicePath(){
		return $this->servicePath;
	}
	public function setServicePath($servicePath){
		$this->servicePath = $servicePath;
	}
	
	public function getServiceTimeout(){
		return $this->serviceTimeout;
	}
	public function setServiceTimeout($serviceTimeout){
		$this->serviceTimeout = $serviceTimeout;
	}
	
	public function getServiceUrl(){
		return $this->getWebserviceUrl().$this->getServicePath();
	}
	
	public function getResource($resource) {
		return $this->resources[$resource];
	}
	
	public function getCharset(){
		return $this->charset;
	}
	public function setCharset($charset){
		$this->charset = $charset;
	}	
	
}

?>