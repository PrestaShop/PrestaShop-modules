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

include_once dirname(__FILE__) . '/../PagSeguroLibrary/PagSeguroLibrary.php';

class PagSeguroNotificationOrderPrestashop
{

    private $obj_transaction;

    private $obj_notification_type;

    private $obj_credential;

    private $notification_type;

    private $notification_code;

    private $reference;

    public function postProcess($post)
    {

        try {

            $this->createNotification($post);
            $this->createCredential();
            $this->inicializeObjects();

            if ($this->obj_notification_type->getValue() == $this->notification_type) {
                $this->createTransaction();
            }

            if ($this->obj_transaction) {
                $this->updateCms();
            }

        } catch (Exception $e) {

            $this->createLog($e->getMessage());
        }

    }

    private function createNotification(Array $post)
    {
        $this->notification_type = (isset($post['notificationType']) && trim($post['notificationType']) !== '' ?
            trim($post['notificationType']) : null);

        $this->notification_code = (isset($post['notificationCode']) && trim($post['notificationCode']) !== '' ?
            trim($post['notificationCode']) : null);
    }

    private function createCredential()
    {
        $email = Configuration::get('PAGSEGURO_EMAIL');
        $token = Configuration::get('PAGSEGURO_TOKEN');
        $this->obj_credential = new PagSeguroAccountCredentials($email, $token);
    }

    private function inicializeObjects()
    {
        $this->createNotificationType();
    }

    private function createNotificationType()
    {
        $this->obj_notification_type = new PagSeguroNotificationType();
        $this->obj_notification_type->setByType('TRANSACTION');
    }

    private function createTransaction()
    {
        $this->obj_transaction = PagSeguroNotificationService::checkTransaction(
            $this->obj_credential,
            $this->notification_code
        );

        $transaction = $this->isNotNull($this->obj_transaction);

        if (strpos($this->obj_transaction->getReference(), Configuration::get('PAGSEGURO_ID')) === false) {
            throw new Exception("ID_PAGSEGURO_INCOMPATIVEL", 1);
        }

        $this->reference = $transaction ? (int)EncryptionIdPagSeguro::decrypt($this->obj_transaction->getReference()) : null;
    }

    private function updateCms()
    {

        $id_status = ($this->isNotNull($this->obj_transaction->getStatus()->getValue())) ?
        (int) $this->obj_transaction->getStatus()->getValue() : null;

        if ($this->isNotNull($id_status)) {
            $id_st_transaction = (int) $this->returnIdOrderByStatusPagSeguro(Util::getStatusCMS($id_status));
        }

        if ($this->isNotNull($id_st_transaction)) {
            Util::createAddOrderHistory((int)$this->reference, $id_st_transaction);
        }

        $this->saveTransactionId($this->obj_transaction->getCode(), $this->decryptId($this->obj_transaction->getReference()));
    }

    private function returnIdOrderByStatusPagSeguro($value)
    {

        $isDeleted = version_compare(_PS_VERSION_, '1.5.0.3', '>') ? ' WHERE deleted = 0' : '';

        $sql = 'SELECT distinct os.`id_order_state`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            INNER JOIN `' . _DB_PREFIX_ .
            'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`name` = \''
                . pSQL($value) . '\' and os.id_order_state <> 6)' . $isDeleted;

        $id_order_state = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));

        return $id_order_state[0]['id_order_state'];
    }

    private function isNotNull($value)
    {
        return isset($value);
    }

    private function saveTransactionId($transaction, $reference)
    {
        $sql = "SELECT `id` FROM `" . _DB_PREFIX_ . "pagseguro_order` WHERE `id_order` = $reference";

        $pagseguro_order = Db::getInstance()->getRow($sql);

        if ($pagseguro_order['id']) {
            $this->updateOrder($reference, $transaction, $pagseguro_order['id']);
        } else {
            $this->saveOrder($reference, $transaction);
        }
    }

    private function saveOrder($id_order, $transaction)
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'pagseguro_order` (`id_transaction`, `id_order`)
                VALUES (\'' . pSQL($transaction) . '\', \'' . (int) $id_order . '\')';

        if (! Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($sql)) {
            die(Tools::displayError('Error when updating Transaction Code from PagSeguro in database'));
        }
    }

    private function updateOrder($id_order, $transaction, $pagseguro_order)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'pagseguro_order`
        SET `id_transaction` = \'' . pSQL($transaction) . '\',
        `id_order` = \'' . (int) $id_order . '\'
        WHERE `id` = \'' . (int) $pagseguro_order . '\';';

        if (! Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($sql)) {
            die(Tools::displayError('Error when updating Transaction Code from PagSeguro in database'));
        }
    }

    private function createLog($e)
    {
        /** Retrieving configurated default charset */
        PagSeguroConfig::setApplicationCharset(Configuration::get('PAGSEGURO_CHARSET'));

        /** Retrieving configurated default log info */
        if (Configuration::get('PAGSEGURO_LOG_ACTIVE')) {
            PagSeguroConfig::activeLog(_PS_ROOT_DIR_ . Configuration::get('PAGSEGURO_LOG_FILELOCATION'));
        }

        LogPagSeguro::info(
            "PagSeguroService.Notification( 'Erro ao processar notificação. ErrorMessage: ".$e." ') - end"
        );
    }
    
    /****
     *
     * Grab a PAGSEGURO_ID and decrypts
     * @param string $reference
     * @return PAGSEGURO_ID
     */
    private function decryptId($reference)
    {
    	return Tools::substr($reference, 5);
    }
}
