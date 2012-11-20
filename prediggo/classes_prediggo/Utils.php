<?php

require_once "Pair.php";

/**
 * Description of Utils
 *
 * @package prediggo4php
 * @subpackage tools
 *
 * @author Stef
 */
class Utils
{


    /**
     * Null safe, blank safe method for adding string objects to a collection. Won't add an already existing value.
     * @param array &$array An array of string
     * @param string $value the value to add.
     */
    public static function addStringToUniqueArray( &$array, $value )
    {
        if ( ! isset($value) )
             return;

        $value = trim( $value );

        if (  $value == "" || in_array($value, $array))
            return;

        $array[] = $value;
    }


    /**
     * Null safe, blank safe method for adding Pair objects to a collection. Won't add an already existing value.
     * @param array &$array An array of pairs
     * @param string $first the first element of the pair to add.
     * @param string $second the second element of the pair to add.
     */
    public static function addPairToUniqueArray( &$array, $first, $second )
    {
        if ( ! isset($first) || !isset($second) )
             return;

        if( is_string($first) )
        {
            $first = trim($first);
            if( empty ($first))
                return;
        }

        if( is_string($second) )
        {
            $second = trim($second);
            if( empty ($second))
                return;
        }

        $value = new Pair($first, $second);

        if ( in_array($value, $array))
            return;

        $array[] = $value;
    }


    /**
      * Transform a collection of pairs into two elements (one for keys and one for values) separated by a given separator.
      * Results are affected to the two variables given in parameter.
      * @param array $collection a collection of key value pairs
      * @param string $separator the separator string
      * @param string &$refStringFirst the string ref which will contains the concatened keys
      * @param string &$refStringSecond the string ref which will contains the concatened values
      */
     public static function implodeKeyValuePairsToSeparatedString( $collection, $separator, &$refStringFirst, &$refStringSecond )
     {
         $arrayFirst = array();
         $arraySecond = array();

         foreach($collection as $pair  )
         {
             $arrayFirst[] = $pair->getFirst();
             $arraySecond[] = $pair->getSecond();
         }

         $refStringFirst = implode( $separator,  $arrayFirst);
         $refStringSecond = implode( $separator,  $arraySecond);

     }
    
}

