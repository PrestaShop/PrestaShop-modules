<?php

/**
 * AvaCert2Soap class
 * 
 *  
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */
class AvaCert2Soap extends AvalaraSoapClient {

  static $servicePath = '/AvaCert2/AvaCert2Svc.asmx';	
  private static $classmap = array(
                                    'CustomerSave' => 'CustomerSave',
                                    'CustomerSaveRequest' => 'CustomerSaveRequest',
                                    'Customer' => 'Customer',
                                    'Certificate' => 'Certificate',
                                    'CertificateStatus' => 'CertificateStatus',
                                    'ReviewStatus' => 'ReviewStatus',
                                    'CertificateUsage' => 'CertificateUsage',
                                    'CertificateJurisdiction' => 'CertificateJurisdiction',
                                    'CustomerSaveResult' => 'CustomerSaveResult',
                                    'Profile' => 'Profile',
                                    'CertificateRequestInitiate' => 'CertificateRequestInitiate',
                                    'CertificateRequestInitiateRequest' => 'CertificateRequestInitiateRequest',
                                    'CertificateRequestInitiateResult' => 'CertificateRequestInitiateResult',
                                    'CertificateGet' => 'CertificateGet',
                                    'CertificateGetRequest' => 'CertificateGetRequest',
                                    'CommunicationMode' => 'CommunicationMode',
                                    'CertificateGetResult' => 'CertificateGetResult',
                                    'CertificateRequestGet' => 'CertificateRequestGet',
                                    'CertificateRequestGetRequest' => 'CertificateRequestGetRequest',
                                    'CertificateRequestGetResult' => 'CertificateRequestGetResult',
                                    'CertificateRequest' => 'CertificateRequest',
                                    'CertificateRequestStatus' => 'CertificateRequestStatus',
                                    'CertificateRequestStage' => 'CertificateRequestStage',
                                    'CertificateImageGet' => 'CertificateImageGet',
                                    'CertificateImageGetRequest' => 'CertificateImageGetRequest',
                                    'FormatType' => 'FormatType',
                                    'CertificateImageGetResult' => 'CertificateImageGetResult',
                                    'BaseRequest' => 'BaseRequest',                                    
                                    'RequestType' => 'RequestType',
                                    'BaseResult' => 'BaseResult',
                                    'SeverityLevel' => 'SeverityLevel',
                                    'Message' => 'Message',
                                    'Ping' => 'Ping',
                                    'PingResult' => 'PingResult',
                                    'IsAuthorized' => 'IsAuthorized',
                                    'IsAuthorizedResult' => 'IsAuthorizedResult',
                                   );

public function __construct($configurationName = 'Default')
    {
        $config = new ATConfig($configurationName);
        
        $this->client = new DynamicSoapClient   (
            $config->avacert2WSDL,
            array
            (
                'location' => $config->url.$config->avacert2Service, 
                'trace' => $config->trace,
                'classmap' => AvaCert2Soap::$classmap
            ), 
            $config
        );
    }

  /**
   * This method adds an exempt customer record to AvaCert.
   *
   * <pre>
   * $customer = new Customer();
   * $customer->setCompanyCode("DEFAULT");
   * $customer->setCustomerCode("AVALARA");
   * $customer->setBusinessName("Avalara, Inc.");
   * $customer->setAddress1("435 Ericksen Ave NE");
   * $customer->setCity("Bainbridge Island");
   * $customer->setState("WA");
   * $customer->setZip("98110");
   * $customer->setCountry("US");
   * $customer->setEmail("info@avalara.com");
   * $customer->setPhone("206-826-4900");
   * $customer->setFax("206-780-5011");
   * $customer->setType("Bill_To");
   *
   * $customerSaveRequest = new CustomerSaveRequest();
   * $customerSaveRequest->setCustomer($customer);
   * 
   * $customerSaveResult= $avacert2Service->customerSave($customerSaveRequest);
   * </pre> 
   *
   * @param CustomerSave $parameters
   * @return CustomerSaveResult
   */
  public function CustomerSave(CustomerSaveRequest $customerSaveRequest) {    
      return $this->client->CustomerSave(array('CustomerSaveRequest' => $customerSaveRequest))->CustomerSaveResult;
  }

  /**
   * This method initiates a request from AvaCert to the customer for an exemption certificate.
   * The request will be sent using the designated method (email, fax, post).
   *
   * <pre>
   * $certificateRequestInitiateRequest=new CertificateRequestInitiateRequest();
   * $certificateRequestInitiateRequest->setCompanyCode("DEFAULT");
   * $certificateRequestInitiateRequest->setCustomerCode("AVALARA");
   * $certificateRequestInitiateRequest->setCommunicationMode(CommunicationMode::$EMAIL);
   * $certificateRequestInitiateRequest->setCustomMessage("Thank you!");
   *
   * $certificateRequestInitiateResult= $avacert2Service->certificateRequestInitiate($certificateRequestInitiateRequest); 
   * </pre>
   * 
   * @param CertificateRequestInitiate $parameters
   * @return CertificateRequestInitiateResult
   */
  public function CertificateRequestInitiate(CertificateRequestInitiateRequest $certificateRequestInitiateRequest) {    
      return $this->client->CertificateRequestInitiate(array('CertificateRequestInitiateRequest' => $certificateRequestInitiateRequest))->CertificateRequestInitiateResult;
  }
  
  /**
   * This method retrieves all certificates from vCert for a particular customer. 
   * 
   * <pre>
   * $certificateGetRequest=new CertificateGetRequest();
   * $certificateGetRequest->setCompanyCode("DEFAULT");
   * $certificateGetRequest->setCustomerCode("AVALARA");
   *
   * $certificateGetResult= $avacert2Service->certificateGet($certificateGetRequest); 
   * </pre>
   * 
   * @param CertificateGet $parameters
   * @return CertificateGetResult
   */
  public function CertificateGet(CertificateGetRequest $certificateGetRequest) {
    return $this->client->CertificateGet(array('CertificateGetRequest' => $certificateGetRequest))->CertificateGetResult;
  }


  /**
   * This method retrieves all certificate requests from vCert for a particular customer. 
   * 
   * <pre>
   * $certificateRequestGetRequest=new CertificateRequestGetRequest();
   * $certificateRequestGetRequest->setCompanyCode("DEFAULT");
   * $certificateRequestGetRequest->setCustomerCode("AVALARA");
   * $certificateRequestGetRequest->setRequestStatus(CertificateRequestStatus::$OPEN);
   *
   * $certificateRequestGetResult= $avacert2Service->certificateRequestGet($certificateRequestGetRequest); 
   * </pre> 
   *
   * @param CertificateRequestGet $parameters
   * @return CertificateRequestGetResult
   */
  public function CertificateRequestGet(CertificateRequestGetRequest $certificateRequestGetRequest) {
  	return $this->client->CertificateRequestGet(array('CertificateRequestGetRequest' => $certificateRequestGetRequest))->CertificateRequestGetResult;
  }

  /**
   * This method retrieves all certificate requests from vCert for a particular customer. 
   * 
   * <pre>
   * $certificateImageGetRequest=new CertificateImageGetRequest();
   * $certificateImageGetRequest->setCompanyCode("DEFAULT");
   * $certificateImageGetRequest->setAvaCertId("CBSK");
   * $certificateImageGetRequest->setFormat(FormatType::$PNG);
   * $certificateImageGetRequest->setPageNumber(1);
   *
   * $certificateImageGetResult= $avacert2Service->certificateImageGet($certificateImageGetRequest); 
   * </pre>  
   *
   * @param CertificateImageGet $parameters
   * @return CertificateImageGetResult
   */
  public function CertificateImageGet(CertificateImageGetRequest $certificateImageGetRequest) {
  	return $this->client->CertificateImageGet(array('CertificateImageGetRequest' => $certificateImageGetRequest))->CertificateImageGetResult;
  }
  
  /**
   * Verifies connectivity to the web service and returns version information about the service. 
   *
   * @param Ping $parameters
   * @return PingResult
   */
  public function Ping($message = '') {
    return $this->client->Ping(array('Message' => $message))->PingResult;
  }

  /**
   * Checks authentication of and authorization to one or more operations on the service.
   * <p>
   * This operation allows pre-authorization checking of any or all operations.
   * It will return a comma delimited set of operation names which will be all or a subset
   * of the requested operation names.  For security, it will never return operation names
   * other than those requested, i.e. protects against phishing.
   * </p>
   * <b>Example:</b><br>
   * <code> isAuthorized("CustomerSave,CertificateRequestInitiate")</code>
   * @param IsAuthorized $parameters
   * @return IsAuthorizedResult
   */
  public function IsAuthorized(IsAuthorized $parameters) {
    return $this->client->IsAuthorized(array('Operations' => $operations))->IsAuthorizedResult;
  }

}

?>
