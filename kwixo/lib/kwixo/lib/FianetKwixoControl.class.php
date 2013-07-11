<?php

class FianetKwixoControl extends KwixoControl
{

	public function createWallet($datecom, $datelivr)
	{
		$wallet = $this->root->appendChild(new KwixoWallet());
		$wallet->addAttribute('version', KwixoWallet::WALLET_VERSION);
		$wallet->createChild('datecom', $datecom);
		$wallet->createChild('datelivr', $datelivr);
		return $wallet;
	}

	public function createPaymentOptions($type, $rnp = null, $rnp_offered = null)
	{
		$attributes = array(
			'type' => $type,
		);
		if (!is_null($rnp))
			$attributes['comptant-rnp'] = $rnp;
		if (!is_null($rnp_offered))
			$attributes['comptant-rnp-offert'] = $rnp_offered;

		$options = $this->root->appendChild(new KwixoXMLElement('options-paiement', ' '));
		foreach ($attributes as $key => $value)
			$options->setAttribute($key, $value);
		return $options;
	}

}