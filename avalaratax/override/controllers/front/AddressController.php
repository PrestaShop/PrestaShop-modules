<?php

class AddressController extends AddressControllerCore
{
	public function preProcess()
	{
		if (version_compare(_PS_VERSION_, '1.5', '<') && (Tools::isSubmit('submitAddress') || Tools::isSubmit('submitAccount')))
		{
			include_once(dirname(__FILE__).'/../../modules/avalaratax/avalaratax.php');
			$avalaraModule = new AvalaraTax();
			$result = $avalaraModule->fixPOST();
			if (isset($result['ResultCode']) && $result['ResultCode'] == 'Error')
			{
				if (isset($result['Messages']['Summary']))
					foreach ($result['Messages']['Summary'] as $error)
						$this->errors[] = Tools::safeOutput($error);
				else
					$this->errors[] = Tools::displayError('This address cannot be submitted');
				return false;
			}
		}
		parent::preProcess();
	}
	
	public function processSubmitAddress()
	{
		include_once(_PS_MODULE_DIR_.'avalaratax/avalaratax.php');
		$avalara_module = new AvalaraTax();
		if ($avalara_module->active)
		{
			$result = $avalara_module->fixPOST();
			if (isset($result['ResultCode']) && $result['ResultCode'] == 'Error')
			{
				if (isset($result['Messages']['Summary']))
					foreach ($result['Messages']['Summary'] as $error)
						$this->errors[] = Tools::safeOutput($error);
				else
					$this->errors[] = Tools::displayError('This address cannot be submitted');
				return false;
			}
		}

		parent::processSubmitAddress();
	}
}