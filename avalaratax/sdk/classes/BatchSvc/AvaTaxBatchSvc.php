<?php
/**
 * AvaTaxBatchSvc.class.php
 */
 
/**
 * Defines class loading search path.
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Base 
 */
 
function __autoload($class_name) 
{    
    require_once $class_name . '.class.php';
}

function EnsureIsArray( $obj ) 
{
    if( is_object($obj)) 
	{
        $item[0] = $obj;
    } 
	else 
	{
        $item = (array)$obj;
    }
    return $item;
}



/**
* Takes xml as a string and returns it nicely indented
*
* @param string $xml The xml to beautify
* @param boolean $html_output If the xml should be formatted for display on an html page
* @return string The beautified xml
*/

function xml_pretty_printer($xml, $html_output=FALSE)
{
    $xml_obj = new SimpleXMLElement($xml);
    $xml_lines = explode("n", $xml_obj->asXML());
    $indent_level = 0;
    
    $new_xml_lines = array();
    foreach ($xml_lines as $xml_line) {
        if (preg_match('#(<[a-z0-9:-]+((s+[a-z0-9:-]+="[^"]+")*)?>.*<s*/s*[^>]+>)|(<[a-z0-9:-]+((s+[a-z0-9:-]+="[^"]+")*)?s*/s*>)#i', $xml_line)) {
            $new_line = str_pad('', $indent_level*4) . $xml_line;
            $new_xml_lines[] = $new_line;
        } elseif (preg_match('#<[a-z0-9:-]+((s+[a-z0-9:-]+="[^"]+")*)?>#i', $xml_line)) {
            $new_line = str_pad('', $indent_level*4) . $xml_line;
            $indent_level++;
            $new_xml_lines[] = $new_line;
        } elseif (preg_match('#<s*/s*[^>/]+>#i', $xml_line)) {
            $indent_level--;
            if (trim($new_xml_lines[sizeof($new_xml_lines)-1]) == trim(str_replace("/", "", $xml_line))) {
                $new_xml_lines[sizeof($new_xml_lines)-1] .= $xml_line;
            } else {
                $new_line = str_pad('', $indent_level*4) . $xml_line;
                $new_xml_lines[] = $new_line;
            }
        } else {
            $new_line = str_pad('', $indent_level*4) . $xml_line;
            $new_xml_lines[] = $new_line;
        }
    }
    
    $xml = join("n", $new_xml_lines);
    return ($html_output) ? '<pre>' . htmlentities($xml) . '</pre>' : $xml;
}


?>