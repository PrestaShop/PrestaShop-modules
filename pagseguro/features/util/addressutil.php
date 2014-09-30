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

class AddressUtil
{

    public static function data($v)
    {
        $data = array();
        $data['complementos'] = array(
            "casa",
            "ap",
            "apto",
            "apart",
            "frente",
            "fundos",
            "sala",
            "cj"
        );
        $data['brasilias'] = array(
            "bloco",
            "setor",
            "quadra",
            "lote"
        );
        $data['naobrasilias'] = array(
            "av",
            "avenida",
            "rua",
            "alameda",
            "al.",
            "travessa",
            "trv",
            "praça",
            "praca"
        );
        $data['sems'] = array(
            "sem ",
            "s.",
            "s/",
            "s. ",
            "s/ "
        );
        $data['numeros'] = array(
            'n.º',
            'nº',
            "numero",
            "num",
            "número",
            "núm",
            "n"
        );
        $data['semnumeros'] = array();
        foreach ($data['numeros'] as $n) {
            foreach ($data['sems'] as $s) {
                $data['semnumeros'][] = "$s$n";
            }
        }
        return $data[$v];
    }

    public static function endtrim($e)
    {
        return preg_replace('/^\W+|\W+$/', '', $e);
    }

    public static function treatAddress($end)
    {

        $address = $end;
        $number = 's/nº';
        $complement = '';
        $district = '';
        
        $token = preg_split("/[-,\\n]/", $end);
        
        if (sizeof($token) == 4) {
            list ($address, $number, $complement, $district) = $token;
        } elseif (sizeof($token) == 3) {
            list ($address, $number, $complement) = $token;
        } elseif (sizeof($token) == 2) {
            list ($address, $number, $complement) = self::sortData($end);
        } else {
            $address = $end;
        }
        
        return array(
            self::endtrim(Tools::substr($address, 0, 69)),
            self::endtrim($number),
            self::endtrim($complement),
            self::endtrim($district)
        );
    }

    public static function sortData($text)
    {
        $token = preg_split('/[-,\\n]/', $text);

        for ($i = 0; $i < Tools::strlen($token[0]); $i ++) {
            if (is_numeric(Tools::substr($token[0], $i, 1))) {
                return array(
                    Tools::substr($token[0], 0, $i),
                    Tools::substr($token[0], $i),
                    $token[1]
                );
            }
        }
        
        $text = preg_replace('/\s/', ' ', $text);
        $textlen = Tools::strlen($text);
        $find = Tools::substr($text, - $textlen);
        for ($i = 0; $i < Tools::strlen($text); $i ++) {
            if (is_numeric(Tools::substr($find, $i, 1))) {
                return array(
                    Tools::substr($text, 0, - Tools::strlen($text) + $i),
                    Tools::substr($text, - Tools::strlen($text) + $i),
                    ''
                );
            }
        }
    }
}
