<?php
class BuysterOperation
{
	private $_idCart;
	private $_operation;
	private $_status;
	private $_reference;
	
	public	function __construct($id)
	{
		$this->_idCart = $id;
		if (Db::getInstance()->getValue('SELECT * FROM `'._DB_PREFIX_.'buyster_operation` WHERE `id_cart` = "'.(int)$this->_idCart.'"') < 1)
			Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'buyster_operation` (`id_cart`) VALUES ("'.(int)$id.'")');
	}
	
	
	public function setOperation($operation)
	{
		$this->_operation = $operation;
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'buyster_operation` SET `operation_name` = "'.pSQL($operation).'" WHERE `id_cart` = "'.(int)$this->_idCart.'"');
	}
	
	public function setReference($ref)
	{
		$this->_reference = $ref;
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'buyster_operation` SET `reference` = "'.pSQL($ref).'" WHERE `id_cart` = "'.(int)$this->_idCart.'"');
	}
	
	public function setToken($token)
	{
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'buyster_operation` SET `token` = "'.pSQL($token).'" WHERE `id_cart` = "'.(int)$this->_idCart.'"');
	}
	
	
	public function setStatus($status)
	{
		$this->_status = $status;
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'buyster_operation` SET `status` = "'.pSQL($status).'" WHERE `id_cart` = "'.(int)$this->_idCart.'"');
	}
	
	public static function setStatusId($id, $status)
	{
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'buyster_operation` SET `status` = "'.pSQL($status).'" WHERE `id_cart` = "'.(int)$id.'"');
	}
	
	public function getOperation()
	{
		return $this->_operation;
	}
	
	public function getReference()
	{
		return $this->_reference;
	}
	
	public function getStatus()
	{
		return $this->_status;
	}
	
	public static function getOperationId($id)
	{
		return (Db::getInstance()->getValue('SELECT `operation_name` FROM `'._DB_PREFIX_.'buyster_operation` WHERE `id_cart` = "'.(int)$id.'"'));
	}
	
	public static function getReferenceId($id)
	{
		return (Db::getInstance()->getValue('SELECT `reference` FROM `'._DB_PREFIX_.'buyster_operation` WHERE `id_cart` = "'.(int)$id.'"'));
	}
	
	public static function getTokenId($id)
	{
		return (Db::getInstance()->getValue('SELECT `token` FROM `'._DB_PREFIX_.'buyster_operation` WHERE `id_cart` = "'.(int)$id.'"'));
	}
	
	public static function getStatusId($id)
	{
		return (Db::getInstance()->getValue('SELECT `status` FROM `'._DB_PREFIX_.'buyster_operation` WHERE `id_cart` = "'.(int)$id.'"'));
	}
	
	public static function setReferenceReference($ref, $old_ref)
	{
		Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'buyster_operation` SET `reference` = "'.pSQL($ref).'" WHERE `reference` = "'.pSQL($old_ref).'"');
	}
	
}
?>