<?php
/**
 * 2007-2013 PrestaShop
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
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once dirname(__FILE__).'/../../../../config/config.inc.php';

class Util
{

    private static $charset_options = array(
        '1' => 'ISO-8859-1',
        '2' => 'UTF-8'
    );

    private static $active = array(
        '0' => 'NÃO',
        '1' => 'SIM'
    );

    private static $type_checkout = array(
        '0' => 'PADRÃO',
        '1' => 'LIGHTBOX'
    );

    private static $order_status = array(
        'INITIATED' => 'Iniciado',
        'WAITING_PAYMENT' => 'Aguardando pagamento',
        'IN_ANALYSIS' => 'Em análise',
        'PAID' => 'Paga',
        'AVAILABLE' => 'Disponível',
        'IN_DISPUTE' => 'Em disputa',
        'REFUNDED' => 'Devolvida',
        'CANCELLED' => 'Cancelada'
    );

    private static $order_status_pagseguro = array(
        'INITIATED' => array(
            'name' => 'Iniciado',
            'send_email' => false,
            'template' => '',
            'hidden' => true,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false
        ),
        'WAITING_PAYMENT' => array(
            'name' => 'Aguardando pagamento',
            'send_email' => true,
            'template' => 'awaiting_payment',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false
        ),
        'IN_ANALYSIS' => array(
            'name' => 'Em análise',
            'send_email' => true,
            'template' => 'in_analysis',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false
        ),
        'PAID' => array(
            'name' => 'Paga',
            'send_email' => true,
            'template' => 'payment',
            'hidden' => false,
            'delivery' => false,
            'logable' => true,
            'invoice' => true,
            'unremovable' => false,
            'shipped' => false,
            'paid' => true
        ),
        'AVAILABLE' => array(
            'name' => 'Disponível',
            'send_email' => false,
            'template' => '',
            'hidden' => true,
            'delivery' => false,
            'logable' => true,
            'invoice' => true,
            'unremovable' => false,
            'shipped' => false,
            'paid' => true
        ),
        'IN_DISPUTE' => array(
            'name' => 'Em disputa',
            'send_email' => false,
            'template' => '',
            'hidden' => true,
            'delivery' => false,
            'logable' => true,
            'invoice' => true,
            'unremovable' => false,
            'shipped' => false,
            'paid' => true
        ),
        'REFUNDED' => array(
            'name' => 'Devolvida',
            'send_email' => true,
            'template' => 'refund',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false
        ),
        'CANCELLED' => array(
            'name' => 'Cancelada',
            'send_email' => true,
            'template' => 'order_canceled',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false
        )
    );

    private static $update_config_versio_14 = array(
        'PS_OS_CHEQUE' => 1,
        'PS_OS_PAYMENT' => 2,
        'PS_OS_PREPARATION' => 3,
        'PS_OS_SHIPPING' => 4,
        'PS_OS_DELIVERED' => 5,
        'PS_OS_CANCELED' => 6,
        'PS_OS_REFUND' => 7,
        'PS_OS_ERROR' => 8,
        'PS_OS_OUTOFSTOCK' => 9,
        'PS_OS_BANKWIRE' => 10,
        'PS_OS_PAYPAL' => 11,
        'PS_OS_WS_PAYMENT' => 12
    );
    
    private static $array_st_cms = array(
        0 => 'Iniciado',
        1 => 'Aguardando pagamento',
        2 => 'Em análise',
        3 => 'Paga',
        4 => 'Disponível',
        5 => 'Em disputa',
        6 => 'Devolvida',
        7 => 'Cancelada'
    );
    
    private static $days_recovery = array(
        1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5,
        6 => 6, 7 => 7, 8 => 8, 9 => 9, 10=> 10
    );
    
    private static $days_search = array(
        0 => 5,  1 => 10, 2 => 15,
        3 => 20, 4 => 25, 5 => 30
    );
    
    public static function getStatusCMS($id_status)
    {
        return self::$array_st_cms[$id_status];
    }
    
    public static function getDaysSearch()
    {
        return self::$days_search;
    }
    
    public static function getDaysRecovery()
    {
        return self::$days_recovery;
    }
    
    public static function getCharsetOptions()
    {
        return self::$charset_options;
    }

    public static function getActive()
    {
        return self::$active;
    }
    
    public static function getTypeCheckout()
    {
        return self::$type_checkout;
    }

    public static function getOrderStatus()
    {
        return self::$order_status;
    }

    public static function getCustomOrderStatusPagSeguro()
    {
        return self::$order_status_pagseguro;
    }

    public static function getUpdateConfigVersion14()
    {
        return self::$update_config_versio_14;
    }
    
    public static function getJsBehaviorPS14()
    {
        return __PS_BASE_URI__ . 'modules/pagseguro/assets/js/behaviors-version-14.js';
    }
    
    public static function getJsBehaviorPS15()
    {
        return __PS_BASE_URI__ . 'modules/pagseguro/assets/js/behaviors-version-15.js';
    }

    public static function getJsBehaviorPS16()
    {
        return __PS_BASE_URI__ . 'modules/pagseguro/assets/js/behaviors-version-15.js';
    }

    public static function getJsBehaviorPS1601()
    {
        return __PS_BASE_URI__ . 'modules/pagseguro/assets/js/behaviors-version-15.js';
    }
    
    public static function getCssDisplayPS14()
    {
        return __PS_BASE_URI__ . 'modules/pagseguro/assets/css/styles-version-14.css';
    }
    
    public static function getCssDisplayPS15()
    {
        return __PS_BASE_URI__ . 'modules/pagseguro/assets/css/styles-version-15.css';
    }

    public static function getCssDisplayPS16()
    {
        return __PS_BASE_URI__ . 'modules/pagseguro/assets/css/styles-version-16.css';
    }

    public static function getCssDisplayPS1601()
    {
        return __PS_BASE_URI__ . 'modules/pagseguro/assets/css/styles-version-1601.css';
    }
    
    private static function getBaseDefaultUrl()
    {
        return _PS_BASE_URL_ . __PS_BASE_URI__;
    }
    
    public static function getDefaultRedirectUrlPS14()
    {
        return self::getBaseDefaultUrl();
    }
    
    public static function getDefaultRedirectUrlPS15()
    {
        $index = version_compare(_PS_VERSION_, '1.5.0.3', '<=') ? '' : 'index.php';
        return self::getBaseDefaultUrl() . $index;
    }

    public static function getDefaultRedirectUrlPS16()
    {
        $index = "";
        return self::getBaseDefaultUrl() . $index;
    }
    
    public static function getDefaultNotificationUrlPS14()
    {
        return self::getBaseDefaultUrl() . 'modules/pagseguro/standard/front/notification.php';
    }
    
    public static function getDefaultNotificationUrlPS15()
    {
        return self::getBaseDefaultUrl() . 'index.php?fc=module&module=pagseguro&controller=notification';
    }

    public static function getDefaultNotificationUrlPS16()
    {
        return self::getBaseDefaultUrl() . 'index.php?fc=module&module=pagseguro&controller=notification';
    }
    
    public static function urlToRedirectPS14 (Array $data)
    {
        
        $urlToCompose = self::getRedirectUrl();
        if (Tools::isEmpty($urlToCompose)) {
            $urlToCompose = self::getDefaultRedirectUrlPS14();
        }
        
        return $urlToCompose . 'order-confirmation.php?id_cart=' . $data['id_cart'] . '&id_module=' .
            $data['id_module'] . '&id_order=' . $data['id_order'] . '&key=' . $data['key'];
    }
    
    public static function urlToRedirectPS15 (Array $data)
    {

        $urlToCompose = self::getRedirectUrl();
        if (Tools::isEmpty($urlToCompose)) {
            $urlToCompose = self::getDefaultRedirectUrlPS15();
        }

        return $urlToCompose . '?controller=order-confirmation&id_cart=' . $data['id_cart'] .
        '&id_module=' . $data['id_module'] . '&id_order=' . $data['id_order'] . '&key=' . $data['key'];
    }

    public static function urlToRedirectPS16 (Array $data)
    {

        $urlToCompose = self::getRedirectUrl();
        if (Tools::isEmpty($urlToCompose)) {
            $urlToCompose = self::getDefaultRedirectUrlPS15();
        }

        return $urlToCompose . '?controller=order-confirmation&id_cart=' . $data['id_cart'] .
        '&id_module=' . $data['id_module'] . '&id_order=' . $data['id_order'] . '&key=' . $data['key'];
    }
    
    public static function urlToNotificationPS14 ()
    {
        $urlToNotification = self::getNotificationUrl();
        return Tools::isEmpty($urlToNotification) ? self::getDefaultNotificationUrlPS14() : $urlToNotification;
    }
    
    public static function urlToNotificationPS15 ()
    {
        $urlToNotification = self::getNotificationUrl();
        return Tools::isEmpty($urlToNotification) ? self::getDefaultNotificationUrlPS15() : $urlToNotification;
    }

    public static function urlToNotificationPS16 ()
    {
        $urlToNotification = self::getNotificationUrl();
        return Tools::isEmpty($urlToNotification) ? self::getDefaultNotificationUrlPS15() : $urlToNotification;
    }
    
    public static function getNotificationUrl()
    {
        return Configuration::get('PAGSEGURO_NOTIFICATION_URL');
    }
    
    public static function getRedirectUrl()
    {
        return Configuration::get('PAGSEGURO_URL_REDIRECT');
    }

    public static function getWidthVersion($module_version)
    {
        return version_compare($module_version, '1.5', '<') ? 'style="width: 896px;' : 'style="width: 935px;';
    }

    public static function convertPriceFull($amount, $currency_from = null, $currency_to = null)
    {
        
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            return Tools::convertPriceFull($amount, $currency_from, $currency_to);
        } else {
            
            if ($currency_from === $currency_to) {
                return $amount;
            }
            if ($currency_from === null) {
                $currency_from = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            }
            if ($currency_to === null) {
                $currency_to = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            }
            if ($currency_from->id == Configuration::get('PS_CURRENCY_DEFAULT')) {
                $amount *= $currency_to->conversion_rate;
            } else {
                $conversion_rate = ($currency_from->conversion_rate == 0 ? 1 : $currency_from->conversion_rate);

                // Convert amount to default currency (using the old currency rate)
                $amount = Tools::ps_round($amount / $conversion_rate, 2);

                // Convert to new currency
                $amount *= $currency_to->conversion_rate;
            }
            return Tools::ps_round($amount, 2);
        }
    }
    
    public static function createAddOrderHistory($idOrder, $status)
    {
        $order_history = new OrderHistory();
        $order_history->id_order = $idOrder;
        $order_history->changeIdOrderState($status, $idOrder);
        $order_history->addWithemail();
    
        return true;
    }
}
