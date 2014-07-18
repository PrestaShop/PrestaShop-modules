<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Get information about a subscription
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_subscription.html#get-subscription-information
 */
class Syspay_Merchant_SubscriptionInfoRequest extends Syspay_Merchant_Request
{
    const METHOD = 'GET';
    const PATH   = '/api/v1/merchant/subscription/%d';

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
     * @param integer $subscriptionId the subscriptionId
     *
     * @return self
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;

        return $this;
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

        if (isset($response->redirect) && !empty($response->redirect)) {
            $subscription->setRedirect($response->redirect);
        }

        return $subscription;
    }

}
