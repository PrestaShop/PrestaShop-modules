<?php
/**
 * AvaCertSvc.class.php
 */

/**
 * AvaCertSvc class
 * This is a proxy for the Avalara AvaCert service.  It contains methods that perform remote calls
 * to the Avalara AvaCert Service.
 * <p><b>Note: This web service is only available to accounts that have enrolled in the AvaCert service.</b></p> 
 * 
 * <p>
 * <b>Example:</b>
 * <pre>
 *  $avacertService = new AvaCertSvc('Development');
 *  $result = $avacertService->ping();
 * </pre>
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert
 */
class AvaCertSvc extends AvalaraSoapClient {

  private static $classmap = array(                                    
                                    'AddCustomerRequest' => 'AddCustomerRequest',                                    
                                    'Customer' => 'AvalaraCustomer',                                    
                                    'ExemptionCertificate' => 'ExemptionCertificate',
                                    'Jurisdiction' => 'Jurisdiction',
                                    'CertificateStatus' => 'CertificateStatus',
                                    'ReviewStatus' => 'ReviewStatus',
                                    'CertificateUsage' => 'CertificateUsage',
                                    'RequestType' => 'RequestType',
                                    'AddCustomerResult' => 'AddCustomerResult',
                                    'BaseResult' => 'BaseResult',
                                    'SeverityLevel' => 'SeverityLevel',
                                    'Message' => 'AvalaraMessage',
                                    'Profile' => 'AvalaraProfile',
                                    'InitiateExemptCert' => 'InitiateExemptCert',
                                    'InitiateExemptCertRequest' => 'InitiateExemptCertRequest',
                                    'GetExemptionCertificates' => 'GetExemptionCertificates',
                                    'GetExemptionCertificatesRequest' => 'GetExemptionCertificatesRequest',
                                    'GetExemptionCertificatesResult' => 'GetExemptionCertificatesResult',
                                    'BaseRequest' => 'BaseRequest',
                                    'CommunicationMode' => 'CommunicationMode',                                    
                                    'InitiateExemptCertResult' => 'InitiateExemptCertResult',                                                                                                            
                                    'Ping' => 'Ping',                                    
                                    'PingResult' => 'PingResult',
                                    'IsAuthorized' => 'IsAuthorized',                                    
                                    'IsAuthorizedResult' => 'IsAuthorizedResult',
                                   );

	public function __construct($configurationName = 'Default')
    {
        $config = new ATConfig($configurationName);
        
        $this->client = new DynamicSoapClient   (
            $config->avacertWSDL,
            array
            (
                'location' => $config->url.$config->avacertService, 
                'trace' => $config->trace,
                'classmap' => AvaCertSvc::$classmap
            ), 
            $config
        );
    }

  /**
   * This method adds an exempt customer record to AvaCert.  This can also be done
   * within the initiateExemptCert method.  It should be used when you only wish to
   * add the customer and are not yet ready to request a certificate.  Use initiateExemptCert
   * if you wish to both add a customer and request an exempt certificate.
   *
   * <pre>
   * $customer = new Customer();
   * $customer->setCompanyCode("DEFAULT");
   * $customer->setCustomerCode("AVALARA");
   * $customer->setCustomerName("Avalara, Inc.");
   * $customer->setAddress1("435 Ericksen Ave NE");
   * $customer->setCity("Bainbridge Island");
   * $customer->setRegion("WA");
   * $customer->setPostalCode("98110");
   * $customer->setCountry("US");
   * $customer->setEmail("info@avalara.com");
   * $customer->setPhone("206-826-4900");
   * $customer->setFax("206-780-5011");
   * $customer->setCustomerType("Bill_To");
   *
   * $addCustomerRequest = new AddCustomerRequest();
   * $addCustomerRequest->setCustomer($customer);
   * 
   * $addCustomerResult= $avacertService->addCustomer($addCustomerRequest);
   * </pre> 
   *
   * @param AddCustomer $parameters
   * @return AddCustomerResponse
   */
  public function AddCustomer(AddCustomerRequest $addCustomerRequest) {
    
      return $this->client->AddCustomer(array('AddCustomerRequest' => $addCustomerRequest))->AddCustomerResult;
  }

  /**
   * This method initiates a request from AvaCert to the customer for an exemption certificate.
   * The request will be sent using the designated method (email, fax, post).
   * It creates or updates the included customer record in the process.
   *
   * <pre>
   * $customer = new Customer();
   * $customer->setCompanyCode("DEFAULT");
   * $customer->setCustomerCode("AVALARA");
   * $customer->setCustomerName("Avalara, Inc.");
   * $customer->setAddress1("435 Ericksen Ave NE");
   * $customer->setCity("Bainbridge Island");
   * $customer->setRegion("WA");
   * $customer->setPostalCode("98110");
   * $customer->setCountry("US");
   * $customer->setEmail("info@avalara.com");
   * $customer->setPhone("206-826-4900");
   * $customer->setFax("206-780-5011");
   * $customer->setCustomerType("Bill_To");
   *
   * $initiateExemptCertRequest=new InitiateExemptCertRequest();
   * $initiateExemptCertRequest->setCustomer($customer);
   * $initiateExemptCertRequest->setCommunicationMode(CommunicationMode::$Email);
   * $initiateExemptCertRequest->setCustomMessage("Thank you!");
   *
   * $initiateExemptCertResult= $avacertService->initiateExemptCert($initiateExemptCertRequest); 
   * </pre>
   * 
   * @param InitiateExemptCert $parameters
   * @return InitiateExemptCertResponse
   */
  public function InitiateExemptCert(InitiateExemptCertRequest $initiateExemptCertRequest) {    
      return $this->client->InitiateExemptCert(array('InitiateExemptCertRequest' => $initiateExemptCertRequest))->InitiateExemptCertResult;
  }
  
  /**
   * This method retrieves all certificates from vCert for a particular customer. 
   * <p>
   * If only Customer.CustomerCode and Customer.CompanyCode are set, and all other properties in InitiateExemptCertRequest.Customer are empty then the InitiateExemptCert method will not attempt to create/update the customer record in vCert. It will simply initiate a request in vCert.
   * If any additional properties on InitiateExemptCertRequest.Customer are set then the method will either create/update the customer record in vCert, and then initiate a request in vCert.
   * </p>
   *
   * <pre>
   * $getExemptionCertificatesRequest=new GetExemptionCertificatesRequest();
   * $getExemptionCertificatesRequest->setCompanyCode("Default");	        
   * $dateTime=new DateTime();	    
   * $getExemptionCertificatesRequest->setToDate(date_format($dateTime,"Y-m-d"));	        
   * $dateTime->modify("-10 day");	    
   * $getExemptionCertificatesRequest->setFromDate(date_format($dateTime,"Y-m-d"));
   * $getExemptionCertificatesRequest->setRegion("WA");
   *
   * $getExemptionCertificatesResult = $avacertService->getExemptionCertificates($getExemptionCertificatesRequest);
   * </pre>
   * 
   * @param GetExemptionCertificates $parameters
   * @return GetExemptionCertificatesResponse
   */
  public function GetExemptionCertificates(GetExemptionCertificatesRequest $getExemptionCertificatesRequest) {
  	return $this->client->GetExemptionCertificates(array('GetExemptionCertificatesRequest' => $getExemptionCertificatesRequest))->GetExemptionCertificatesResult;    
  }
  
  /**
   * Verifies connectivity to the web service and returns version information about the service. 
   *
   * @param Ping $parameters
   * @return PingResponse
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
   * <code> isAuthorized("GetTax,PostTax")</code>
   * @param IsAuthorized $parameters
   * @return IsAuthorizedResponse
   */
  public function IsAuthorized(IsAuthorized $parameters) {
    return $this->client->IsAuthorized(array('Operations' => $operations))->IsAuthorizedResult;
  }

}

?>
