<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Get information about a refund
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-refund-information
 */
class Syspay_Merchant_RefundInfoRequest extends Syspay_Merchant_Request
{
    const METHOD = 'GET';
    const PATH   = '/api/v1/merchant/refund/%d';

    /**
     * @var integer
     */
    private $refundId;

    public function __construct($refundId = null)
    {
        if (null !== $refundId) {
            $this->setRefundId($refundId);
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
        return sprintf(self::PATH, $this->refundId);
    }

    /**
     * Gets the value of refundId.
     *
     * @return integer
     */
    public function getRefundId()
    {
        return $this->refundId;
    }

    /**
     * Sets the value of refundId.
     *
     * @param integer $refundId the refundId
     *
     * @return self
     */
    public function setRefundId($refundId)
    {
        $this->refundId = $refundId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function buildResponse(stdClass $response)
    {
        if (!isset($response->refund)) {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to retrieve "refund" data from response', $response);
        }

        $refund = Syspay_Merchant_Entity_Refund::buildFromResponse($response->refund);

        return $refund;
    }

}
