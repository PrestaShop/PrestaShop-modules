<?php
/**
 * 2007-2014 [PagSeguro Internet Ltda.]
 *
 * NOTICE OF LICENSE
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 *
 *  @author    PagSeguro Internet Ltda.
 *  @copyright 2007-2014 PagSeguro Internet Ltda.
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

include_once dirname(__FILE__).'/../../../../config/config.inc.php';
include_once dirname(__FILE__).'/../../../../init.php';
include_once dirname(__FILE__).'/../../pagseguro.php';

foreach (Language::getLanguages(false) as $language) {
    if (strcmp($language["iso_code"], 'br') == 0) {
        $idLang = $language["id_lang"];
    }
}

$recovery = "";
$ajaxRequest = Tools::getValue('action') ;
$pagseguro = new PagSeguro();

switch ($ajaxRequest) {

    case 'singleemail':

        $recoveryCode = Tools::getValue('recovery');
        $idCustomer = Tools::getValue('customer');

        $customer = new Customer((int)($idCustomer));

        $orderMessage = OrderMessage::getOrderMessages($idLang);
        $template = '';
        $message = '';
        foreach ($orderMessage as $key => $value) {
            if (strcmp($value["id_order_message"], Configuration::get('PAGSEGURO_MESSAGE_ORDER_ID')) == 0) {
                $template = $value['name'];
                $message = $value['message'];
            }
        }

        $params = array(
            '{message}' =>  $message,
            '{link}' => '<a href="https://pagseguro.uol.com.br/checkout/v2/resume.html?r='.$recoveryCode
            .'" target="_blank"> Clique aqui para continuar sua compra </a>'
        );

        $isSend = @Mail::Send(
            $idLang,
            'recovery_cart',
            $template,
            $params,
            $customer->email,
            $customer->firstname.' '.$customer->lastname,
            null,
            null,
            null,
            null,
            _PS_ROOT_DIR_ . '/modules/pagseguro/mails/',
            true
        );

        if ($isSend) {
            echo '<div class="module_confirmation conf confirm" '.Util::getWidthVersion(_PS_VERSION_).' ">'
                . $pagseguro->l('Email enviado com sucesso') . '</div>';
        } else {
            echo '<div class="module_error alert error" '.Util::getWidthVersion(_PS_VERSION_).' ">'
                . $pagseguro->l('Falha ao enviar email') . '</div>';
        }
        break;
    case 'multiemails':

        $emails = Tools::getValue('send_emails');

        $orderMessage = OrderMessage::getOrderMessages($idLang);
        $template = '';
        $message = '';
        foreach ($orderMessage as $key => $value) {
            if (strcmp($value["id_order_message"], Configuration::get('PAGSEGURO_MESSAGE_ORDER_ID')) == 0) {
                $template = $value['name'];
                $message = $value['message'];
            }
        }

        foreach ($emails as $key => $value) {

            parse_str($value);

            $customer = new Customer((int)($customer));

            $params = array(
                '{message}' =>  $message,
                '{link}' => '<a href="https://pagseguro.uol.com.br/checkout/v2/resume.html?r='.$recovery
                .'" target="_blank"> Clique aqui para continuar sua compra </a>'
            );

            $isSend = @Mail::Send(
                $idLang,
                'recovery_cart',
                $template,
                $params,
                $customer->email,
                $customer->firstname.' '.$customer->lastname,
                null,
                null,
                null,
                null,
                _PS_ROOT_DIR_ . '/modules/pagseguro/mails/',
                true
            );

            if (!$isSend) {
                echo '<div class="module_error alert error" '.Util::getWidthVersion(_PS_VERSION_).' ">'
                    . $pagseguro->l('Falha ao enviar email') . '</div>';
                die();
            }
        }

        echo Tools::jsonEncode(
                array('divError' => '<div class="module_confirmation conf confirm" ' . Util::getWidthVersion(_PS_VERSION_).' ">'. $pagseguro->l('Emails enviados com sucesso') . '</div>' )
            );

        break;
}
