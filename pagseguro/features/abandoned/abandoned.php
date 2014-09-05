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

include_once dirname(__FILE__) . '/../../../../config/config.inc.php';
include_once dirname(__FILE__) . '/../../features/PagSeguroLibrary/PagSeguroLibrary.php';
include_once dirname(__FILE__) . '/../../features/util/encryptionIdPagSeguro.php';
include_once dirname(__FILE__) . '/../../features/util/util.php';

$abandoned = new PagSeguroAbandoned();

if (Tools::getValue('getAbandoned'))
{
    echo Tools::jsonEncode($abandoned->getTableResult());
}

class PagSeguroAbandoned
{

    private $objCredential = "";

    private $errorMsg = array();

    private $tableResult = "";

    private $idStatusPagseguro;
    
    private $idLang = "";
    
    private $idInitiatedState;
    
    public function __construct()
    {

        foreach (Language::getLanguages(false) as $language) {
            if (strcmp($language["iso_code"], 'br') == 0) {
                $this->idLang = $language["id_lang"];
            } else if (strcmp($language["iso_code"], 'en') == 0) {
                $this->idLang = $language["id_lang"];
            }
        }
        
        $order_state = OrderState::getOrderStates($this->idLang);
        foreach ($order_state as $value) {
            if (strcmp($value["name"], Util::getStatusCMS(0)) == 0) {
                $this->idInitiatedState = $value["id_order_state"];
            }
        }
    }

    public function getTableResult()
    {
        $this->setObjCredential();
        $tableResult = $this->getTable();
        $this->createLog();
        return $tableResult;
    }

    private function getTable()
    {

        try {

            $abandonedOrders = array();
            
            if (!$this->errorMsg) {
            
                $listOfAbandoned = $this->getAbandoned();

                if (is_array($listOfAbandoned->getTransactions())) {
                    
                    foreach ($listOfAbandoned->getTransactions() as $value) {
                        
                        $helper = array();
                        
                        $create_date_order_pagseguro = date("d/m/Y", strtotime($value->getDate()));
                        list($day, $month, $year) = explode('/', $create_date_order_pagseguro);
    
                        $expiration_date = date(
                            "d/m/Y",
                            mktime('0', '0', '0', $month, $day + 10, $year)
                        );
                        
                        $params = array();
                        $params['reference'] = $value->getReference();
                        $params['data_expired'] = $expiration_date;

                        if ($this->validateOrderAbandoned($params)) {

                                $helper['data_expired'] = $expiration_date;
    
                                $reference = ((int)EncryptionIdPagSeguro::decrypt($value->getReference()));
                                $helper['reference'] = $reference;
                                $helper['masked_reference'] = sprintf("#%06s",$reference);
                                
                                $order = new Order($reference);
                                $helper['data_add_cart'] = date("d/m/Y H:i", strtotime($order->date_add));
                                $helper['customer'] = $order->id_customer;
    
                                $recoveryCode = $value->getRecoveryCode();
                                $helper['recovery_code'] = $recoveryCode;
        
                                array_push($abandonedOrders, $helper);
                        }
                    }
                }
            }
        } catch (PagSeguroServiceException $e) {
            array_push($this->errorMsg, Tools::displayError($e->getOneLineMessage()));
        } catch (Exception $e) {
            array_push($this->errorMsg, Tools::displayError($e->getMessage()));
        }

        $this->tableResult = $abandonedOrders;

        return array('tableResult' => $this->tableResult,'errorMsg' => $this->errorMsg, 'day_recovery' => Configuration::get('PAGSEGURO_DAYS_RECOVERY'), 'abandoned_orders' => $abandonedOrders, 'is_recovery_cart' => Configuration::get('PAGSEGURO_RECOVERY_ACTIVE'));
    }

    private function getAbandoned()
    { 
        
        $now = date('Y-m-d H:i:s');
        
        list($year, $month, $day) = explode('-', $now);
        list($hour, $minutes, $seconds) = explode(':', $now);
        $hour = explode(" ",$hour);
        $initialDay = date(DATE_ATOM, mktime($hour[1], $minutes, $seconds, $month, $day - Configuration::get('PAGSEGURO_DAYS_RECOVERY'), $year));

        return PagSeguroTransactionSearchService::searchAbandoned($this->objCredential, 1, 1000, $initialDay);

    }

    public function validateOrderAbandoned($params)
    {
        
        if (strpos($params['reference'], Configuration::get('PAGSEGURO_ID')) !== false) {

            $initiated = Util::getStatusCMS(0);
            $order_state = OrderHistory::getLastOrderState(((int)EncryptionIdPagSeguro::decrypt($params['reference'])));
            if (strcmp($order_state->name, $initiated) != 0) {
                return false;
            }

        } else {
            return false;
        }
        
        return true;
    }

    private function setObjCredential()
    {
        $email = Configuration::get('PAGSEGURO_EMAIL');
        $token = Configuration::get('PAGSEGURO_TOKEN');
        if (!empty($email) && !empty($token)) {
            $this->objCredential = new PagSeguroAccountCredentials($email, $token);
        } else {
            $this->errorMsg = true;
        }
    }

     /****
     *
     * Create Log
     * @param array $dados;
     */

    public function createLog()
    {

        /*** Retrieving configurated default charset */
        PagSeguroConfig::setApplicationCharset(Configuration::get('PAGSEGURO_CHARSET'));

        /*** Retrieving configurated default log info */
        if (Configuration::get('PAGSEGURO_LOG_ACTIVE')) {
            PagSeguroConfig::activeLog(_PS_ROOT_DIR_ . Configuration::get('PAGSEGURO_LOG_FILELOCATION'));
        }
                
        LogPagSeguro::info(
            "PagSeguroAbandoned.Search( 'Pesquisa de transações abandonadas realizada em " . date("d/m/Y H:i") . ".')"
        );


    }

}
