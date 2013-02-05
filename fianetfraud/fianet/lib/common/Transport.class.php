<?php

/**
 * Description of Transport
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class Transport extends XMLElement {
    const RAPIDITE_STANDARD=2;
    const RAPIDITE_EXPRESS=1;
    const TYPE_RETRAIT_MARCHAND=1;
    const TYPE_POINT_RELAIS=2;
    const TYPE_RETRAIT_AGENCE=3;
    const TYPE_TRANSPORTEUR=4;
    const TYPE_TELECHARGEMENT=5;

    public function __construct($type="", $nom="", $rapidite="", $pointrelais=array()) {
        parent::__construct();

        $this->childType($type);
        $this->childNom($nom);
        $this->childRapidite($rapidite);

        $this->childPointrelais($pointrelais);
    }

}