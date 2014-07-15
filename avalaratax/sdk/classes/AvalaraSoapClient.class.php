<?php
/**
 * AvalaraSoapClient.class.php
 */

/**
 * Abstract base class for all Avalara web service clients.
 *
 * Users should never create instances of this class.
 *
 * @abstract
 * @see AddressServiceSoap
 * @see TaxServiceSoap
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Base
 */

class AvalaraSoapClient 
{
    protected $client;

    public function __getLastRequest() { return $this->client->__getLastRequest(); }
    public function __getLastResponse() { return $this->client->__getLastResponse(); }
    public function __getLastRequestHeaders() { return $this->client->__getLastRequestHeaders(); }
    public function __getLastResponseHeaders() { return $this->client->__getLastResponseHeaders(); }

}



?>
