<?php
/** 
 * BaseResult.class.php
 */
 
 /**
 * The base class for result objects that return a ResultCode and Messages collection -- There is no reason for clients to create these.
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Base
 */
 
  
class BaseResult
{

//@author:swetal
//Removed declarations of variable as it was creating problem due to bug in SoapClient

/**
 * A unique Transaction ID identifying a specific request/response set.
 * @return string
 */
    public function getTransactionId() { return $this->TransactionId; }
/**
 * Indicates whether operation was successfully completed or not.
 * @return string
 */
    public function getResultCode() { return $this->ResultCode; }
/**
 * Accessor
 * @return array
 */
    public function getMessages() { return EnsureIsArray($this->Messages->Message); }

}

?>