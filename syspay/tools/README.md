# Syspay PHP Merchant SDK

## API Documentation
An online version of the API documentation can be found [on our site](https://app.syspay.com/docs/merchant-sdk-php/index.html)

## Installation
This library requires php 5.2+ along with the `json` and the `curl` extensions.

~~~ {.php}
    <?php
    require_once('/path/to/loader.php');
~~~

## Requesting the API
Implementation reference: [SysPay Processing API](https://app.syspay.com/docs/api/merchant_api.html)

### Create a client
All operations are requested via an instance of a [Client object](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Client.html).

~~~ {.php}
    <?php
    $client = new Syspay_Merchant_Client($username, $secret[, $baseUrl]);
~~~

To call against the sandbox environment, the `$baseUrl` can be set to `Syspay_Merchant_Client::BASE_URL_SANDBOX`.

### Creditcard payment request

Request class: [Syspay\_Merchant\_PaymentRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_PaymentRequest.html)

You request a [payment](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Payment.html) for a [customer](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Customer.html) on a given [creditcard](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Creditcard.html):

~~~ {.php}
    <?php
    $paymentRequest = new Syspay_Merchant_PaymentRequest(Syspay_Merchant_PaymentRequest::FLOW_API);
    $paymentRequest->setPaymentMethod(Syspay_Merchant_PaymentRequest::METHOD_CREDITCARD);
    $paymentRequest->setBillingAgreement(true); // true means you want to be able to rebill this customer later. Defaults to false

    $customer = new Syspay_Merchant_Entity_Customer();
    $customer->setEmail('foo@bar.baz'); // Customer's email
    $customer->setLanguage('en'); // Optional, used to send notifications in the correct language
    $customer->setIp('1.2.3.4'); // Customer IP address
    $paymentRequest->setCustomer($customer);

    $creditcard = new Syspay_Merchant_Entity_Creditcard();
    $creditcard->setHolder('John Doe');
    $creditcard->setNumber('4556267280522645');
    $creditcard->setCvc('123');
    $creditcard->setExpMonth('01');
    $creditcard->setExpYear('2014');
    $paymentRequest->setCreditcard($creditcard);

    $payment = new Syspay_Merchant_Entity_Payment();
    $payment->setReference('1234567'); // Your own reference for this payment
    $payment->setPreauth(true); // By default, we will process a DIRECT payment. Set this to true to PREAUTH only, it will then need to be confirmed later
    $payment->setAmount(1000); // Amount in *cents*
    $payment->setCurrency('EUR'); // Currency
    $payment->setDescription('some description'); // An optional description
    $payment->setExtra(json_encode($someInformation)); // An optional information that will given back to you on notifications
    $paymentRequest->setPayment($payment);

    $payment = $client->request($paymentRequest);
    // $payment is an instance of Syspay_Merchant_Entity_Payment
~~~

### Confirm an AUTHORIZED payment

Request class: [Syspay\_Merchant\_ConfirmRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_ConfirmRequest.html)

~~~ {.php}
    <?php
    $confirmRequest = new Syspay_Merchant_ConfirmRequest();
    $confirmRequest->setPaymentId($originalPaymentId); // Returned to you on the initial payment request

    $confirm = $client->request($confirmRequest);
    // $confirm is an instance of Syspay_Merchant_Entity_Payment
~~~

### Void an AUTHORIZED payment

Request class: [Syspay\_Merchant\_VoidRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_VoidRequest.html)

~~~ {.php}
    <?php
    $voidRequest = new Syspay_Merchant_VoidRequest();
    $voidRequest->setPaymentId($originalPaymentId); // Returned to you on the initial payment request

    $void = $client->request($voidRequest);
    // $void is an instance of Syspay_Merchant_Entity_Payment
~~~

### Get information about a payment

Request class: [Syspay\_Merchant\_PaymentInfoRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_PaymentInfoRequest.html)

~~~ {.php}
    <?php
    $infoRequest = new Syspay_Merchant_PaymentInfoRequest($paymentId);

    $payment = $client->request($infoRequest);
    // $payment is an instance of Syspay_Merchant_Entity_Payment
~~~

### Export a list of payments

Request class: [Syspay\_Merchant\_PaymentListRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_PaymentListRequest.html)
The list of available filters can be found in our [api documentation](https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-a-list-of-payments)

~~~ {.php}
    <?php
    $paymentListRequest = new Syspay_Merchant_PaymentListRequest();
    // Optionally set filters (refer to the API documentation for an exhaustive list)
    $paymentListRequest->addFilter('start_date', $someTimestamp);
    $paymentListRequest->addFilter('end_date', $someOtherTimestamp);

    $payments = $client->request($paymentListRequest);
    // $payments is an array of Syspay_Merchant_Entity_Payment
~~~

### Refund a payment

Request class: [Syspay\_Merchant\_RefundRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_RefundRequest.html)

You request a [refund](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Refund.html) on a given *payment id*:

~~~ {.php}
    <?php
    $refund = new Syspay_Merchant_Entity\Refund();
    $refund->setReference('1234567'); // Your own reference for this refund
    $refund->setAmount(1000); // The amount to refund in *cents*
    $refund->setCurrency('EUR'); // The currency of the refund. It must match the one of the original payment
    $refund->setDescription('some description'); // An optional description for this refund
    $refund->setExtra(json_encode($someInformation)); // An optional information that will be given back to you on notifications

    $refundRequest = new Syspay_Merchant_RefundRequest();
    $refundRequest->setPaymentId($paymentId); // The payment id to refund
    $refundRequest->setRefund($refund);

    $refund = $client->request($refundRequest);
    // $refund is an instance of Syspay_Merchant_Entity_Refund
~~~

### Get information about a refund

Request class: [Syspay\_Merchant\_RefundInfoRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_RefundInfoRequest.html)

~~~ {.php}
    <?php
    $infoRequest = new Syspay_Merchant_RefundInfoRequest($refundId);

    $refund = $client->request($infoRequest);
    // $refund is an instance of Syspay_Merchant_Entity_Refund
~~~

### Export a list of refunds

Request class: [Syspay\_Merchant\_RefundListRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_RefundListRequest.html)
The list of available filters can be found in our [api documentation](https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-a-list-of-refunds)

~~~ {.php}
    <?php
    $refundListRequest = new Syspay_Merchant_RefundListRequest();
    // Optionally set filters (refer to the API document for an exhaustive list)
    $paymentListRequest->addFilter('status', 'SUCCESS');

    $refunds = $client->request($refundListRequest);
    // $refunds is an array of Syspay_Merchant_Entity_Refund
~~~

### Rebill on a given billing agreement

Request class: [Syspay\_Merchant\_RebillRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_RebillRequest.html)

~~~ {.php}
    <?php
    // The billing agreement id returned from the initial payment request must be used
    $rebillRequest = new Syspay_Merchant_RebillRequest($billingAgreementId);
    $rebillRequest->setAmount(1000); // Amount in *cents*
    $rebillRequest->setCurrency('EUR'); // This is used as security and must match the currency that was used to create the billing agreement
    $rebillRequest->setReference('123456'); // Your own reference for this payment
    $rebillRequest->setDescription('some description'); // An optional description
    $rebillRequest->setExtra(json_encode($someInformation)); // An optional information that will given back to you on notifications
    $rebillRequest->setEmsUrl('https://foo.bar/baz'); // An optional EMS url the notifications will be posted to if you don't want to use the default one

    $payment = $client->request($rebillRequest);
    // $payment is an instance of Syspay_Merchant_Entity_Payment
~~~

### Get information about a billing agreement

Request class: [Syspay\_Merchant\_BillingAgreementInfoRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_BillingAgreementInfoRequest.html)

~~~ {.php}
    <?php
    $infoRequest = new Syspay_Merchant_BillingAgreementInfoRequest($billingAgreementId);

    $billingAgreement = $client->request($infoRequest);
    // $billingAgreement is an instance of Syspay_Merchant_Entity_BillingAgreement
~~~

### Cancel a billing agreement

Request class: [Syspay\_Merchant\_BillingAgreementCancellationRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_BillingAgreementCancellationRequest.html)

~~~ {.php}
    <?php
    $cancellationRequest = new Syspay_Merchant_BillingAgreementCancellationRequest($billingAgreemntId);

    $billingAgreement = $client->request($cancellationRequest);
    // $billingAgreement is an instance of Syspay_Merchant_Entity_BillingAgreement
~~~

### Export a list of billing agreements

Request class: [Syspay\_Merchant\_BillingAgreementListRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_BillingAgreementListRequest.html)
The list of available filters can be found in our [api documentation](https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-a-list-of-billing-agreements)

~~~ {.php}
    <?php
    $billingAgreementsRequest = new Syspay_Merchant_BillingAgreementListRequest();
    // Optionally set filters (refer to the API document for an exhaustive list)
    $billingAgreementsRequest->addFilter('status', 'ACTIVE');

    $billingAgreements = $client->request($billingAgreementsRequest);
    // $billingAgreements is an array of Syspay_Merchant_Entity_BillingAgreement
~~~

### Get information about a chargeback

Request class: [Syspay\_Merchant\_ChargebackInfoRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_ChargebackInfoRequest.html)

~~~ {.php}
    <?php
    $infoRequest = new Syspay_Merchant_ChargebackInfoRequest($chargebackId);

    $chargeback = $client->request($infoRequest);
    // $chargeback is an instance of Syspay_Merchant_Entity_Chargeback
~~~

### Export a list of chargebacks

Request class: [Syspay\_Merchant\_ChargebackListRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_ChargebackListRequest.html)
The list of available filters can be found in our [api documentation](https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#get-a-list-of-chargebacks)

~~~ {.php}
    <?php
    $chargebackListRequest = new Syspay_Merchant_ChargebackListRequest();
    // Optionally set filters (refer to the API document for an exhaustive list)
    $paymentListRequest->addFilter('email', 'foo@bar.baz');

    $chargebacks = $client->request($chargebackListRequest);
    // $chargebacks is an array of Syspay_Merchant_Entity_Chargeback
~~~

### Get the Syspay IP addresses

Request class: [Syspay\_Merchant\_IpAddressesRequest](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_IpAddressesRequest.html)

~~~ {.php}
    <?php
    $ipRequest = new Syspay_Merchant_IpAddressesRequest();

    $ips = $client->request($ipRequest);
    // $ips is an array of strings (ips)
~~~

## Handling hosted payment pages and 3DS redirections

When a payment requires a redirection (either during a server-to-server payment that needs a 3DS verification, or when using the hosted payment page flow), you will not know synchronously the result of the transaction.

Instead, once the transaction will be processed, the customer will be redirected back to your site (either to your default redirect url, or to the one you set upon request) along with extra parameters that will inform you about the result of the transaction.

To make it easy for you to validate these extra parameters and extract the information, you can use the [Syspay\_Merchant\_Redirect](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Redirect.html) handler. It will check that the parameters haven't been tampered with and return a [payment](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Payment.html) object.

NOTE: Should the customer not come back to your site (e.g, he closes his browser on the hosted payment confirmation page), you will still be notified about the payment status using our EMS system (described in the [next chapter](#receiving-ems-notifications))

### Sample handler

~~~ {.php}
    <?php

    require '/path/to/merchant-sdk-php/loader.php';

    // You might have multiple merchant credentials that all point to the same EMS url
    $secrets = array(
        'login_1' => 'secret_1',
        'login_2' => 'secret_2'
    );

    try {
        // The getResult method takes an array as input. This array must contain the 'result', 'merchant' and 'checksum' request parameters
        $payment = $redirect->getResult($_REQUEST);
    } catch (Syspay_Merchant_RedirectException $e) {
        // If an error status is sent, syspay will try again to deliver the message several times.
        header(':', true, 500);
        printf("Something went wrong while processing the message: (%d) %s\n",
                    $e->getCode(), $e->getMessage());
    }
~~~


## Receiving EMS notifications

Implementation reference: [SysPay Event Messaging System](https://app.syspay.com/bundles/emiuser/doc/merchant_ems.html)

The [Syspay\_Merchant\_EMS](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_EMS.html) class will automatically validate the HTTP headers and parse the event to return the relevant object.

The currently supported events are:

* Payments ([Syspay\_Merchant\_Entity\_Payment](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Payment.html))
* Refunds ([Syspay\_Merchant\_Entity\_Refund](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Refund.html))
* Billing agreements ([Syspay\_Merchant\_Entity\_BillingAgreement](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_BillingAgreement.html))
* Chargebacks ([Syspay\_Merchant\_Entity\_Chargeback](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_Entity_Chargeback.html))

If an error occurs, a [Syspay\_Merchant\_EMSException](https://app.syspay.com/docs/merchant-sdk-php/class-Syspay_Merchant_EMSException.html) will be thrown and will contain one of the following codes:

* `Syspay_Merchant_EMSException::CODE_MISSING_HEADER`: The *X-Merchant* and/or the *X-Checksum* headers could not be found
* `Syspay_Merchant_EMSException::CODE_INVALID_CHECKSUM`: The *X-Checksum* header doesn't validate against the provided keys
* `Syspay_Merchant_EMSException::CODE_INVALID_CONTENT`: The request content could not be parsed
* `Syspay_Merchant_EMSException::CODE_UNKNOWN_MERCHANT`: The *X-Merchant* header is there but cannot be found in the array passed to the constructor

### Sample listener

~~~~~ {.php}
    <?php

    require '/path/to/merchant-sdk-php/loader.php';

    // You might have multiple merchant credentials that all point to the same EMS url
    $secrets = array(
        'login_1' => 'secret_1',
        'login_2' => 'secret_2'
    );

    $ems = new Syspay_Merchant_EMS($secrets);

    try {
        $event = $ems->getEvent();
        switch ($event->getType()) {
            case 'payment':
                printf("Payment %d received, status: %s\n", $event->getId(), $event->getStatus());
                break;
            case 'refund':
                printf("Refund %d received, status: %s\n", $event->getId(), $event->getStatus());
                break;
            case 'chargeback':
                printf("Chargeback %d received, status: %s\n", $event->getId(), $event->getStatus());
                break;
            case 'billing_agreement':
                printf("Billing Agreement %d received, status: %s\n", $event->getId(), $event->getStatus());
                break;
        }
    } catch (Syspay_Merchant_EMSException $e) {
        // If an error status is sent, syspay will try again to deliver the message several times
        header(':', true, 500);
        printf("Something went wrong while processing the message: (%d) %s",
                    $e->getCode(), $e->getMessage());
    }
~~~~~

