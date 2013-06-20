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

/*
 * Represents a exception behavior 
 * */
class PagSeguroServiceException extends Exception{

	private $httpStatus;
	private $httpMessage;
	private $errors = Array();
	
	public function __construct(PagSeguroHttpStatus $httpStatus, Array $errors = null) {
		$this->httpStatus  = $httpStatus;
		if ($errors) {
			$this->errors = $errors;
		}
		$this->message = $this->getFormatedMessage();
	}
	
	public function getErrors($errors){
		return $this->errors;
	}		
	public function setErrors(Array $errors){
		$this->errors = errors;
	}
	
	public function getHttpStatus(){
		return $this->httpStatus;
	}		
	public function setHttpStatus(PagSeguroHttpStatus $httpStatus){
		$this->httpStatus = $httpStatus;
	}
	
	private function getHttpMessage() {
		switch($this->httpStatus->getType()){
			
			case 'BAD_REQUEST':
				$message = "BAD_REQUEST";
				break;
			
			case 'UNAUTHORIZED':
				$message = "UNAUTHORIZED";
				break;
			
			case 'FORBIDDEN':
				$message = "FORBIDDEN";
				break;
			
			case 'NOT_FOUND':
				$message = "NOT_FOUND";
				break;
			
			case 'INTERNAL_SERVER_ERROR':
				$message = "INTERNAL_SERVER_ERROR";
				break;
				
			case 'BAD_GATEWAY':
				$message = "BAD_GATEWAY";
				break;
			
			default:
				$message = "UNDEFINED";
				break;
				
		}
		return $message;
	}
	
	public function getFormatedMessage(){
		$message  = "";
		$message .= "[HTTP " . $this->httpStatus->getStatus() . "] - " . $this->getHttpMessage(). "\n";
		foreach ($this->errors as $key => $value) {
			if ($value instanceof PagSeguroError) {
				$message .= "[" .$value->getCode() . "] - " . $value->getMessage();
			}
		}
		return $message;
	}
	
	public function getOneLineMessage() {
		return str_replace("\n", " ", $this->getFormatedMessage());
	}
	
}
?>