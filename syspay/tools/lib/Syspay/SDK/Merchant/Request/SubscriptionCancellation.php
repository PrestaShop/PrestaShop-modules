<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Cancel a subscription
 * @see https://app.syspay.com/docs/api/merchant_subscription.html#cancelling-a-subscription
 */
class Syspay_Merchant_SubscriptionCancellationRequest extends Syspay_Merchant_Request
{
    const METHOD = 'POST';
    const PATH   = '/api/v1/merchant/subscription/%d/cancel';

    /**
     * @var integer
     */
    private $subscriptionId;

    public function __construct($subscriptionId = null)
    {
        if (null !== $subscriptionId) {
            $this->setSubscriptionId($subscriptionId);
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
        return sprintf(self::PATH, $this->subscriptionId);
    }

    /**
     * {@inheritDoc}
     */
    public function buildResponse(stdClass $response)
    {
        if (!isset($response->subscription)) {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to retrieve "subscription" data from response', $response);
        }

        $subscription = Syspay_Merchant_Entity_Subscription::buildFromResponse($response->subscription);

        return $subscription;
    }

    /**
     * Gets the value of subscriptionId.
     *
     * @return integer
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * Sets the value of subscriptionId.
     *
     * @param integer $subscriptionId the subscription id
     *
     * @return self
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;

        return $this;
    }
}
