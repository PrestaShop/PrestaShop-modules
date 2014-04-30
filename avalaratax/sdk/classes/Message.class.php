<?php
/**
 * Message.class.php
 */

/**
 * Message class used in results and exceptions.
 * Contains status detail about call results.
 *
 * @package   Address
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 */

class Message
{
    private $Summary;
    private $Details;
    private $HelpLink;
    private $RefersTo;
    private $Severity;
    private $Source;
    private $Name;
    
    /**
     * Gets the concise summary of the message. 
     *
     * @return string
     */    
    public function getSummary() { return $this->Summary; }
    
    /**
     * Gets the details of the message. 
     *
     * @return string
     */
    public function getDetails() { return $this->Details; }
    
    /**
     *Gets the URL to help page for this message. 
     *
     * @return unknown
     */
    public function getHelpLink() { return $this->HelpLink; }
    
    /**
     * Gets the item the message refers to, if applicable. Used to indicate a missing or incorrect value. 
     *
     * @return unknown
     */
    public function getRefersTo() { return $this->RefersTo; }
    
    /**
     * Gets the Severity Level of the message. 
     *
     * @return unknown
     */
    public function getSeverity() { return $this->Severity; }
    
    /**
     * Gets the source of the message.
     *
     * @return unknown
     */
    public function getSource() { return $this->Source; }
    
    /**
     * Gets the name of the message. 
     *
     * @return unknown
     */
    public function getName() { return $this->Name; }
    
    // mutators
    public function setSummary($value) { $this->Summary = $value; return $this; }
    public function setDetails($value) { $this->Details = $value; return $this; }
    public function setHelpLink($value) { $this->HelpLink = $value; return $this; }
    public function setRefersTo($value) { $this->RefersTo = $value; return $this; }
    public function setSeverity($value) { SeverityLevel::Validate($value); $this->Severity = $value; return $this; }
    public function setSource($value) { $this->Source = $value; return $this; }
    public function setName($value) { $this->Name = $value; return $this; }
    
}

?>