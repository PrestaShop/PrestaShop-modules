<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Handle Redirections
 */
class Syspay_Merchant_Redirect
{
    protected $secrets = array();
    protected $skipAuthCheck;

    /**
     * Build an Redirect handler
     * @param array   $secrets       An array where each key is your merchant login and the value is the related passphrase
     * @param boolean $skipAuthCheck Skip the checksum validation
     */
    public function __construct(array $secrets, $skipAuthCheck = false)
    {
        $this->secrets       = $secrets;
        $this->skipAuthCheck = $skipAuthCheck;
    }

    /**
     * Return the payment decoded from the redirection parameters
     * @param  array $source An array that contains a 'result', 'merchant', and 'checksum' parameters. Typically this can be $_GET or $_REQUEST.
     * @return Syspay_Merchant_Entity_Payment The decoded payment
     * @throws Syspay_Merchant_RedirectException If something went wrong while parsing the request
     */
    public function getResult(array $source)
    {
        $result   = isset($source['result'])?$source['result']:null;
        $merchant = isset($source['merchant'])?$source['merchant']:null;
        $checksum = isset($source['checksum'])?$source['checksum']:null;

        if (!$this->skipAuthCheck) {
            $this->checkChecksum($result, $merchant, $checksum);
        }

        $result = base64_decode($result);
        if ($result === false) {
            throw new Syspay_Merchant_RedirectException('Unable to decode the result parameter',
                                                            Syspay_Merchant_RedirectException::CODE_INVALID_CONTENT);
        }

        $result = Tools::jsonDecode($result);
        if ($result === null || empty($result->payment)) {
            throw new Syspay_Merchant_RedirectException('Unable to decode the result parameter',
                                                            Syspay_Merchant_RedirectException::CODE_INVALID_CONTENT);
        }
        return Syspay_Merchant_Entity_Payment::buildFromResponse($result->payment);
    }

    /**
     * Validate the request's checksum
     * @throws Syspay_Merchant_RedirectException If the checksum didn't validate
     */
    private function checkChecksum($result, $merchant, $checksum)
    {
        if (empty($merchant) || empty($checksum) || empty($result)) {
            throw new Syspay_Merchant_RedirectException('Missing parameter',
                                                            Syspay_Merchant_RedirectException::CODE_MISSING_PARAM);
        }

        if (empty($this->secrets[$merchant])) {
            throw new Syspay_Merchant_RedirectException('Unknown merchant: ' . $merchant,
                                                            Syspay_Merchant_RedirectException::CODE_UNKNOWN_MERCHANT);
        }

        if (!Syspay_Merchant_Utils::checkChecksum($result, $this->secrets[$merchant], $checksum)) {
            throw new Syspay_Merchant_RedirectException('Invalid checksum', Syspay_Merchant_RedirectException::CODE_INVALID_CHECKSUM);
        }
    }
}
