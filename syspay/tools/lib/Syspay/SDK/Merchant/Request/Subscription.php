<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Process a subscription
 * @see https://app.syspay.com/docs/api/merchant_subscription.html#subscribe-a-customer-to-a-plan-hosted-flow
 * @see https://app.syspay.com/docs/api/merchant_subscription.html#subscribe-a-customer-to-a-plan-server-2-server-flow
 */
class Syspay_Merchant_SubscriptionRequest extends Syspay_Merchant_Request
{
    const FLOW_API     = 'API';
    const FLOW_BUYER   = 'BUYER';
    const FLOW_SELLER  = 'SELLER';

    const METHOD = 'POST';
    const PATH   = '/api/v1/merchant/subscription';

    /**
     * @var string
     */
    private $flow;

    /**
     * @var Syspay_Merchant_Entity_Subscription
     */
    private $subscription;

    /**
     * @var Syspay_Merchant_Entity_Customer
     */
    private $customer;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $threatMetrixSessionId;

    /**
     * @var Syspay_Merchant_Entity_Creditcard
     */
    private $creditcard;

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
        return self::PATH;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $data = array();
        $data['flow'] = $this->flow;
        $data['subscription'] = $this->subscription->toArray();

        if (false === empty($this->customer)) {
            $data['customer'] = $this->customer->toArray();
        }

        if (false === empty($this->paymentMethod)) {
            $data['method'] = $this->paymentMethod;
        }

        if (false == empty($this->threatMetrixSessionId)) {
            $data['threatmetrix_session_id'] = $this->threatMetrixSessionId;
        }

        if (false === empty($this->creditcard)) {
            $data['creditcard'] = $this->creditcard->toArray();
        }

        return $data;
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


    /**
     * Gets the value of subscription.
     *
     * @return Syspay_Merchant_Entity_Subscription
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Sets the value of subscription.
     *
     * @param Syspay_Merchant_Entity_Subscription $subscription the subscription
     *
     * @return self
     */
    public function setSubscription(Syspay_Merchant_Entity_Subscription $subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * Gets the value of flow.
     *
     * @return string
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * Sets the value of flow.
     *
     * @param string $flow the flow
     *
     * @return self
     */
    public function setFlow($flow)
    {
        $this->flow = $flow;

        return $this;
    }

    /**
     * Gets the value of customer.
     *
     * @return Syspay_Merchant_Entity_Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Sets the value of customer.
     *
     * @param Syspay_Merchant_Entity_Customer $customer the customer
     *
     * @return self
     */
    public function setCustomer(Syspay_Merchant_Entity_Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Gets the value of paymentMethod.
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Sets the value of paymentMethod.
     *
     * @param string $paymentMethod the payment method
     *
     * @return self
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Gets the value of threatMetrixSessionId.
     *
     * @return string
     */
    public function getThreatMetrixSessionId()
    {
        return $this->threatMetrixSessionId;
    }

    /**
     * Sets the value of threatMetrixSessionId.
     *
     * @param string $threatMetrixSessionId the threat metrix session id
     *
     * @return self
     */
    public function setThreatMetrixSessionId($threatMetrixSessionId)
    {
        $this->threatMetrixSessionId = $threatMetrixSessionId;

        return $this;
    }

    /**
     * Gets the value of creditcard.
     *
     * @return Syspay_Merchant_Entity_Creditcard
     */
    public function getCreditcard()
    {
        return $this->creditcard;
    }

    /**
     * Sets the value of creditcard.
     *
     * @param Syspay_Merchant_Entity_Creditcard $creditcard the creditcard
     *
     * @return self
     */
    public function setCreditcard(Syspay_Merchant_Entity_Creditcard $creditcard)
    {
        $this->creditcard = $creditcard;

        return $this;
    }

}
