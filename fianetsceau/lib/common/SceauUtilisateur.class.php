<?php

/**
 * Balise <utilisateur>
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class SceauUtilisateur extends SceauXMLElement
{
	const TYPE_ENTREPRISE=1;
	const TYPE_PARTICULIER=2;

	public function __construct($civility=null, $lastname=null, $firstname=null, $email_address=null)
	{
		parent::__construct();

		$this->childNom($lastname, array('titre' => $civility));
		$this->childPrenom($firstname);
		$this->childEmail($email_address);
	}

}