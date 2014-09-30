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
include_once dirname(__FILE__) . '/../util/util.php';
include_once dirname(__FILE__) . '/pagseguromoduleconfigurable.php';

class PagSeguroPS14 implements PagSeguroModuleConfigurable
{

    private $context;
    private $params = "";

    public function installConfiguration()
    {
        /** For 1.4.3 and less compatibility */
        $updateConfig = Util::getUpdateConfigVersion14();
        
        foreach ($updateConfig as $u => $v) {
            if (! Configuration::get($u) || (int) Configuration::get($u) < 1) {
                if (defined('_' . $u . '_') && (int) constant('_' . $u . '_') > 0) {
                    Configuration::updateValue($u, constant('_' . $u . '_'));
                } else {
                    Configuration::updateValue($u, $v);
                }
            }
        }
        
        return true;
    }

    public function uninstallConfiguration()
    {
        return true;
    }

    public function paymentConfiguration($params)
    {
        include_once dirname(__FILE__).'/../../../../init.php';
        $this->params = $params;
        $this->context = Context::getContext();

        $this->context->smarty->assign(
            array(
                'version_module' => _PS_VERSION_,
                'action_url' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/pagseguro/standard/front/payment.php',
                'image' => __PS_BASE_URI__ . 'modules/pagseguro/assets/images/logops_86x49.png',
                'this_path' => __PS_BASE_URI__ . 'modules/pagseguro/',
                'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/pagseguro/'
            )
        );
    }

    public function returnPaymentConfiguration($params)
    {
        include_once dirname(__FILE__).'/../../../../init.php';
        $this->context = Context::getContext();
        
        if (! Tools::isEmpty($params['objOrder']) && $params['objOrder']->module === 'pagseguro') {
            
            $this->context->smarty->assign(
                array(
                    'total_to_pay' => Tools::displayPrice(
                        $params['objOrder']->total_paid_real,
                        $this->context->currency->id,
                        false
                    ),
                    'status' => 'ok',
                    'id_order' => (int) $params['objOrder']->id
                )
            );
            
            if (isset($params['objOrder']->reference) && ! Tools::isEmpty($params['objOrder']->reference)) {
                $this->context->smarty->assign('reference', $params['objOrder']->reference);
            }
        } else {
            $this->context->smarty->assign('status', 'failed');
        }
    }
    
    public function getNotificationUrl()
    {
        return Tools:: isEmpty(Util::getNotificationUrl()) ?
            Util::getDefaultNotificationUrlPS14() :
            Util::getNotificationUrl();
    }
    
    public function getDefaultRedirectionUrl()
    {
        return Tools:: isEmpty(Util::getRedirectUrl()) ? Util::getDefaultRedirectUrlPS14() : Util::getRedirectUrl();
    }
    
    public function getCssDisplay()
    {
        return Util::getCssDisplayPS14();
    }
    
    public function getJsBehaviors()
    {
        return Util::getJsBehaviorPS14();
    }
}
