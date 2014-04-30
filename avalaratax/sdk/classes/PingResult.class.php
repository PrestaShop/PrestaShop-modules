<?php
/**
 * PingResult.class.php
 */
 
 /**
 * Result information returned from the {@link AddressServiceSoap}'s
 * {@link AddressServiceSoap#ping} method and the {@link TaxServiceSoap}'s
 * {@link TaxServiceSoap#ping} method.
 * <b>Example:</b><br>
 * <pre>
 *  $svc = new AddressServiceSoap();
 *
 *  $result = svc->ping();
 *  $numMessages = sizeof($result->getMessages());
 *
 * </pre>
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Base
 */


class PingResult //extends BaseResult
{
/**
 * Version string of the pinged service.
 * @var string
 */
    private $Version;
    
/**
 * Method returning version string of the pinged service.
 * @return string
 */
    public function getVersion() { return $this->Version; }

// BaseResult innards - workaround a bug in SoapClient

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