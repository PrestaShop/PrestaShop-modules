<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * A payment method object (this gives displayable information about a payment method used for a payment)
 */
class Syspay_Merchant_Entity_PaymentMethod extends Syspay_Merchant_Entity
{
    const TYPE = 'payment_method';

    const TYPE_CREDITCARD  = 'CREDITCARD';
    const TYPE_PAYSAFECARD = 'PAYSAFECARD';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $display;

    /**
     * Build a paymentMethod entity based on a json-decoded payment_method stdClass
     *
     * @param  stdClass $response The payment method data
     * @return Syspay_Merchant_Entity_PaymentMethod The payment method object
     */
    public static function buildFromResponse(stdClass $response)
    {
        $paymentMethod = new self();
        $paymentMethod->setType(isset($response->type)?$response->type:null);
        $paymentMethod->setDisplay(isset($response->display)?$response->display:null);
        return $paymentMethod;
    }

    /**
     * Gets the value of type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the value of type.
     *
     * @param string $type the type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the value of display.
     *
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Sets the value of display.
     *
     * @param string $display the display
     *
     * @return self
     */
    public function setDisplay($display)
    {
        $this->display = $display;

        return $this;
    }
}
