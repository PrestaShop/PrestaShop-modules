<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Get information about a chargeback
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-chargeback-information
 */
class Syspay_Merchant_ChargebackInfoRequest extends Syspay_Merchant_Request
{
    const METHOD = 'GET';
    const PATH   = '/api/v1/merchant/chargeback/%d';

    /**
     * @var integer
     */
    private $chargebackId;

    public function __construct($chargebackId = null)
    {
        if (null !== $chargebackId) {
            $this->setChargebackId($chargebackId);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return self::METHOD;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return sprintf(self::PATH, $this->chargebackId);
    }

    /**
     * Gets the value of chargebackId.
     *
     * @return integer
     */
    public function getChargebackId()
    {
        return $this->chargebackId;
    }

    /**
     * Sets the value of chargebackId.
     *
     * @param integer $chargebackId the chargebackId
     *
     * @return self
     */
    public function setChargebackId($chargebackId)
    {
        $this->chargebackId = $chargebackId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function buildResponse(stdClass $response)
    {
        if (!isset($response->chargeback)) {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to retrieve "chargeback" data from response', $response);
        }

        $chargeback = Syspay_Merchant_Entity_Chargeback::buildFromResponse($response->chargeback);

        return $chargeback;
    }

}
