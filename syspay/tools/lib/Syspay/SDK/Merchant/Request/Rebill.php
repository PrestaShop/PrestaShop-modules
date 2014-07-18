<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Rebill a customer
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#issue-a-rebill-on-a-billing-agreement
 */
class Syspay_Merchant_RebillRequest extends Syspay_Merchant_Request
{
    const METHOD = 'POST';
    const PATH   = '/api/v1/merchant/billing-agreement/%d/rebill';

    /**
     * @var string
     */
    private $threatMetrixSessionId;

    /**
     * @var string
     */
    private $emsUrl;

    /**
     * @var integer
     */
    private $billingAgreementId;

    /**
     * @var string
     */
    private $reference;

    /**
     * @var integer
     */
    private $amount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $extra;

    public function __construct($billingAgreementId = null)
    {
        if (null !== $billingAgreementId) {
            $this->setBillingAgreementId($billingAgreementId);
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
        return sprintf(self::PATH, $this->billingAgreementId);
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

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        $data = array();

        if (false == empty($this->threatMetrixSessionId)) {
            $data['threatmetrix_session_id'] = $this->threatMetrixSessionId;
        }

        if (false === empty($this->emsUrl)) {
            $data['ems_url'] = $this->emsUrl;
        }

        $data['payment'] = array();

        if (false === empty($this->reference)) {
            $data['payment']['reference'] = $this->reference;
        }

        if (false === empty($this->amount)) {
            $data['payment']['amount'] = $this->amount;
        }

        if (false === empty($this->currency)) {
            $data['payment']['currency'] = $this->currency;
        }

        if (false === empty($this->description)) {
            $data['payment']['description'] = $this->description;
        }

        if (false === empty($this->extra)) {
            $data['payment']['extra'] = $this->extra;
        }

        return $data;
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
     * @param string $threatMetrixSessionId the threatMetrixSessionId
     *
     * @return self
     */
    public function setThreatMetrixSessionId($threatMetrixSessionId)
    {
        $this->threatMetrixSessionId = $threatMetrixSessionId;

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
     * Gets the value of billingAgreementId.
     *
     * @return integer
     */
    public function getBillingAgreementId()
    {
        return $this->billingAgreementId;
    }

    /**
     * Sets the value of billingAgreementId.
     *
     * @param integer $billingAgreementId the billingAgreementId
     *
     * @return self
     */
    public function setBillingAgreementId($billingAgreementId)
    {
        $this->billingAgreementId = $billingAgreementId;

        return $this;
    }

    /**
     * Gets the value of reference.
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Sets the value of reference.
     *
     * @param string $reference the reference
     *
     * @return self
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Gets the value of amount.
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets the value of amount.
     *
     * @param integer $amount the amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Gets the value of currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Sets the value of currency.
     *
     * @param string $currency the currency
     *
     * @return self
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Gets the value of description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the value of description.
     *
     * @param string $description the description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of extra.
     *
     * @return string
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Sets the value of extra.
     *
     * @param string $extra the extra
     *
     * @return self
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;

        return $this;
    }
}
