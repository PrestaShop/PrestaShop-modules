<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * A payment object
 */
class Syspay_Merchant_Entity_Payment extends Syspay_Merchant_Entity
{
    const TYPE = 'payment';

    /**
     * The card has been flagged as lost or stolen
     * @see getFailureCategory()
     */
    const FAILURE_CARD_FLAGGED       = 'card_flagged';

    /**
     * The transaction has been declined by the acquirer
     * @see getFailureCategory()
     */
    const FAILURE_DECLINED           = 'declined';

    /**
     * Two transactions with the same information have been attempted in a too short period
     * @see getFailureCategory()
     */
    const FAILURE_DUPLICATED         = 'duplicated';

    /**
     * The card has expired
     * @see getFailureCategory()
     */
    const FAILURE_EXPIRED            = 'expired';

    /**
     * The transaction has been refused for suspicion of fraud
     * @see getFailureCategory()
     */
    const FAILURE_FRAUD              = 'fraud_suspicious';

    /**
     * There wasn't enough money available to complete the transaction
     * @see getFailureCategory()
     */
    const FAILURE_INSUFFICIENT_FUNDS = 'insufficient_funds';

    /**
     * The card number is not valid
     * @see getFailureCategory()
     */
    const FAILURE_INVALID_CARD       = 'invalid_card';

    /**
     * The CV2 is not valid
     * @see getFailureCategory()
     */
    const FAILURE_INVALID_CV2        = 'invalid_cv2';

    /**
     * The card information are not valid
     * @see getFailureCategory()
     */
    const FAILURE_INVALID_DETAILS    = 'invalid_details';

    /**
     * The transaction was refused by the acquirer but the reason could not be precisely determined
     * @see getFailureCategory()
     */
    const FAILURE_OTHER              = 'other';

    /**
     * A technical error occured while attempting to process the transaction
     * @see getFailureCategory()
     */
    const FAILURE_TECHNICAL_ERROR    = 'technical_error';

    /**
     * The card scheme is not supported
     * @see getFailureCategory()
     */
    const FAILURE_UNSUPPORTED        = 'unsupported';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $chipAndPinStatus;

    /**
     * @var  string
     */
    private $failureCategory;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var integer
     */
    protected $amount;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $extra;

    /**
     * @var boolean
     */
    protected $preauth;

    /**
     * @var integer
     */
    private $website;
    /**
     * @var Syspay_Merchant_Entity_BillingAgreement
     */
    private $billing_agreement;

    /**
     * @var Syspay_Merchant_Entity_Subscription
     */
    private $subscription;

    /**
     * @var string
     */
    private $redirect;

    /**
     * @var DateTime
     */
    private $processingTime;

    /**
     * @var array
     */
    protected $recipient_map;

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

    /**
     * Gets the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param integer $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the value of status.
     *
     * @param string $status the status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets the value of billing_agreement.
     *
     * @return Syspay_Merchant_Entity_BillingAgreement
     */
    public function getBillingAgreement()
    {
        return $this->billing_agreement;
    }

    /**
     * Sets the value of billing_agreement.
     *
     * @param Syspay_Merchant_Entity_BillingAgreement $billing_agreement the billing_agreement
     *
     * @return self
     */
    public function setBillingAgreement(Syspay_Merchant_Entity_BillingAgreement $billingAgreement)
    {
        $this->billing_agreement = $billingAgreement;

        return $this;
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
     * Gets the value of redirect.
     *
     * @return string
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Sets the value of redirect.
     *
     * @param string $redirect the redirect
     *
     * @return self
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;

        return $this;
    }

    /**
     * Build a payment entity based on a json-decoded payment stdClass
     *
     * @param  stdClass $response The payment data
     * @return Syspay_Merchant_Entity_Payment The payment object
     */
    public static function buildFromResponse(stdClass $response)
    {
        $payment = new self();
        $payment->setId(isset($response->id)?$response->id:null);
        $payment->setReference(isset($response->reference)?$response->reference:null);
        $payment->setAmount(isset($response->amount)?$response->amount:null);
        $payment->setCurrency(isset($response->currency)?$response->currency:null);
        $payment->setStatus(isset($response->status)?$response->status:null);
        $payment->setExtra(isset($response->extra)?$response->extra:null);
        $payment->setDescription(isset($response->description)?$response->description:null);
        $payment->setWebsite(isset($response->website)?$response->website:null);
        $payment->setFailureCategory(isset($response->failure_category)?$response->failure_category:null);
        $payment->setChipAndPinStatus(isset($response->chip_and_pin_status)?$response->chip_and_pin_status:null);

        if (isset($response->processing_time)
                && !is_null($response->processing_time)) {
            $payment->setProcessingTime(Syspay_Merchant_Utils::tsToDateTime($response->processing_time));
        }

        if (isset($response->billing_agreement)
                && ($response->billing_agreement instanceof stdClass)) {
            $billingAgreement = Syspay_Merchant_Entity_BillingAgreement::buildFromResponse($response->billing_agreement);
            $payment->setBillingAgreement($billingAgreement);
        }

        if (isset($response->subscription)
                && ($response->subscription instanceof stdClass)) {
            $subscription = Syspay_Merchant_Entity_Subscription::buildFromResponse($response->subscription);
            $payment->setSubscription($subscription);
        }

        return $payment;
    }

    /**
     * Gets the value of preauth.
     *
     * @return boolean
     */
    public function getPreauth()
    {
        return $this->preauth;
    }

    /**
     * Sets the value of preauth.
     *
     * @param boolean $preauth the preauth
     *
     * @return self
     */
    public function setPreauth($preauth)
    {
        $this->preauth = $preauth;

        return $this;
    }

    /**
     * Gets the value of website.
     *
     * @return integer
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Sets the value of website.
     *
     * @param integer $website the website
     *
     * @return self
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Gets the value of processingTime.
     *
     * @return DateTime
     */
    public function getProcessingTime()
    {
        return $this->processingTime;
    }

    /**
     * Sets the value of processingTime.
     *
     * @param DateTime $processingTime the processingTime
     *
     * @return self
     */
    public function setProcessingTime(DateTime $processingTime)
    {
        $this->processingTime = $processingTime;

        return $this;
    }

    /**
     * Gets the failure category, this is one of the FAILURE_* constants
     * @return string
     */
    public function getFailureCategory() {
        return $this->failureCategory;
    }

    /**
     * Sets the value of failureCategory.
     *
     * @param string $failureCategory the failureCategory
     *
     * @return self
     */
    public function setFailureCategory($failureCategory) {
        $this->failureCategory = $failureCategory;
    }


    /**
     * Gets the value of chipAndPinStatus.
     *
     * @return string
     */
    public function getChipAndPinStatus()
    {
        return $this->chipAndPinStatus;
    }

    /**
     * Sets the value of chipAndPinStatus.
     *
     * @param string $chipAndPinStatus the chipAndPinStatus
     *
     * @return self
     */
    public function setChipAndPinStatus($chipAndPinStatus)
    {
        $this->chipAndPinStatus = $chipAndPinStatus;

        return $this;
    }

    /**
     * Gets the value of recipient_map.
     *
     * @return array
     */
    public function getRecipientMap()
    {
        return $this->recipient_map;
    }

    /**
     * Sets the value of recipient_map.
     *
     * @param array $recipientMap An array of Syspay_Merchant_Entity_PaymentRecipient
     *
     * @return self
     */
    public function setRecipientMap(array $recipientMap)
    {
        foreach ($recipientMap as $r) {
            if (!$r instanceof Syspay_Merchant_Entity_PaymentRecipient) {
                throw new InvalidArgumentException('The given array must only contain Syspay_Merchant_Entity_PaymentRecipient instances');
            }
        }
        $this->recipient_map = $recipientMap;

        return $this;
    }

    /**
     * Add a PaymentRecipient to the recipient map list
     *
     * @param Syspay_Merchant_Entity_PaymentRecipient $paymentRecipient
     *
     * @return self
     */
    public function addRecipient(Syspay_Merchant_Entity_PaymentRecipient $paymentRecipient)
    {
        if (!isset($this->recipient_map)) {
            $this->recipient_map = array();
        }
        array_push($this->recipient_map, $paymentRecipient);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        $data = parent::toArray();
        if (!empty($data['recipient_map']) && is_array($data['recipient_map'])) {
            for ($i = 0; $i < count($data['recipient_map']); $i++) {
                $data['recipient_map'][$i] = $data['recipient_map'][$i]->toArray();
            }
        } else {
            unset($data['recipient_map']);
        }
        return $data;
    }
}
