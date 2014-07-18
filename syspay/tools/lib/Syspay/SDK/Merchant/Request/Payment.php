<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Process a payment
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#hosted-payment-request
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#server-to-server-credit-card-payment
 */
class Syspay_Merchant_PaymentRequest extends Syspay_Merchant_Request
{
    const FLOW_API     = 'API';
    const FLOW_BUYER   = 'BUYER';
    const FLOW_SELLER  = 'SELLER';

    const MODE_BOTH     = 'BOTH';
    const MODE_ONLINE   = 'ONLINE';
    const MODE_TERMINAL = 'TERMINAL';

    const METHOD_CREDITCARD  = 'CREDITCARD';
    const METHOD_PAYSAFECARD = 'PAYSAFECARD';

    const METHOD = 'POST';
    const PATH   = '/api/v1/merchant/payment';

    /**
     * @var string
     */
    private $flow;
    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $paymentMethod;


    /**
     * @var string
     */
    private $threatMetrixSessionId;

    /**
     * @var boolean
     */
    private $billingAgreement = false;

    /**
     * @var string
     */
    private $emsUrl;

    /**
     * @var string
     */
    private $redirectUrl;

    /**
     * @var string
     */
    private $website;

    /**
     * @var string
     */
    private $agent;

    /**
     * @var Syspay_Merchant_Entity_Payment
     */
    private $payment;

    /**
     * @var Syspay_Merchant_Entity_Customer
     */
    private $customer;

    /**
     * @var Syspay_Merchant_Entity_Creditcard
     */
    private $creditcard;

    public function __construct($flow)
    {
        if (!in_array($flow, array(self::FLOW_API, self::FLOW_BUYER, self::FLOW_SELLER))) {
            throw new InvalidArgumentException('Invalid flow: ' . $flow);
        }

        $this->flow = $flow;
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
        return self::PATH;
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

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $data = array();
        $data['flow'] = $this->flow;

        if (false === empty($this->billingAgreement)) {
            $data['billing_agreement'] = $this->billingAgreement?1:0;
        }

        if (false === empty($this->mode)) {
            $data['mode'] = $this->mode;
        }

        if (false == empty($this->threatMetrixSessionId)) {
            $data['threatmetrix_session_id'] = $this->threatMetrixSessionId;
        }

        if (false === empty($this->paymentMethod)) {
            $data['method'] = $this->paymentMethod;
        }

        if (false === empty($this->website)) {
            $data['website'] = $this->website;
        }

        if (false === empty($this->agent)) {
            $data['agent'] = $this->agent;
        }


        if (false === empty($this->redirectUrl)) {
            $data['redirect_url'] = $this->redirectUrl;
        }

        if (false === empty($this->emsUrl)) {
            $data['ems_url'] = $this->emsUrl;
        }

        if (false === empty($this->creditcard)) {
            $data['creditcard'] = $this->creditcard->toArray();
        }

        if (false === empty($this->customer)) {
            $data['customer'] = (array) $this->customer->toArray();
        }

        if (false === empty($this->payment)) {
            $data['payment'] = (array) $this->payment->toArray();
        }

        return $data;
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
     * Get the value of mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }
    /**
     * Sets the value of mode
     *
     * @param string $mode
     *
     * @return self
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Sets the value of threatMatrixSessionId
     *
     * @param string $threatMatrixSessionId
     *
     * @return self
     */
    public function setThreatMetrixSessionId($threatMatrixSessionId)
    {
        $this->threatMetrixSessionId = $threatMatrixSessionId;

        return $this;
    }

    /**
     * Get the value of threatMatrixSessionId
     *
     * @return string
     */
    public function getThreatMetrixSessionId()
    {
        return $this->threatMetrixSessionId;
    }


    /**
     * Gets the value of billingAgreement.
     *
     * @return boolean
     */
    public function getBillingAgreement()
    {
        return $this->billingAgreement;
    }

    /**
     * Sets the value of billingAgreement.
     *
     * @param boolean $billingAgreement the billingAgreement
     *
     * @return self
     */
    public function setBillingAgreement($billingAgreement)
    {
        $this->billingAgreement = $billingAgreement;

        return $this;
    }

    /**
     * Gets the value of emsUrl.
     *
     * @return string
     */
    public function getEmsUrl()
    {
        return $this->emsUrl;
    }

    /**
     * Sets the value of emsUrl.
     *
     * @param string $emsUrl the emsUrl
     *
     * @return self
     */
    public function setEmsUrl($emsUrl)
    {
        $this->emsUrl = $emsUrl;

        return $this;
    }

    /**
     * Gets the value of redirectUrl.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * Sets the value of redirectUrl.
     *
     * @param string $redirectUrl the redirectUrl
     *
     * @return self
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * Gets the value of payment.
     *
     * @return Syspay_Merchant_Entity_Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Sets the value of payment.
     *
     * @param Syspay_Merchant_Entity_Payment $payment the payment
     *
     * @return self
     */
    public function setPayment(Syspay_Merchant_Entity_Payment $payment)
    {
        $this->payment = $payment;

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
     * @param string $paymentMethod the paymentMethod
     *
     * @return self
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Gets the value of website.
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Sets the value of website.
     *
     * @param string $website the website
     *
     * @return self
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Gets the agent
     * @return string
     */
    public function getAgent() {
        return $this->agent;
    }

    /**
     * Sets the value of agent.
     *
     * @param string $agent the agent id
     *
     * @return self
     */
    public function setAgent($agent) {
        $this->agent = $agent;
        return $this;
    }


}
