<?php
/**
 * FetchRequest.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class FetchRequest {
  private $Fields; // string
  private $Filters; // string
  private $Sort; // string
  private $MaxCount; // int
  private $PageIndex; // int
  private $PageSize; // int
  private $RecordCount; // int
  
  function __construct()
  {
  	$this->MaxCount=0;
  	$this->PageIndex=0;
  	$this->PageSize=0;
  	$this->RecordCount=0;
  }

  public function setFields($value){$this->Fields=$value;} // string
  public function getFields(){return $this->Fields;} // string

  public function setFilters($value){$this->Filters=$value;} // string
  public function getFilters(){return $this->Filters;} // string

  public function setSort($value){$this->Sort=$value;} // string
  public function getSort(){return $this->Sort;} // string

  public function setMaxCount($value){$this->MaxCount=$value;} // int
  public function getMaxCount(){return $this->MaxCount;} // int

  public function setPageIndex($value){$this->PageIndex=$value;} // int
  public function getPageIndex(){return $this->PageIndex;} // int

  public function setPageSize($value){$this->PageSize=$value;} // int
  public function getPageSize(){return $this->PageSize;} // int

  public function setRecordCount($value){$this->RecordCount=$value;} // int
  public function getRecordCount(){return $this->RecordCount;} // int

}

?>
