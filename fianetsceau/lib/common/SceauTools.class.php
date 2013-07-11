<?php

/**
 * Description of SceauTools
 *
 * @author nespiau
 */
class SceauTools extends SceauMother
{

	/**
	 * retourne vrai si $string est uns chaine XML valide, faux sinon
	 *
	 * @param string $string chaine à tester
	 * @return bool
	 */
	static function isXMLstring($string)
	{
//on vérifie si des balises sont présentes avec ou sans déclaration xml
		preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*>.*</(.+)>$#s', $string, $output);
		preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*/>$#s', $string, $output2);

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
	static function isType($type, $object)
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
	static function isXMLElement($input)
	{
		return self::isType('SceauXMLElement', $input);
	}

	/**
	 * retour vrai si l'objet en paramètre est un objet FormField, faux sinon
	 *
	 * @param mixed $input
	 * @return boolean
	 */
	static function isFormField($input)
	{
		return self::isType('SceauFormField', $input);
	}

	/**
	 * retour vrai si l'objet en paramètre est un objet Form, faux sinon
	 *
	 * @param mixed $input
	 * @return boolean
	 */
	static function isForm($input)
	{
		return self::isType('SceauForm', $input);
	}

	/**
	 * retourne vrai si $input est un objet de classe SimpleXMLElement, faux sinon
	 *
	 * @param mixed $input objet à tester
	 * @return bool
	 */
	static function isSimpleXMLElement($input)
	{
		return self::isType('SimpleXMLElement', $input);
	}

	/**
	 * converti une chaine en chaine valide pour une balise XML, exemple : OptionsPaiement devient options-paiement
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
	 * remplace les caractères accentués par des caractères non accentués, et remplace tous les caractères non alphanumériques par un tiret
	 * 
	 * @param string $string
	 * @return string
	 */
	static function normalizeString($string, $charset="UTF-8")
	{
		//passage en minuscule
		$string = strtolower($string);
		//suppresion des accents
		$normalized_string = self::dropAccents($string, $charset);
		//remplacement des caractères non alphanum
		$normalized_string = self::dropNANchars($normalized_string);

		return $normalized_string;
	}

	/**
	 * remplace les caractères accentués par leur équivalent sans accent
	 * 
	 * @param string $string
	 * @return string
	 */
	static function dropAccents($string, $charset='UTF-8')
	{
		$string = htmlentities($string, ENT_NOQUOTES, $charset);

		$string = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $string);
		$string = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $string); // pour les ligatures e.g. '&oelig;'
		$string = preg_replace('#&[^;]+;#', '', $string); // supprime les autres caractères

		return $string;
	}

	/**
	 * remplace tous les caractères non alphanumériques par des tirets
	 * 
	 * @param string $string
	 * @return string
	 */
	static function dropNANchars($string)
	{
		return preg_replace('#[^A-Za-z0-9]#', '-', $string);
	}

	/**
	 * insère une erreur en haut du fichier de log, en le créant s'il n'existe pas déjà
	 *
	 * @param string $func nom de la fonction reportant le bug
	 * @param string $msg description de l'erreur
	 */
	static function insertLog($func, $msg)
	{
//si le fichier log existe mais a dépassé 500Ko on le renomme pour en créer un vierge
		if (file_exists(SCEAU_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt') && filesize(SCEAU_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt') > 500000)
		{
			$prefix = SCEAU_ROOT_DIR.'/logs/fianetlog-';
			$base = date('YmdHis');
			$sufix = '.txt';
			$filename = $prefix.$base.$sufix;

			for ($i = 0; file_exists($filename); $i++)
				$filename = $prefix.$base."-$i".$sufix;

			rename(SCEAU_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt', $filename);
		}
//si le fichier log n'existe pas on le créé vide
		if (!file_exists(SCEAU_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt'))
		{
//création du fichier en écriture
			$handle = fopen(SCEAU_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt', 'w');

			$entry = date('d-m-Y h:i:s')." | ".__METHOD__." : ".__LINE__." | Création du fichier de log\r";

			fwrite($handle, $entry);

//fermeture immédiate du fichier
			fclose($handle);
		}

//création d'une nouvelle entrée
		$entry = date('d-m-Y h:i:s')." | $func | $msg\r";

//ouverture du log principal
		$handle = fopen(SCEAU_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt', 'a+');
		fwrite($handle, $entry);

//fermeture immédiate du fichier
		fclose($handle);
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
