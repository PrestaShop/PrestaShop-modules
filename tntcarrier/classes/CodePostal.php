<?php
class CodePostal
{
	private $_postal;
	
	public	function __construct($code)
	{
		$this->_postal = $code;
	}
	
	public function getCity($city)
	{
		if (strpos($this->_postal, '75') === 0 && (int)$this->_postal > 75000 && strpos($city, 'PARIS') === 0)
			return ('PARIS '.substr($this->_postal, -2));
		else if (strpos($this->_postal, '13') === 0 && (int)$this->_postal > 13000 && strpos($city, 'MARSEILLE') === 0)
			return ('MARSEILLE '.substr($this->_postal, -2));
		else if (strpos($this->_postal, '69') === 0 && (int)$this->_postal > 69000 && strpos($city, 'LYON') === 0)	
			return ('LYON '.substr($this->_postal, -2));
		else
			return $city;
	}
}
?>