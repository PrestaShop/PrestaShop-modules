<?php
/**
 * ATObject.class.php
 */
 
/**
 * Generic Dynamic Object
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Base
 */
class ATObject
{
	private $_ivars = array();
	public function __get($n) 
    { 
        if(isset($this->_ivars[$n])) 
        {
            return $this->_ivars[$n]; 
        }
        else 
        {
            return null; 
        }
    }
	public function __set($n,$v) 
    { 
        if($v == null)
        {
            unset($this->_ivars[$n]); 
        }
        else 
        {
            $this->_ivars[$n] = $v; 
        }
    }
	public function __isset($n) { return isset($ivars[$n]); }
	public function __unset($n) { unset($this->_ivars[$n]); }	
	public function __call($n,$args)
	{
		if(sizeof($args) == 1)
		{
			$this->__set($n,$args[0]);
			return null;
		}
		else if(sizeof($args) == 0)
		{
			return $this->__get($n);
		}
	}
    public function ivars() { return $this->_ivars; }
}

?>