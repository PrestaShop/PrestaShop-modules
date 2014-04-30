<?php
/**
 * PingResult.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class PingResult extends BaseResult
 {
  private $Version; // string

  public function setVersion($value){$this->Version=$value;} // string
  public function getVersion(){return $this->Version;} // string

  //public function getTransactionId() { return $this->TransactionId; }
/**
 * Accessor
 * @return string
 */
    //public function getResultCode() { return $this->ResultCode; }
/**
 * Accessor
 * @return array
 */
    //public function getMessages() { return EnsureIsArray($this->Messages->Message);}
  
}

?>
