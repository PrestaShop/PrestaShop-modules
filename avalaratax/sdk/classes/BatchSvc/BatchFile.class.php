<?php
/**
 * BatchFile.class.php
 */

/**
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class BatchFile {
  private $BatchFileId; // int
  private $BatchId; // int
  private $Content; // base64Binary
  private $ContentType; // string
  private $Ext; // string
  private $FilePath; // string
  private $Name; // string
  private $Size; // int
  private $ErrorCount; // int
  
  function __construct()
  {
  	$this->BatchFileId=0;
  	$this->BatchId=0;
  	$this->ErrorCount=0;
  }

  public function setBatchFileId($value){$this->BatchFileId=$value;} // int
  public function getBatchFileId(){return $this->BatchFileId;} // int

  public function setBatchId($value){$this->BatchId=$value;} // int
  public function getBatchId(){return $this->BatchId;} // int

  public function setContent($value){$this->Content=$value;} // base64Binary
  public function getContent(){return $this->Content;} // base64Binary

  public function setContentType($value){$this->ContentType=$value;} // string
  public function getContentType(){return $this->ContentType;} // string

  public function setExt($value){$this->Ext=$value;} // string
  public function getExt(){return $this->Ext;} // string

  public function setFilePath($value){$this->FilePath=$value;} // string
  public function getFilePath(){return $this->FilePath;} // string

  public function setName($value){$this->Name=$value;} // string
  public function getName(){return $this->Name;} // string

  public function setSize($value){$this->Size=$value;} // int
  public function getSize(){return $this->Size;} // int

  public function setErrorCount($value){$this->ErrorCount=$value;} // int
  public function getErrorCount(){return $this->ErrorCount;} // int

}

?>
