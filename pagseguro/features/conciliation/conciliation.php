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

include_once dirname(__FILE__) .
        '/../../../../config/config.inc.php';
include_once dirname(__FILE__) .
        '/../../features/PagSeguroLibrary/PagSeguroLibrary.php';
include_once dirname(__FILE__) .
        '/../../features/validation/pagsegurovalidateorderprestashop.php';
include_once dirname(__FILE__) .
        '/../../features/PagSeguroLibrary/domain/PagSeguroTransactionSearchResult.class.php';
include_once dirname(__FILE__) .
        '/../../features/PagSeguroLibrary/domain/PagSeguroTransactionStatus.class.php';

$conciliation = new PagSeguroConciliation();

if (Tools::getValue('idOrder')) {

    $pagSeguroStatus = Util::getStatusCMS(Tools::getValue('newIdStatus'));
    $status_id = $conciliation->getPestashopOrderStatusId($pagSeguroStatus);

    $data = array ("idOrder" => Tools::getValue('idOrder'),
                   "newStatus" => $pagSeguroStatus,
                   "newIdStatus" => Tools::getValue('newIdStatus'));

    $conciliation->createLog('register',$data);

    if (Tools::getValue('orderDays') != 1)
        $conciliation->getDays(Tools::getValue('orderDays'));

    $conciliation->updateStatus(Tools::getValue('idOrder'), $status_id);

    echo Tools::jsonEncode( $conciliation->getTableArrayResult() );


} elseif (Tools::getValue('dias')) {


    $data = array ('days' => Tools::getValue('dias'));

    $conciliation->createLog('search',$data);

    $conciliation->getDays(Tools::getValue('dias'));

    echo Tools::jsonEncode( $conciliation->getTableArrayResult() );


} else {

    return $conciliation->getTableResult();

}

class PagSeguroConciliation
{

    private $obj_credential = "";
    private $errorMsg = false;
    private $regError = false;
    private $tableResult = "";
    private $daysRange = 0;
    private $idStatusPagseguro;
    private $fullArray = array();  
    private $counter = 1;  
    private $order_state = "";

    /****
    *
    * Getters and Setter
    */
    public function getTableResult()
    {

        $this->setObjCredential();
        $tableResult = $this->setTableResults();
        return $tableResult;

    }

    public function getTableArrayResult()
    {
        $this->counter = 0;     
        $this->setObjCredential(); 
        $this->setTableResults(true);;
        return $this->fullArray;

    }

    public function setTableResults($flow = false)
    {       

        if (!$this->regError) {

            if ($this->getPrestashopPaymentList()) {

                $paymentPagSeguro = $this->getPagSeguroPaymentsList();

                if ($paymentPagSeguro) {

                    foreach ($this->getPrestashopPaymentList() as $row) {

                        $row['status_pagseguro'] = '';
                        $row['id_status_pagseguro'] = '';
                        $row['id_pagseguro'] = '';

                        foreach ($paymentPagSeguro as $value) {

                            if ($row['id_order'] == $this->decryptId($value['reference'])) {

                                $row['id_pagseguro'] = $value['code'];
                                $row['id_status_pagseguro'] = $value['status'];
                                $row['status_pagseguro'] = Util::getStatusCMS($value['status']);

                                if ($this->verifyVersion() === false) {

                                    if ($flow) {
                                        $this->createArray(
                                            $row['id_order'],
                                            $row['id_order_state'],
                                            $row['status_pagseguro'],
                                            $this->dateToBr($row['date_add']),
                                            $this->getPrestashopStatus($row['current_state']),
                                            $row['id_pagseguro'],
                                            $row
                                        );
                                    } else {   
                                        $this->createTables(
                                            $row['id_order'],
                                            $row['id_order_state'],
                                            $row['status_pagseguro'],
                                            $this->dateToBr($row['date_add']),
                                            $this->getPrestashopStatus($row['current_state']),
                                            $row['id_pagseguro'],
                                            $row
                                        );
                                    }

                                } else {

                                    if ($flow) {
                                        $this->createArray(
                                            $row['id_order'],
                                            $row['id_order_state'],
                                            $row['status_pagseguro'],
                                            $this->dateToBr($row['date_add']),
                                            $this->getPrestashopStatus($row['id_order_state']),
                                            $row['id_pagseguro'],
                                            $row
                                        );
                                    } else {     
                                        $this->createTables(
                                            $row['id_order'],
                                            $row['id_order_state'],
                                            $row['status_pagseguro'],
                                            $this->dateToBr($row['date_add']),
                                            $this->getPrestashopStatus($row['id_order_state']),
                                            $row['id_pagseguro'],
                                            $row
                                        );
                                    }

                                }
                            }

                        }

                    }

                } else {
                    $this->errorMsg = true;
                }
            } else {
                $this->errorMsg = true;
            }

        } 

        return array('tabela' => $this->tableResult,'errorMsg' => $this->errorMsg, 'regError' => $this->regError );
    }

    /****
    *
    * Set credentials account(e-mail) and token.
    */
    private function setObjCredential()
    {
        $email = Configuration::get('PAGSEGURO_EMAIL');
        $token = Configuration::get('PAGSEGURO_TOKEN');

        if (!empty($email) && !empty($token)) {
            $this->obj_credential = new PagSeguroAccountCredentials($email, $token);
        } else {
            $this->regError = true;
        }
    }

    /****
    *
    * Get a list of payments.
    * Methods: getPagSeguroPaymentsList(); getToken(); decrypt(); validateRef();
    *
    */
    private function getPagSeguroPaymentsList()
    {

        $pageNumber = 1;
        $maxPageResults = 1000;

        $timeZone = date_default_timezone_get();
        date_default_timezone_set('America/Sao_Paulo');

        $finalDate = date("Y-m-d")."T".date("H:i");

        date_default_timezone_set($timeZone);

        if ($this->daysRange == 0) {

                $initialDate = $this->subDayIntoDate($finalDate, 0);

        } else {

                $initialDate = $this->subDayIntoDate($finalDate, $this->daysRange);
        }

        $results = array();
        try {

                $result = PagSeguroTransactionSearchService::searchByDate(
                    $this->obj_credential,
                    $pageNumber,
                    $maxPageResults,
                    $initialDate,
                    $finalDate
                );

                $pageNumber = $result->getTotalPages();

                if ($result->getTotalPages() > 1) {
                    
                    for ($i = 1; $i <= $pageNumber; $i++) {

                        $results[] = PagSeguroTransactionSearchService::searchByDate(
                            $this->obj_credential,
                            $i,
                            $maxPageResults,
                            $initialDate,
                            $finalDate
                        ); 
                    }

                    $newArray = new ArrayObject();
                    for ($i = 0; $i < count($results); $i++) {

                        $nResult = $results[$i];
                        foreach ($nResult->getTransactions() as $item) {
                            $newArray['transactions'][] = $item;
                        }
                    }

                    $result = $newArray;
                    
                } else {
                    
                    for ($i = 0; $i < count($result); $i++) {
                    
                    	if (count($result) > 1)
                    		$nResult = $result[$i];
                    	else
                    		$nResult = $result;
                    
                    	$newArray = new ArrayObject();
                    	foreach ($nResult->getTransactions() as $item) {
                    		$newArray['transactions'][] = $item;
                    	}
                    }
                    
                    $result = $newArray;
                }
                
                $return = $this->validateRef($result);

        } catch (PagSeguroServiceException $e) {
                $return = false;
        }

        return $return;

    }

    /****
    *
    * checks if the PAGSEGURO_ID is the same and returns the related transactions
    * @param PagSeguroTransactionSearchResult $result
    * @param counter $n
    */
    public function validateRef(ArrayObject $result, $n = 0)
    {

        $prestashopTransactions = array();
        $transactions = $result['transactions'];

        if (!empty($transactions)){
            foreach ($transactions as $transactionSummary) {

                        $decrypt = $this->decrypt($transactionSummary->getReference());

                if ($this->getToken() == $decrypt) {

                                $prestashopTransactions[$n]['code'] = $transactionSummary->getCode();
                                $prestashopTransactions[$n]['reference'] = $transactionSummary->getReference();
                                $prestashopTransactions[$n++]['status'] = $transactionSummary->getStatus()->getValue();

                }

            }
        }   

        if (!isset($prestashopTransactions)) {
            $prestashopTransactions = false;
        }

        return $prestashopTransactions;

    }

    /****
    *
    * Grab a PAGSEGURO_ID and decrypts
    * @param string $reference
    */
    private function decrypt($reference)
    {

            return Tools::substr($reference, 0, 5);

    }

    /****
    *
    * Return PagSeguro ID
    */
    private function getToken()
    {

            $query = 'SELECT c.`name`, c.`value`
                             FROM `'._DB_PREFIX_.'configuration` c
                             WHERE c.`name` = "PAGSEGURO_ID"';

            $result = Db::getInstance()->executeS($query);

            return $result[0]['value'];

    }

        /****
        *
        *  Return Prestashop payment list
        *
        */
    private function getPrestashopPaymentList()
    {
        if ($this->verifyVersion() === false) {

                    $query = 'SELECT
                        psord.`id_order`,
                        psord.`date_add`,
                        psord.`current_state`,
                        osl.`name`,
                        oh.`id_order_state`,
                        (SELECT COUNT(od.`id_order`) FROM `'._DB_PREFIX_.'order_detail` od
                                WHERE od.`id_order` = psord.`id_order`
                                GROUP BY `id_order`) AS product_number

                      FROM `'._DB_PREFIX_.'orders` AS psord
                            LEFT JOIN `'._DB_PREFIX_.'order_history` oh
                                ON (oh.`id_order` = psord.`id_order`)
                            LEFT JOIN `'._DB_PREFIX_.'order_state` os
                                ON (os.`id_order_state` = oh.`id_order_state`)
                            LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl
                                ON (os.`id_order_state` = osl.`id_order_state`)

                     WHERE oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_
                            .'order_history` moh
                        WHERE moh.`id_order` = psord.`id_order`
                        GROUP BY moh.`id_order`)
                        AND psord.payment = "PagSeguro"
                        AND osl.`id_lang` = psord.id_lang
                        AND psord.date_add >= DATE_SUB(CURDATE(),INTERVAL \''
                            .( Tools::getValue('dias') ? Tools::getValue('dias') : '1').
                        '\' DAY)';

        } else {

                    $query = 'SELECT
                        psord.`id_order`,
                        psord.`date_add`,
                        osl.`name`,
                        oh.`id_order_state`,
                        (SELECT COUNT(od.`id_order`) FROM `'._DB_PREFIX_.'order_detail` od
                                WHERE od.`id_order` = psord.`id_order`
                                GROUP BY `id_order`) AS product_number

                      FROM `'._DB_PREFIX_.'orders` AS psord
                            LEFT JOIN `'._DB_PREFIX_.'order_history` oh
                                ON (oh.`id_order` = psord.`id_order`)
                            LEFT JOIN `'._DB_PREFIX_.'order_state` os
                                ON (os.`id_order_state` = oh.`id_order_state`)
                            LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl
                                ON (os.`id_order_state` = osl.`id_order_state`)

                     WHERE oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_
                            .'order_history` moh
                        WHERE moh.`id_order` = psord.`id_order`
                        GROUP BY moh.`id_order`)
                        AND psord.payment = "PagSeguro"
                        AND osl.`id_lang` = psord.id_lang
                        AND psord.date_add >= DATE_SUB(CURDATE(),INTERVAL \''
                            .(Tools::getValue('dias') ? Tools::getValue('dias') : '1').
                        '\' DAY)';
        }

                $results = Db::getInstance()->ExecuteS($query);
                echo Db::getInstance()->getMsgError();

          return $results;

    }

    private function verifyVersion()
    {

        if (version_compare(_PS_VERSION_, '1.5.0.5', '<')) {
                    $result = true;
        } else {
                    $result = false;
        }
        return $result;
    }

    private function getPrestashopStatus($status)
    {


        $query = $this->getOrderStatusNameById($status);

        $results = Db::getInstance()->executeS($query);

        return $results[0]['name'];

    }

    public function getPestashopOrderStatusId($name)
    {

        $query = $this->getOrderStatusIdByName($name);

        $results = Db::getInstance()->executeS($query);

        return $results[0]['id_order_state'];

    }

    private function getOrderStatusNameById($id)
    {

        $query = 'SELECT osl.`id_order_state`, osl.`name`
            	  FROM `'._DB_PREFIX_.'order_state_lang` osl
            	  WHERE osl.`id_order_state` = '.$id.'';

        return $query;

    }

    private function getOrderStatusIdByName($name)
    {

        $query = 'SELECT osl.`id_order_state`, osl.`name`
                          FROM `'._DB_PREFIX_.'order_state_lang` osl
                          WHERE osl.`name` LIKE "'.$name.'"';

        return $query;

    }

    private function decryptId($reference)
    {

            return Tools::substr($reference, 5);

    }

    private function createTables($id_order, $id_order_state, $status_pagseguro, $date_add, $name, $pagseguro_code, $row)
    {


        $cOrder = $id_order;
        $id_order = sprintf("#%06s", $id_order);

        $this->tableResult .= "<tr class='tabela' id='" .$id_order."' style='font-size: 12px; color:"
                .$this->getColor($id_order_state, $status_pagseguro)."'>";
        $this->tableResult .= "<td style='text-align: center;'> " .$date_add." </td>";
        $this->tableResult .= "<td style='text-align: center;'> " .$id_order." </td>";
        $this->tableResult .= "<td style='text-align: center;'> " .$pagseguro_code ." </td>";
        $this->tableResult .= "<td style='text-align: center;'> " .$name." </td>";
        $this->tableResult .= "<td style='text-align: center;'> ". $status_pagseguro." </td>";
        $this->tableResult .= "<td id='editar'>
        	                        <a onclick='editRedirect(" . $cOrder . ")'
        	                            id='" . $id_order . "' style='cursor:pointer'>
        	                        <img src='../img/admin/edit.gif'
        	                            border='0' alt='edit' title='Editar'/>
        	                        </a>
        	                    </td>";
        $this->tableResult .= "<td id='duplicar'><a onclick='duplicateStatus(".$row['id_order'].","
                .$row['id_status_pagseguro'].",".$row['id_order_state'].")' style='cursor:pointer'> "
                . "<img src='../modules/pagseguro/assets/images/refresh_.png' border='0' alt='Atualizar' title='Atualizar' width='16'/> </a></td>";
        $this->tableResult .= "</tr>";


    }

    private function createArray($id_order, $id_order_state, $status_pagseguro, $date_add, $name, $pagseguro_code, $row)
    {

        $this->order_state = $id_order_state;

        $cOrder = $id_order;
        $id_order = sprintf("#%06s", $id_order);

        if ( $name == $status_pagseguro ) {   


            $img = "<img src='../modules/pagseguro/assets/images/refresh_deactived.png' 
                         border='0' 
                         alt='Atualizar' 
                         title='Atualizar' 
                         width='16'/>";
        } else {

            $img = "<a 
                    onclick='duplicateStatus(".$row['id_order']."," .$row['id_status_pagseguro'].",".$row['id_order_state'].")' 
                    style='cursor:pointer'>
                        <img src='../modules/pagseguro/assets/images/refresh_.png' 
                             border='0' 
                             alt='Atualizar' 
                             title='Atualizar' 
                             width='16' />
                    </a>";
        }

        $array = array(  $date_add,
                         $id_order,
                         $pagseguro_code,
                         $name,
                         $status_pagseguro,
                         "<a onclick='editRedirect(" . $cOrder . ")' id='" . $id_order . "' style='cursor:pointer'>
                                    <img src='../img/admin/edit.gif' border='0' alt='edit' title='Editar'/></a>",
                         $img
            );

       $this->addArray($array);

    }

    private function addArray($array)
    {
            $this->fullArray[$this->counter++] = $array;
    }

    private function getImages($statusPagSeguro, $row)
    {
        $retorno = "<img src='../modules/pagseguro/assets/images/refreshDisabled.png'
                        border='0' alt='edit' title='Modificar'/>
                    ";

        if (empty($statusPagSeguro)) {
            return $retorno;
        }

        foreach ($statusPagSeguro as $status) {
            if ($status['id_order_state'] == $row['id_order_state']) {
                return $retorno;
            }
        }

        $newStatus = empty($statusPagSeguro) ? "" : $statusPagSeguro[count($statusPagSeguro)-1]['id_order_state'];
        $status = $row['status_pagseguro'];

        return "<a onclick='duplicateStatus(
                    " . $row['id_order'] . ",
                    " . $newStatus . ",
                    " . $row['id_order_state'] . ",
                    \" $status \"
                )' style='cursor:pointer'>
                <img src='../modules/pagseguro/assets/images/refresh.png'
                    border='0' alt='edit' title='Modificar'/>
                ";
    }

    private function getPagSeguroState($pagSeguroState, $where = '')
    {
        $sql = 'SELECT distinct os.`id_order_state`
                        FROM `' . _DB_PREFIX_ . 'order_state` os
                        INNER JOIN `' . _DB_PREFIX_ .'order_state_lang` osl ON
                            (os.`id_order_state` = osl.`id_order_state`
                            AND osl.`name` = \''. $pagSeguroState . '\')'
                                            . $where ;

        $id_order_state = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $id_order_state;
    }

    private function where($state)
    {
        $where = "os.id_order_state = " . $state;
        if (version_compare(_PS_VERSION_, '1.5.0.3', '>')) {
            return " WHERE deleted = 0 AND ". $where;
        } else {
            return " WHERE " . $where;
        }
    }

    private function getColor($state, $pagSeguroState)
    {
        $where = $this->where($state);
        $id_order_state = $this->getPagSeguroState($pagSeguroState, $where);

        if ($id_order_state == 0) {
            return 'red';
        }
        foreach ($id_order_state as $id_state) {
            if ($state == $id_state['id_order_state']) {
                return 'green';
            }
        }

        return 'red';
    }

    /****
     *
     * Create Log
     * @param array $dados;
     */

    public function createLog($type, $dados)
    {

        /*** Retrieving configurated default charset */
        PagSeguroConfig::setApplicationCharset(Configuration::get('PAGSEGURO_CHARSET'));

        /*** Retrieving configurated default log info */
        if (Configuration::get('PAGSEGURO_LOG_ACTIVE')) {
            PagSeguroConfig::activeLog(_PS_ROOT_DIR_ . Configuration::get('PAGSEGURO_LOG_FILELOCATION'));
        }

        switch ($type) {
            case 'search':
                
                LogPagSeguro::info(
                    "PagSeguroConciliation.Search( 'Pesquisa de conciliação realizada em " . date("d/m/Y H:i") . " em um intervalo de ".$dados['days']." dias.')"
                );
                break;
            
            default:

                LogPagSeguro::info(
                    "PagSeguroConciliation.Register( 'Alteração de Status da compra '"
                    . $dados['idOrder'] . "' para o Status '" . $dados['newStatus'] . "("
                    . $dados['newIdStatus'] . ")' - '" . date("d/m/Y H:i") . "') - end"
                );
                break;
        }
        
    }

        /****
 	 *
 	 * Update Order Status in Database
 	 * @param $id (int)
 	 * @param $new_status (int)
 	 */
    public function updateStatus($id, $new_status)
    {

        	$objOrder = new Order($id); //order with id=1
            $history = new OrderHistory();
            $history->id_order = (int)$objOrder->id;
            $history->changeIdOrderState($new_status, (int)($objOrder->id));
            $history->addWithemail();

    }

    public function getDays($daysRange)
    {

            $this->daysRange = $daysRange;

    }

    private function subDayIntoDate($date, $days)
    {

            $date = date("Ymd");

            $thisyear = Tools::substr($date, 0, 4);
            $thismonth = Tools::substr($date, 4, 2);
            $thisday = Tools::substr($date, 6, 2);
            $nextdate = mktime(0, 0, 0, $thismonth, $thisday - $days, $thisyear);

            $nData = strftime("%Y-%m-%d", $nextdate);

            return $nData."T00:00";

    }

    private function dateToBr($data)
    {

            $data = date("d/m/Y", strtotime($data));

            return $data;

    }
}
