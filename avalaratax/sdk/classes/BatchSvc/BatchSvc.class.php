<?php
/**
 * BatchSvc.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */


class BatchSvc extends SoapClient {

  	private $client;
	private static $classmap = array(
                                    'BatchFetch' => 'BatchFetch',
                                    'FetchRequest' => 'FetchRequest',
                                    'BatchFetchResponse' => 'BatchFetchResponse',
                                    'BatchFetchResult' => 'BatchFetchResult',
                                    'BaseResult' => 'BaseResult',
                                    'SeverityLevel' => 'SeverityLevel',
                                    'Message' => 'Message',
                                    'Batch' => 'Batch',
                                    'BatchFile' => 'BatchFile',
                                    'Profile' => 'Profile',
                                    'BatchSave' => 'BatchSave',
                                    'BatchSaveResponse' => 'BatchSaveResponse',
                                    'BatchSaveResult' => 'BatchSaveResult',
                                    'AuditMessage' => 'AuditMessage',
                                    'BatchDelete' => 'BatchDelete',
                                    'DeleteRequest' => 'DeleteRequest',
                                    'FilterRequest' => 'FilterRequest',
                                    'BatchDeleteResponse' => 'BatchDeleteResponse',
                                    'DeleteResult' => 'DeleteResult',
                                    'FilterResult' => 'FilterResult',
                                    'BatchProcess' => 'BatchProcess',
                                    'BatchProcessRequest' => 'BatchProcessRequest',
                                    'BatchProcessResponse' => 'BatchProcessResponse',
                                    'BatchProcessResult' => 'BatchProcessResult',
                                    'BatchFileFetch' => 'BatchFileFetch',
                                    'BatchFileFetchResponse' => 'BatchFileFetchResponse',
                                    'BatchFileFetchResult' => 'BatchFileFetchResult',
                                    'BatchFileSave' => 'BatchFileSave',
                                    'BatchFileSaveResponse' => 'BatchFileSaveResponse',
                                    'BatchFileSaveResult' => 'BatchFileSaveResult',
                                    'BatchFileDelete' => 'BatchFileDelete',
                                    'BatchFileDeleteResponse' => 'BatchFileDeleteResponse',
                                    'Ping' => 'Ping',
                                    'PingResponse' => 'PingResponse',
                                    'PingResult' => 'PingResult',
                                    'IsAuthorized' => 'IsAuthorized',
                                    'IsAuthorizedResponse' => 'IsAuthorizedResponse',
                                    'IsAuthorizedResult' => 'IsAuthorizedResult',
                                   );

	public function __construct($configurationName = 'Default')
    {
        $config = new ATConfig($configurationName);
        
        $this->client = new DynamicSoapClient   (
            $config->batchWSDL,
            array
            (
                'location' => $config->url.$config->batchService, 
                'trace' => $config->trace,
                'classmap' => BatchSvc::$classmap
            ), 
            $config
        );
    }    

  /**
   * Fetches one or more Batch 
   *
   * @param BatchFetch $parameters
   * @return BatchFetchResponse
   */  
    public function BatchFetch(&$fetchRequest) {    
      
      return $this->client->BatchFetch(array('FetchRequest' => $fetchRequest))->getBatchFetchResult();
  }

  /**
   * Saves a Batch entry 
   *
   * @param BatchSave $parameters
   * @return BatchSaveResponse
   */
  public function BatchSave(&$batch) {
   	
  	return $this->client->BatchSave(array('Batch' => $batch))->getBatchSaveResult();
  	
  }

  /**
   * Deletes one or more Batches 
   *
   * @param BatchDelete $parameters
   * @return BatchDeleteResponse
   */
  public function BatchDelete(&$deleteRequest) {
     	
  	return $this->client->BatchDelete(array('DeleteRequest' => $deleteRequest))->getBatchDeleteResult();
  	
  }

  /**
   * Processes one or more Batches 
   *
   * @param BatchProcess $parameters
   * @return BatchProcessResponse
   */
  public function BatchProcess(&$batchProcessRequest) {
    
  	return $this->client->BatchProcess(array('BatchProcessRequest' => $batchProcessRequest))->getBatchProcessResult();
  	
  }

  /**
   * Fetches one or more BatchFiles 
   *
   * @param BatchFileFetch $parameters
   * @return BatchFileFetchResponse
   */
  public function BatchFileFetch(&$fetchRequest) {
  	
  	return $this->client->BatchFileFetch(array('FetchRequest' => $fetchRequest))->getBatchFileFetchResult();
    
  }

  /**
   * Saves a Batch File 
   *
   * @param BatchFileSave $parameters
   * @return BatchFileSaveResponse
   */
  public function BatchFileSave(&$batchFile) {   
  	
  	return $this->client->BatchFileSave(array('BatchFile' => $batchFile))->getBatchFileSaveResult();
  	
  }

  /**
   * Deletes one or more BatchFiles 
   *
   * @param BatchFileDelete $parameters
   * @return BatchFileDeleteResponse
   */
  public function BatchFileDelete(&$deleteRequest) {
    
  	return $this->client->BatchFileDelete(array('DeleteRequest' => $deleteRequest))->getBatchFileDeleteResult();
  	
  }

  /**
   * Tests connectivity and version of the service 
   *
   * @param Ping $parameters
   * @return PingResponse
   */
  public function Ping($message = '') {    
      return $this->client->Ping(array('Message' => $message))->getPingResult();
  }

  /**
   * Checks authentication and authorization to one or more operations on the service. 
   *
   * @param IsAuthorized $parameters
   * @return IsAuthorizedResponse
   */
public function IsAuthorized($operations)
    {
        return $this->client->IsAuthorized(array('Operations' => $operations))->getIsAuthorizedResult();
    }

}

?>
