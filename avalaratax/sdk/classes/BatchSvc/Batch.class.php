<?php
/**
 * Batch.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class Batch {
  private $AccountId; // int
  private $BatchId; // int
  private $BatchStatusId; // string
  private $BatchTypeId; // string
  private $CompanyId; // int
  private $CreatedDate; // dateTime
  private $CreatedUserId; // int
  private $CompletedDate; // dateTime
  private $Files; // ArrayOfBatchFile
  private $ModifiedDate; // dateTime
  private $ModifiedUserId; // int
  private $Name; // string
  private $Options; // string
  private $RecordCount; // int
  private $CurrentRecord; // int
  
  function __construct()
  {
  	$this->AccountId=0;
  	$this->BatchId=0;
  	$this->CreatedUserId=0;
  	$this->ModifiedUserId=0;
  	$this->RecordCount=0;
  	$this->CurrentRecord=0;
  	
  	$this->CreatedDate=getCurrentDate();
  	$this->CompletedDate=getCurrentDate();
  	$this->ModifiedDate=getCurrentDate();
  	
  	
  	
  }

  public function setAccountId($value){$this->AccountId=$value;} // int
  public function getAccountId(){return $this->AccountId;} // int

  public function setBatchId($value){$this->BatchId=$value;} // int
  public function getBatchId(){return $this->BatchId;} // int

  public function setBatchStatusId($value){$this->BatchStatusId=$value;} // string
  public function getBatchStatusId(){return $this->BatchStatusId;} // string

  public function setBatchTypeId($value){$this->BatchTypeId=$value;} // string
  public function getBatchTypeId(){return $this->BatchTypeId;} // string

  public function setCompanyId($value){$this->CompanyId=$value;} // int
  public function getCompanyId(){return $this->CompanyId;} // int

  public function setCreatedDate($value){$this->CreatedDate=$value;} // dateTime
  public function getCreatedDate(){return $this->CreatedDate;} // dateTime

  public function setCreatedUserId($value){$this->CreatedUserId=$value;} // int
  public function getCreatedUserId(){return $this->CreatedUserId;} // int

  public function setCompletedDate($value){$this->CompletedDate=$value;} // dateTime
  public function getCompletedDate(){return $this->CompletedDate;} // dateTime

  public function setFiles($value){$this->Files=$value;} // ArrayOfBatchFile
  public function getFiles(){return $this->Files;} // ArrayOfBatchFile

  public function setModifiedDate($value){$this->ModifiedDate=$value;} // dateTime
  public function getModifiedDate(){return $this->ModifiedDate;} // dateTime

  public function setModifiedUserId($value){$this->ModifiedUserId=$value;} // int
  public function getModifiedUserId(){return $this->ModifiedUserId;} // int

  public function setName($value){$this->Name=$value;} // string
  public function getName(){return $this->Name;} // string

  public function setOptions($value){$this->Options=$value;} // string
  public function getOptions(){return $this->Options;} // string

  public function setRecordCount($value){$this->RecordCount=$value;} // int
  public function getRecordCount(){return $this->RecordCount;} // int

  public function setCurrentRecord($value){$this->CurrentRecord=$value;} // int
  public function getCurrentRecord(){return $this->CurrentRecord;} // int

}

?>
