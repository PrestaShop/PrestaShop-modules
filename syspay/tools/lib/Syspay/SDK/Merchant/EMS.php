<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Handle EMS (Event Messaging System) callbacks
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_ems.html
 */
class Syspay_Merchant_EMS
{
    protected $secrets = array();
    protected $skipAuthCheck;
    protected $headers = array();
    protected $content;

    /**
     * Build an EMS handler
     * @param array   $secrets       An array where each key is your merchant login and the value is the related passphrase
     * @param boolean $skipAuthCheck Skip the checksum validation
     */
    public function __construct(array $secrets, $skipAuthCheck = false)
    {
        $this->secrets       = $secrets;
        $this->skipAuthCheck = $skipAuthCheck;

        $this->headers['content-type'] = isset($_SERVER['CONTENT_TYPE'])?$_SERVER['CONTENT_TYPE']:'application/x-www-form-urlencoded';
        $this->headers['x-merchant']   = isset($_SERVER['HTTP_X_MERCHANT'])?$_SERVER['HTTP_X_MERCHANT']:null;
        $this->headers['x-checksum']   = isset($_SERVER['HTTP_X_CHECKSUM'])?$_SERVER['HTTP_X_CHECKSUM']:null;

        $this->content = Tools::file_get_contents('php://input');
    }

    /**
     * Return the entity linked to the EMS call received
     * @return mixed One of the Syspay_Merchant_Entity classes depending on the event received
     * @throws Syspay_Merchant_EMSException If something went wrong while parsing the request
     */
    public function getEvent()
    {
        if (!$this->skipAuthCheck) {
            $this->checkChecksum();
        }

        $content = $this->getContent();

        if (!isset($content->type)) {
            throw new Syspay_Merchant_EMSException('Unable to get event type',
                                                        Syspay_Merchant_EMSException::CODE_INVALID_CONTENT);
        }

        if (!isset($content->data)) {
            throw new Syspay_Merchant_EMSException('Unable to get data from content',
                                                        Syspay_Merchant_EMSException::CODE_INVALID_CONTENT);
        }

        switch ($content->type) {
            case 'payment':
                if (!isset($content->data->payment)) {
                    throw new Syspay_Merchant_EMSException('Payment event received with no payment data',
                                                                Syspay_Merchant_EMSException::CODE_INVALID_CONTENT);
                }
                return Syspay_Merchant_Entity_Payment::buildFromResponse($content->data->payment);
            case 'refund':
                if (!isset($content->data->refund)) {
                    throw new Syspay_Merchant_EMSException('Refund event received with no refund data',
                                                                Syspay_Merchant_EMSException::CODE_INVALID_CONTENT);
                }
                return Syspay_Merchant_Entity_Refund::buildFromResponse($content->data->refund);
            case 'chargeback':
                if (!isset($content->data->chargeback)) {
                    throw new Syspay_Merchant_EMSException('Chargeback event received with no chargeback data',
                                                                Syspay_Merchant_EMSException::CODE_INVALID_CONTENT);
                }
                return Syspay_Merchant_Entity_Chargeback::buildFromResponse($content->data->chargeback);
            case 'billing_agreement':
                if (!isset($content->data->billing_agreement)) {
                    throw new Syspay_Merchant_EMSException('Billing agreement event received with no billing_agreement data',
                                                                Syspay_Merchant_EMSException::CODE_INVALID_CONTENT);
                }
                return Syspay_Merchant_Entity_BillingAgreement::buildFromResponse($content->data->billing_agreement);
            case 'subscription':
                if (!isset($content->data->subscription)) {
                    throw new Syspay_Merchant_EMSException('Subscription event received with no subscription data',
                                                                Syspay_Merchant_EMSException::CODE_INVALID_CONTENT);
                }
                return Syspay_Merchant_Entity_Subscription::buildFromResponse($content->data->subscription);
            default:
                throw new Syspay_Merchant_EMSException('Unknown type: ' . $content->type,
                                                            Syspay_Merchant_EMSException::CODE_INVALID_CONTENT);
        }
    }

    /**
     * Validate the header's checksum
     * @throws Syspay_Merchant_EMSException If the checksum didn't validate
     */
    private function checkChecksum()
    {
        if (empty($this->headers['x-merchant'])) {
            throw new Syspay_Merchant_EMSException('Missing x-merchant header',
                                                        Syspay_Merchant_EMSException::CODE_MISSING_HEADER);
        }

        if (empty($this->headers['x-checksum'])) {
            throw new Syspay_Merchant_EMSException('Missing x-checksum header',
                                                        Syspay_Merchant_EMSException::CODE_MISSING_HEADER);
        }

        if (!isset($this->secrets[$this->headers['x-merchant']])) {
            throw new Syspay_Merchant_EMSException('Unknown merchant: ' . $this->headers['x-merchant'],
                                                        Syspay_Merchant_EMSException::CODE_UNKNOWN_MERCHANT);
        }

        if (!Syspay_Merchant_Utils::checkChecksum($this->content, $this->secrets[$this->headers['x-merchant']],
                                                    $this->headers['x-checksum'])) {
            throw new Syspay_Merchant_EMSException('Invalid checksum', Syspay_Merchant_EMSException::CODE_INVALID_CHECKSUM);
        }
    }

    /**
     * Get the request's content
     * @return stdClass
     * @throws Syspay_Merchant_EMSException If the request body could not be parsed
     */
    private function getContent()
    {
        switch ($this->headers['content-type']) {
            case 'application/json':
                $content = Tools::jsonDecode($this->content);
                if (false === $content) {
                    throw new Syspay_Merchant_EMSException('Unable to parse request body, invalid json',
                                                                Syspay_Merchant_EMSException::CODE_INVALID_CONTENT);
                }
                return $content;
            case 'application/x-www-form-urlencoded':
            default:
                return self::toObject($_POST);
        }
    }

    /**
     * Convert an array or a scalar to a stdClass object
     * @param  mixed    $a The input value
     * @return stdClass Its object representation
     */
    private static function toObject($a) {
        if (is_array($a)) {
            return (object) array_map(array('Syspay_Merchant_EMS', 'toObject'), $a);
        }
        return $a;
    }
}
