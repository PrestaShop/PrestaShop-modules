<?php if (!defined('ALLOW_PAGSEGURO_RESOURCES')) { die('No direct script access allowed'); }

// Production environment
$PagSeguroResources['environment'] = Array();
$PagSeguroResources['environment']['production']['webserviceUrl'] = "https://ws.pagseguro.uol.com.br";

// Payment service
$PagSeguroResources['paymentService'] = Array();
$PagSeguroResources['paymentService']['servicePath'] = "/v2/checkout";
$PagSeguroResources['paymentService']['checkoutUrl'] = "https://pagseguro.uol.com.br/v2/checkout/payment.html";
$PagSeguroResources['paymentService']['serviceTimeout'] = 20;

// Notification service
$PagSeguroResources['notificationService'] = Array();
$PagSeguroResources['notificationService']['servicePath'] = "/v2/transactions/notifications";
$PagSeguroResources['notificationService']['serviceTimeout'] = 20;

// Transaction search service
$PagSeguroResources['transactionSearchService'] = Array();
$PagSeguroResources['transactionSearchService']['servicePath'] = "/v2/transactions";
$PagSeguroResources['transactionSearchService']['serviceTimeout'] = 20;

?>