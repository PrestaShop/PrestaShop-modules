<?php if (!defined('PAGSEGURO_LIBRARY')) { die('No direct script access allowed'); }

/*
 * ***********************************************************************
  Copyright [2011] [PagSeguro Internet Ltda.]

  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
 * ***********************************************************************
 */

/**
 * Helper functions
 */
class PagSeguroHelper {

    public static function formatDate($date) {
        $format = "Y-m-d\TH:i:sP";
        if ($date instanceof DateTime) {
            $d = $date->format($format);
        } elseif (is_numeric($date)) {
            $d = date($format, $date);
        } else {
            $d = (String) "$date";
        }
        return $d;
    }

    public static function decimalFormat($numeric) {
        if (is_float($numeric)) {
            $numeric = (float) $numeric;
            $numeric = (string) number_format($numeric, 2, '.', '');
        }
        return $numeric;
    }

    public static function subDays($date, $days) {
        $d = self::formatDate($date);
        $d = date_parse($d);
        $d = mktime($d['hour'], $d['minute'], $d['second'], $d['month'], $d['day'] - $days, $d['year']);
        return self::formatDate($d);
    }

    public static function print_rr($var, $dump = null) {
        if (is_array($var) || is_object($var)) {
            echo "<pre>";
            if ($dump) {
                var_dump($var);
            } else {
                print_r($var);
            }
            echo "</pre>";
        }
    }

    /**
     * Remove left, right and inside extra spaces in string
     * @param string $string
     * @return string
     */
    public static function removeStringExtraSpaces($string){
        return trim(preg_replace("/( +)/", " ", $string));
    }
    
    /**
     * Perform truncate of string value
     * @param string $string
     * @param type $limit
     * @param type $endchars
     * @return string
     */
    public static function truncateValue($string, $limit, $endchars = '...'){
        
        if (!is_array($string) && !is_object($string)){
            
            $stringLength = strlen($string);
            $endcharsLength  = strlen($endchars);
            
            if ($stringLength > (int)$limit){
                $cut = (int)($limit - $endcharsLength);
                $string = substr($string, 0, $cut).$endchars;
            }
        }
        return $string;
    }
    
    /**
     * Return formatted string to send in PagSeguro request
     * @param type $string
     * @param type $limit
     * @param type $endchars
     * @return type
     */
    public static function formatString($string, $limit, $endchars = '...'){
        $string = PagSeguroHelper::removeStringExtraSpaces($string);
        return PagSeguroHelper::truncateValue($string, $limit, $endchars);
    }

    /**
     * Check if var is empty
     * @param string $value
     * @return boolean
     */
    public static function isEmpty($value){
        return (!isset($value) || trim($value) == "" );
    }
    
    /**
     * Check if notification post is empty
     * @param array $notification_data
     * @return type
     */
    public static function isNotificationEmpty(Array $notification_data){
        $isEmpty = TRUE;
        
        if (isset($notification_data['notificationCode']) && isset($notification_data['notificationType'])){
            $isEmpty = (PagSeguroHelper::isEmpty($notification_data['notificationCode']) || PagSeguroHelper::isEmpty($notification_data['notificationType']));
        }
        
        return $isEmpty;
    }
}

?>