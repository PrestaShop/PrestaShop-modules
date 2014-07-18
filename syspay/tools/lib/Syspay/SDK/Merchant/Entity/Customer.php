<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * A customer object
 */
class Syspay_Merchant_Entity_Customer extends Syspay_Merchant_Entity
{
    const TYPE = 'customer';

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var string
     */
    protected $ip;

    /**
     * Gets the value of email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the value of email.
     *
     * @param string $email the email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets the value of language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Sets the value of language.
     *
     * @param string $language the language
     *
     * @return self
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Gets the value of ip.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Sets the value of ip.
     *
     * @param string $ip the ip
     *
     * @return self
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Build a customer entity based on a json-decoded customer stdClass
     *
     * @param  stdClass $response The customer data
     * @return Syspay_Merchant_Entity_Customer The customer object
     */
    public static function buildFromResponse(stdClass $response)
    {
        $customer = new self();
        $customer->setEmail(isset($response->email)?$response->email:null);
        $customer->setLanguage(isset($response->language)?$response->language:null);
        $customer->setIp(isset($response->ip)?$response->ip:null);
        return $customer;
    }
}
