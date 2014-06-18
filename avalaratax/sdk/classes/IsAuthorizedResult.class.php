<?php
/**
 * IsAuthorizedResult.class.php
 */

/**
 * Result information returned from the AddressSvc's
 * {@link AddressServiceSoap#isAuthorized} method and the TaxSvc's
 * {@link TaxServiceSoap#isAuthorized}
 * method.
 * <p><b>Example:</b><br>
 * <pre>
 *  $port = new AddressServiceSoap();
 *
 *  $result = port->ping("");
 *  $numMessages = sizeof($result->Messages);
 *  print('Ping Result # of messages is '.$numMessages);
 * </pre>
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Address
 */

class IsAuthorizedResult //extends BaseResult
{
    private $Operations;
    private $Expires;
    
    /**
     * Authorized operations for the user.
     *
     * @return string
     */
    public function getOperations() { return $this->Operations; }
    
    /**
     * Indicates the subscription expiration date in yyyy-mm-dd format 
     *
     * @return date
     */
    public function getExpires() { return $this->Expires; }
    
    public function setOperations($value) { $this->Operations = $value; return $this; }
    public function setExpires($value) { $this->Expires = $value; return $this; }
	
	//BaseResult innards - workaround for SoapClient bug
	/**
 * @var string
 */
    private $TransactionId;
/**
 * @var string must be one of the values defined in {@link SeverityLevel}.
 */
    private $ResultCode = 'Success';
/**
 * @var array of Message.
 */
    private $Messages = array();

/**
 * Accessor
 * @return string
 */
    public function getTransactionId() { return $this->TransactionId; }
/**
 * Accessor
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