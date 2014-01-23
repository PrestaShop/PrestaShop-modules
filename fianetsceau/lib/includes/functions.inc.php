<?php
/**
 * génère un code aléatoire composé de chiffres et de lettres (maj et min) sur $i caractères (10 par défaut)
 *
 * @param int $i nombre de caractères du code
 * @return string code aléatoire 
 */
function generateRandomRefIdSceau($i = 10)
{
	$characts = 'abcdefghijklmnopqrstuvwxyz';
	$characts .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$characts .= '1234567890';
	$code = '';

	for ($k = 0; $k < $i; $k++)
	{ //10 est le nombre de caractères
		$code .= substr($characts, rand() % (strlen($characts)), 1);
	}

	return $code;
}

/**
 * retourne vrai si $string est uns chaine XML valide, faux sinon
 *
 * @param string $string chaine à tester
 * @return bool 
 */
function isXMLstringSceau($string, $debug = false)
{
	if ($debug)
		var_dump("string = $string");
	//on vérifie si des balises sont présentes avec ou sans déclaration xml
	preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*>.*</(.+)>$#s', $string, $output);
	preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*/>$#s', $string, $output2);
	if ($debug)
	{
		var_dump($output);
		var_dump($output2);
	}

	//retourne vrai si des balises sont présentes et si les balises ont le meme nom
	return (count($output) != 0 && ($output[2] == $output[3])) || count($output2) != 0;
}

/**
 * retourne vrai si l'objet $object et de type $type, faux sinon
 *
 * @param string $type nom de la classe attendue
 * @param mixed $object objet à tester
 * @return bool 
 */
function isTypeSceau($type, $object)
{
	//retourne faux directement si le paramètre n'est pas un objet
	if (!is_object($object))
		return false;

	return (get_class($object) == $type || in_array($type, class_parents($object)));
}

/**
 * retour vrai si l'objet en paramètre est un objet XMLElement, faux sinon
 * 
 * @param mixed $input
 * @return bool 
 */
function isXMLElementSceau($input)
{
	return isType('XMLElement', $input);
}

/**
 * retour vrai si l'objet en paramètre est un objet FormField, faux sinon
 * 
 * @param mixed $input
 * @return boolean 
 */
function isFormFieldSceau($input)
{
	return isType('FormField', $input);
}

/**
 * retour vrai si l'objet en paramètre est un objet Form, faux sinon
 * 
 * @param mixed $input
 * @return boolean 
 */
function isFormSceau($input)
{
	return isType('Form', $input);
}

/**
 * retourne vrai si $input est un objet de classe SimpleXMLElement, faux sinon
 *
 * @param mixed $input objet à tester
 * @return bool 
 */
function isSimpleXMLElementSceau($input)
{
	return isType('SimpleXMLElement', $input);
}

/**
 * converti une chaine en chaine valide pour une balise XML, exemple : OptionsPaiement devient options-paiment
 * @param string $name 
 */
function normalizeNameSceau($name)
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
 * insère une erreur en haut du fichier de log, en le créant s'il n'existe pas déjà
 *
 * @param string $func nom de la fonction reportant le bug
 * @param string $msg description de l'erreur
 */
function insertLogSceau($func, $msg)
{
	SceauLogger::insertLogSceau($func, $msg);
}

/*
function is_utf8($string)
{
	return !strlen(
			preg_replace(
				',[\x09\x0A\x0D\x20-\x7E]'			# ASCII
				.'|[\xC2-\xDF][\x80-\xBF]'			 # non-overlong 2-byte
				.'|\xE0[\xA0-\xBF][\x80-\xBF]'		 # excluding overlongs
				.'|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'  # straight 3-byte
				.'|\xED[\x80-\x9F][\x80-\xBF]'		 # excluding surrogates
				.'|\xF0[\x90-\xBF][\x80-\xBF]{2}'	  # planes 1-3
				.'|[\xF1-\xF3][\x80-\xBF]{3}'		  # planes 4-15
				.'|\xF4[\x80-\x8F][\x80-\xBF]{2}'	  # plane 16
				.',sS', '', $string));
}

function convert_encoding($string, $to, $from = '')
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
}*/