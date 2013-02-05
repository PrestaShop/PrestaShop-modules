<?php

/**
 * Description of Utilisateur
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class Utilisateur extends XMLElement {
    const TYPE_ENTREPRISE=1;
    const TYPE_PARTICULIER=2;

    public function __construct($type=null, $civility=null, $lastname=null, $firstname=null, $society=null, $phone_home=null, $phone_mobile=null, $phone_fax=null, $email_address=null) {
        parent::__construct();
        if (!is_null($type))
            $this->addAttribute("type", $type);
        $this->addAttribute("qualite", self::TYPE_PARTICULIER);

        $this->childNom($lastname, array('titre' => $civility));
        $this->childPrenom($firstname);
        $this->childSociete($society);
        $this->childTelhome($phone_home);
        $this->childTelmobile($phone_mobile);
        $this->childFax($phone_fax);
        $this->childEmail($email_address);
    }

}