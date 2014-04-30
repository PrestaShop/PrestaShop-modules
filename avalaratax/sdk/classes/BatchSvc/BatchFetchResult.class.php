<?php
/**
 * BatchFetchResult.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class BatchFetchResult extends BaseResult {
  private $Batches; // ArrayOfBatch
  private $RecordCount; // int

  public function setBatches($value){$this->Batches=$value;} // ArrayOfBatch
  public function getBatches(){return $this->Batches;} // ArrayOfBatch

  public function setRecordCount($value){$this->RecordCount=$value;} // int
  public function getRecordCount(){return $this->RecordCount;} // int

}

?>
