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

/**
 * Encapsulates web service calls regarding PagSeguro payment requests
 */
class PagSeguroPaymentService {
	
	const serviceName = 'paymentService';
	
	private static function buildCheckoutRequestUrl(PagSeguroConnectionData $connectionData) {
		return $connectionData->getServiceUrl().'/?'.$connectionData->getCredentialsUrlQuery();
	}
	
	private static function buildCheckoutUrl(PagSeguroConnectionData $connectionData, $code) {
		return $connectionData->getResource('checkoutUrl')."?code=$code";
	}
	
	// createCheckoutRequest is the actual implementation of the Register method
	// This separation serves as test hook to validate the Uri
	// against the code returned by the service
	public static function createCheckoutRequest(PagSeguroCredentials $credentials, PagSeguroPaymentRequest $paymentRequest) {
		
		LogPagSeguro::info("PagSeguroPaymentService.Register(".$paymentRequest->toString().") - begin");
		
		$connectionData = new PagSeguroConnectionData($credentials, self::serviceName);
		
		try {
			
			$connection = new PagSeguroHttpConnection();
			$connection->post(
				self::buildCheckoutRequestUrl($connectionData),
				PagSeguroPaymentParser::getData($paymentRequest),
				$connectionData->getServiceTimeout(),
				$connectionData->getCharset()
			);
			
			$httpStatus = new PagSeguroHttpStatus($connection->getStatus());
			
			switch ($httpStatus->getType()) {
				
				case 'OK':
					$PaymentParserData = PagSeguroPaymentParser::readSuccessXml($connection->getResponse());
					$paymentUrl = self::buildCheckoutUrl($connectionData, $PaymentParserData->getCode());
					LogPagSeguro::info("PagSeguroPaymentService.Register(".$paymentRequest->toString().") - end {1}".$PaymentParserData->getCode());
					break;
				
				case 'BAD_REQUEST':
					$errors = PagSeguroPaymentParser::readErrors($connection->getResponse());
					$e = new PagSeguroServiceException($httpStatus, $errors);
					LogPagSeguro::error("PagSeguroPaymentService.Register(".$paymentRequest->toString().") - error ".$e->getOneLineMessage());
					throw $e;
					break;
				
				default:
					$e = new PagSeguroServiceException($httpStatus);
					LogPagSeguro::error("PagSeguroPaymentService.Register(".$paymentRequest->toString().") - error ".$e->getOneLineMessage());
					throw $e;
					break;
					
			}
			
			return ( isset($paymentUrl)? $paymentUrl : false );
			
		} catch (PagSeguroServiceException $e) {
			throw $e;
		} catch (Exception $e) {
			LogPagSeguro::error("Exception: ".$e->getMessage());
			throw $e;
		}
		
	}

}
	
?>