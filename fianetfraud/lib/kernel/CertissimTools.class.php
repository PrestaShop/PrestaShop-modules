<?php

/**
 * Description of FianetTools
 *
 * @author nespiau
 */
class CertissimTools extends CertissimMother
{

	/**
	 * returns true if the string given in paramater is a valid XML string, false otherwise
	 *
	 * @param string $string
	 * @return bool
	 */
	static function isXMLstring($string)
	{
		preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*>.*</(.+)>$#s', $string, $output);
		preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*/>$#s', $string, $output2);

		return (count($output) != 0 && ($output[2] == $output[3])) || count($output2) != 0;
	}

	/**
	 * returns true if the object given in parameter is of type $type, false otherwise
	 *
	 * @param string $type class name expected
	 * @param mixed $object object to check
	 * @return bool
	 */
	static function isType($type, $object)
	{
		if (!is_object($object))
			return false;

		return (get_class($object) == $type || in_array($type, class_parents($object)));
	}

	/**
	 * returns true if the object given in parameter is a CertissimXMLElement object, false otherwise
	 *
	 * @param mixed $input
	 * @return bool
	 */
	static function isXMLElement($input)
	{
		return self::isType('CertissimXMLElement', $input);
	}

	/**
	 * returns true if the object givent in parameter is a SimpleXMLElement object, false otherwise
	 *
	 * @param mixed $input
	 * @return bool
	 */
	static function isSimpleXMLElement($input)
	{
		return self::isType('SimpleXMLElement', $input);
	}

	/**
	 * sanitize the string given a paramter to be a valid XML element name. Example : OptionsPaiement becomes options-paiement
	 * @param string $name
	 */
	static function normalizeName($name)
	{
		$string = strtolower($name[0]);
		$i = 1;
		for ($i; $i < strlen($name); $i++)
		{
			if (ord($name[$i]) >= ord('A') && ord($name[$i]) <= ord('Z'))
			{
				$string .= '-';
			}

			$string .= strtolower($name[$i]);
		}

		return $string;
	}

	/**
	 * replaces characters with accents by simple characters, and replaces every non alphanumerical char by a dash
	 * 
	 * @param string $string
	 * @return string
	 */
	static function normalizeString($string, $charset = "UTF-8")
	{
		$string = strtolower($string);
		$normalized_string = self::dropAccents($string, $charset);
		$normalized_string = self::dropNANchars($normalized_string);

		return $normalized_string;
	}

	/**
	 * drops accents
	 * 
	 * @param string $string
	 * @return string
	 */
	static function dropAccents($string, $charset = 'UTF-8')
	{
		$string = htmlentities($string, ENT_NOQUOTES, $charset);

		$string = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $string);
		$string = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $string); // pour les ligatures e.g. '&oelig;'
		$string = preg_replace('#&[^;]+;#', '', $string); // supprime les autres caract�res

		return $string;
	}

	/**
	 * replaces every non alphanumerical char by a dash
	 * 
	 * @param string $string
	 * @return string
	 */
	static function dropNANchars($string)
	{
		return preg_replace('#[^A-Za-z0-9]#', '-', $string);
	}

	static function convert_encoding($string, $to, $from = '')
	{
// Convert string to ISO_8859-1
		if ($from == "UTF-8")
			$iso_string = utf8_decode($string);
		else
		if ($from == "UTF7-IMAP")
			$iso_string = imap_utf7_decode($string);
		else
			$iso_string = $string;

// Convert ISO_8859-1 string to result coding
		if ($to == "UTF-8")
			return(utf8_encode($iso_string));
		else
		if ($to == "UTF7-IMAP")
			return(imap_utf7_encode($iso_string));
		else
			return($iso_string);
	}

	static function getRemoteAddr()
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND $_SERVER['HTTP_X_FORWARDED_FOR'])
		{
			if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ','))
			{
				$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				return $ips[0];
			}
			else
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		return $_SERVER['REMOTE_ADDR'];
	}

}
