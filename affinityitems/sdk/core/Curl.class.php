<?php
/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future. If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

class Curl {
    
    protected $returnErrors;

    public function __construct($preturnErrors) {
        $this->returnErrors = $preturnErrors;
    }
    
    public function head($url, $vars = array()) {
        return $this->request('HEAD', $url, $vars);
    }

    public function post($url, $vars = array()) {
        return $this->request('POST', $url, $vars);
    }

    public function put($url, $vars = array()) {
        return $this->request('PUT', $url, $vars);
    }

    public function delete($url, $vars = array()) {
        return $this->request('DELETE', $url, $vars);
    }
    
    public function get($url, $vars = array()) {
        if (!empty($vars)) {
            $url .= (stripos($url, '?') !== false) ? '&' : '?';
            $url .= (is_string($vars)) ? $vars : http_build_query($vars, '', '&');
        }
        return $this->request('GET', $url);
    }

    public function getHeaders() {
        $headers = array(
        "Content-type: application/json; charset=utf-8");
        $security = "securityKey: " . AEAdapter::getSecurityKey(); 
        array_push($headers, $security);
        return $headers;
    }

    public function request($method, $url, $vars = array()) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, AEAdapter::getHost().':'.AEAdapter::getPort().'/site/'.AEAdapter::getSiteId().$url);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 1000);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        if(!empty($vars)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, Tools::jsonEncode($vars));
        }

        $return = curl_exec($curl);
        curl_close($curl);

        if($ret = Tools::jsonDecode($return)) {
            if($ret->_ok == "true") {
                return $ret;
            } else {
                AELogger::log("[ERROR]", $ret->_errorCode . " : " . $ret->_errorMessage);
                if($this->returnErrors) {
                    return $ret;
                }
            }
        }
        return false;
    }
    
}