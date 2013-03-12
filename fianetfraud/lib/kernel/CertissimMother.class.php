<?php

/**
 * Class mother defining getters and setters methods
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
abstract class CertissimMother
{

	/**
	 * returns the name of the attribute $name
	 * 
	 * @param string $name attribute named to get
	 * @return string
	 */
	public function __get($name)
	{
		return $this->$name;
	}

	/**
	 * sets the attribute $name the value $value
	 * 
	 * @param string $name name of the attribute to set
	 * @param mixed $value value of the attribute to set
	 * @return bool
	 */
	public function __set($name, $value)
	{
		$this->$name = $value;
		return true;
	}

	/**
	 * magic methods called when a non existing method is called from an child of the class Mother
	 *
	 * @param string $name name of the method called
	 * @param array $params params given
	 * @return mixed
	 */
	public function __call($name, array $params)
	{
		if (preg_match('#^get(.+)$#', $name, $out))
		{
			return $this->__get(strtolower($out[1]));
		}
		if (preg_match('#^set(.+)$#', $name, $out))
		{
			return $this->__set(strtolower($out[1]), $params[0]);
		}
	}

}