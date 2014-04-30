<?php
/**
 * BaseResult.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
class BaseResult {
  private $TransactionId; // string
  private $ResultCode; // SeverityLevel
  private $Messages; // ArrayOfMessage

  public function setTransactionId($value){$this->TransactionId=$value;} // string
  public function getTransactionId(){return $this->TransactionId;} // string

  public function setResultCode($value){$this->ResultCode=$value;} // SeverityLevel
  public function getResultCode(){return $this->ResultCode;} // SeverityLevel

  public function setMessages($value){$this->Messages=$value;} // ArrayOfMessage
  public function getMessages(){return $this->Messages;} // ArrayOfMessage

}

?>
