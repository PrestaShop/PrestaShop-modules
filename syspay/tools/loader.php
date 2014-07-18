<?php
/**
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2011 PrestaShop SA
*  @version   Release: $Revision: 7732 $
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/*
* The MIT License (MIT)
* 
* Copyright (c) 2013, SysPay Ltd.
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*/ 


$base = dirname(__FILE__) . '/lib/Syspay/SDK/Merchant/';

$required = array(
    'Client',
    'EMS',
    'Entity',
    'Entity/BillingAgreement',
    'Entity/Chargeback',
    'Entity/Creditcard',
    'Entity/Customer',
    'Entity/Payment',
    'Entity/PaymentMethod',
    'Entity/PaymentRecipient',
    'Entity/Plan',
    'Entity/Refund',
    'Entity/Subscription',
    'Exception/EMS',
    'Exception/Redirect',
    'Exception/Request',
    'Exception/UnexpectedResponse',
    'Redirect',
    'Request',
    'Request/BillingAgreementCancellation',
    'Request/BillingAgreementInfo',
    'Request/BillingAgreementList',
    'Request/ChargebackInfo',
    'Request/ChargebackList',
    'Request/Confirm',
    'Request/IpAddresses',
    'Request/Payment',
    'Request/PaymentInfo',
    'Request/PaymentList',
    'Request/Plan',
    'Request/PlanInfo',
    'Request/Rebill',
    'Request/Refund',
    'Request/RefundInfo',
    'Request/RefundList',
    'Request/Subscription',
    'Request/SubscriptionCancellation',
    'Request/SubscriptionInfo',
    'Request/Void',
    'Utils',
);

foreach ($required as $req) {
    require_once($base . $req . '.php');
}
