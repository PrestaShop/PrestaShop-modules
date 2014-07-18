<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Get information about a payment
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-payment-information
 */
class Syspay_Merchant_PaymentInfoRequest extends Syspay_Merchant_Request
{
    const METHOD = 'GET';
    const PATH   = '/api/v1/merchant/payment/%d';

    /**
     * @var integer
     */
    private $paymentId;

    public function __construct($paymentId = null)
    {
        if (null !== $paymentId) {
            $this->setPaymentId($paymentId);
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
        return sprintf(self::PATH, $this->paymentId);
    }

    /**
     * Gets the value of paymentId.
     *
     * @return integer
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Sets the value of paymentId.
     *
     * @param integer $paymentId the paymentId
     *
     * @return self
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function buildResponse(stdClass $response)
    {
        if (!isset($response->payment)) {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to retrieve "payment" data from response', $response);
        }

        $payment = Syspay_Merchant_Entity_Payment::buildFromResponse($response->payment);

        if (isset($response->redirect) && !empty($response->redirect)) {
            $payment->setRedirect($response->redirect);
        }

        return $payment;
    }

}
