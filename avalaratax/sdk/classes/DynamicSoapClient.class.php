<?php
/**
 * DynamicSoapClient.class.php
 */
 
/**
 * Private implementation class for all Avalara web service clients.
 *
 * Users should never need to create instances of this class.  This class provides the underlying implementation
 * for instances of {@link AvalaraSoapClient} and it's subclasses.
 *
 * @see AvalaraSoapClient
 * @see AddressServiceSoap
 * @see TaxServiceSoap
 *  
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Base
 */
 
class DynamicSoapClient extends SoapClient
{
    private $config;
    public function __construct($wsdl,$options,&$config)
    {
        parent::__construct($wsdl,$options);
        $this->config = $config; 
    }

	public function __call($n,$args)
	{
        $profileHeader = new SoapHeader('http://avatax.avalara.com/services','Profile',new SoapVar($this->profileXML(),XSD_ANYXML));
        $securityHeader = new SoapHeader('http://avatax.avalara.com/services','Security',new SoapVar($this->securityXML(),XSD_ANYXML));
        	$result = $this->__soapCall($n,$args,NULL,array($securityHeader,$profileHeader));
        return $result;
	}
    
    private function securityXML()
    {
        return 
            '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" SOAP-ENV:mustUnderstand="1">'.
                '<wsse:UsernameToken>'.
                    '<wsse:Username>'.$this->config->account.'</wsse:Username>'.
                    '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->config->license.'</wsse:Password>'.
                    //<wsu:Created xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">2005-11-22T06:33:26.203Z</wsu:Created>
                '</wsse:UsernameToken>'.
            '</wsse:Security>';
    }
    
    private function profileXML()
    {
        return 
            '<Profile xmlns="http://avatax.avalara.com/services" SOAP-ENV:actor="http://schemas.xmlsoap.org/soap/actor/next" SOAP-ENV:mustUnderstand="0">'.
                '<Name>'.$this->config->name.'</Name>'.
            	'<Client>'.$this->config->client.'</Client>'.
                '<Adapter>'.$this->config->adapter.'</Adapter>'.        		
            '</Profile>';
    }
    
}

?>
