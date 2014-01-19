<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class Supplieremail extends Module
{

  public function __construct()
  {
    $this->name = 'supplieremail';
    $this->tab = 'back_office_features';
    $this->version = '1.0';
    $this->author = 'T.B.';
    $this->need_instance = 0;
     
    parent::__construct();
 
    $this->displayName = $this->l('Supplier Email');
    $this->description = $this->l('This module adds email for a supplier.');
 
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
 
    if (!Configuration::get('SUPPLIEREMAIL_NAME'))      
      $this->warning = $this->l('No name provided');
  }

  public function install()
  {
	
	$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = '"._DB_PREFIX_."supplier' AND COLUMN_NAME='email'";
	
	if (Db::getInstance()->getValue($sql)==false)
	{
		Db::getInstance()->execute("ALTER TABLE "._DB_PREFIX_."supplier ADD email varchar(128) AFTER name");
	}
	
	$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = '"._DB_PREFIX_."order_state' AND COLUMN_NAME='send_supplier_email'";
	
	if (Db::getInstance()->getValue($sql)==false)
	{
		Db::getInstance()->execute("ALTER TABLE "._DB_PREFIX_."order_state ADD send_supplier_email TINYINT(4) DEFAULT 0");
	}
	
	// if ($this->registerHook('ActionOrderStatusPostUpdate')==false) return false;
	
	return (parent::install() && $this->registerHook('ActionOrderStatusPostUpdate'));
  }
  
  public function uninstall()
  {
		/* $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = '"._DB_PREFIX_."supplier' AND COLUMN_NAME='email'";
  
		if (Db::getInstance()->getValue($sql))
		{
			Db::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'supplier DROP email');
		} */
	  
	return (parent::uninstall());
  }
  
  public function hookActionOrderStatusPostUpdate($params)
  {
	// If id_order is sent, we instanciate a new Order object
	if (Tools::isSubmit('id_order') && Tools::getValue('id_order') > 0)
	{
		$order = new Order(Tools::getValue('id_order'));
		if (!Validate::isLoadedObject($order))
			throw new PrestaShopException('Can\'t load Order object');
		
		// ShopUrl::cacheMainDomainForShop((int)$order->id_shop);
		
		$products = $order->getProducts();
		
		$emails = array();
					
		foreach ($products  as $pr)
		{
			$suppliers_ids[]= $pr['id_supplier'];
		}
		
		$suppliers_ids = array_unique($suppliers_ids);
		
		$state = Db::getInstance()->getValue("SELECT name FROM "._DB_PREFIX_."order_state_lang WHERE id_order_state=".$order->current_state." AND id_lang=".$order->id_lang);
		
		$turned_on = Db::getInstance()->getValue("SELECT send_supplier_email FROM "._DB_PREFIX_."order_state WHERE id_order_state=".$order->current_state);
		
		if ($turned_on)
		{
			foreach ($suppliers_ids as $id)
			{
				$email = Db::getInstance()->getValue("SELECT email FROM "._DB_PREFIX_."supplier WHERE id_supplier=".$id);
				$name = Db::getInstance()->getValue("SELECT name FROM "._DB_PREFIX_."supplier WHERE id_supplier=".$id);			
				
				mail($email,$name,'Смена статуса заказа. '.$state);
			}					
		}
	}
  }
  
	
}
