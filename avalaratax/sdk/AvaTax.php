<?php
/**
 * AvaTax.php
 *
 * @package Base
 */

/**
 * Defines class loading search path.
 */

/*function __autoload($class_name)
{

	$path=dirname(__FILE__).'/classes/'.$class_name . '.class.php';

	if(!file_exists($path))
	{
		$path=dirname(__FILE__).'/classes/BatchSvc/'.$class_name . '.class.php';

	}

	require_once $path;


}*/

function EnsureIsArray( $obj )
{
	if (is_object($obj))
		$item[0] = $obj;
	else
		$item = (array)$obj;
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

function getDefaultDate()
{
	$dateTime=new DateTime();
    $dateTime->setDate(1900,01,01);

    return $dateTime->format("Y-m-d");
}

function getCurrentDate()
{
	$dateTime=new DateTime();
	return $dateTime->format("Y-m-d");
}
