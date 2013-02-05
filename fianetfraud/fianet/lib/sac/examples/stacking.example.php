<?php

require_once '../../includes/includes.inc.php';

/* * ***************************************************
 * exemple d'envoi de plusieurs flux sac via stacking
 * *************************************************** */

//création des commandes

$sac = new Sac();

/* * ****** création de <control> ************** */
$control1 = new Control();

//création de l'utilisateur
$utilisateur1 = new Utilisateur('facturation', 'Monsieur', 'DUPONT', 'Michel', 'SARL DMICHEL', '0101010203', '0605050404', '0101010233', 'd.michel@live.com');
//création de l'adresse de l'utilisateur
$addresse1 = new Adresse('facturation', '13 rue de la gare', 'Bat. B', '75001', 'Paris', 'France');

/* * ****** création de <infocommande> ************** */

//transport
$transport = new Transport(Transport::TYPE_TRANSPORTEUR, 'La Poste', Transport::RAPIDITE_STANDARD);
//infocommande
$refid1 = generateRandomRefId();
$infocommande = new Infocommande($sac->getSiteId(), $refid1, '35', $_SERVER['REMOTE_ADDR'], date('Y-m-d H:i:s'));
$infocommande->childTransport($transport);

/* * ****** création de <list> ************** */
$list = new ProductList();
$list->addProduit('libellé du produit', array('type' => '13', 'ref' => 'refprod1', 'prixunit' => '35', 'nb' => '1'), true);

/* * ****** création de <paiement> ************** */

$paiement = new Paiement(Paiement::TYPE_CARTE);

/* * ****** remplissage du <control> ************** */
$control1->addChild($utilisateur1);
$control1->addChild($addresse1);
$infocommande->addChild($list);
$control1->addChild($infocommande);
$control1->addChild($paiement);

$refs[] = $refid1;


/* * ****** création de <control> ************** */
$control2 = new Control();

//création de l'utilisateur facturation
$utilisateur1 = new Utilisateur('facturation', 'Monsieur', 'DUPONT', 'Michel', 'SARL DMICHEL', '0101010203', '0605050404', '0101010233', 'd.michel@live.com');
//création de l'utilisateur livraison
$utilisateur2 = new Utilisateur('livraison', 'Madame', 'DURAND', 'Jeanine', null, '0302010101', '0601010202', '0302010110', 'jeanine.durand@gmail.com');
//création de l'adresse de l'utilisateur facturation
$addresse1 = new Adresse('facturation', '13 rue de la gare', 'Bat. B', '75001', 'Paris', 'France');
$appart = array(
    'digicode1' => 'B7G4',
    'digicode2' => 'D4A4',
    'escalier' => 'B',
    'etage' => '2',
    'nporte' => '114',
    'batiment' => 'C',
);
$appartement = new Appartement($appart);
//création de l'adresse de l'utilisateur livraison
$addresse2 = new Adresse('livraison', '4 chemin des aubépines', 'appt 14', '67000', 'Strasbourg', 'France', $appartement);

/* * ****** création de <infocommande> ************** */

//transport
$transport = new Transport(Transport::TYPE_TRANSPORTEUR, 'La Poste', Transport::RAPIDITE_STANDARD);
//infocommande
$refid2 = generateRandomRefId();
$infocommande = new Infocommande($sac->getSiteId(), $refid2, '160', $_SERVER['REMOTE_ADDR'], date('Y-m-d H:i:s'));
$infocommande->childTransport($transport);

/* * ****** création de <list> ************** */
$list = new ProductList();
$list->addProduit('libellé du produit 1', array('type' => '13', 'ref' => 'refprod1', 'prixunit' => '35', 'nb' => '2'));
$list->addProduit('libellé du produit 2', array('type' => '13', 'ref' => 'refprod2', 'prixunit' => '30', 'nb' => '3'));

/* * ****** création de <paiement> ************** */
$paiement = new Paiement(Paiement::TYPE_CARTE);

/* * ****** remplissage du <control> ************** */
$control2->addChild($utilisateur1);
$control2->addChild($utilisateur2);
$control2->addChild($addresse1);
$control2->addChild($addresse2);
$infocommande->addChild($list);
$control2->addChild($infocommande);
$control2->addChild($paiement);

$refs[] = $refid2;


/* * ****** création de <control> ************** */
$control3 = new Control();

//création de l'utilisateur facturation
$utilisateur1 = new Utilisateur('facturation', 'Monsieur', 'DUPONT', 'Michel', 'SARL DMICHEL', '0101010203', '0605050404', '0101010233', 'd.michel@live.com');
//création de l'adresse de l'utilisateur facturation
$addresse1 = new Adresse('facturation', '13 rue de la gare', 'Bat. B', '75001', 'Paris', 'France');

/* * ****** création de <infocommande> ************** */

//adresse du point relais
$adressepointrelais = new XMLElement('<adresse></adresse>');
$adressepointrelais->childRue1('1 rue de la gare');
$adressepointrelais->childRue2('lot. des petit pins');
$adressepointrelais->childCpostal('75009');
$adressepointrelais->childVille('Paris');
$adressepointrelais->childPays('France');
$pointrelais = new Pointrelais('KIALA115542', 'Kiala Les Petits Pins', $adressepointrelais);
//transport
$transport = new Transport(Transport::TYPE_POINT_RELAIS, 'Kiala', Transport::RAPIDITE_STANDARD, $pointrelais);
//infocommande
$refid3 = generateRandomRefId();
$infocommande = new Infocommande($sac->getSiteId(), $refid3, '160', $_SERVER['REMOTE_ADDR'], date('Y-m-d H:i:s'));
$infocommande->childTransport($transport);

/* * ****** création de <list> ************** */
$list = new ProductList();
$list->addProduit('libellé du produit 1', array('type' => '13', 'ref' => 'refprod1', 'prixunit' => '35', 'nb' => '2'));
$list->addProduit('libellé du produit 2', array('type' => '13', 'ref' => 'refprod2', 'prixunit' => '30', 'nb' => '3'));

/* * ****** création de <paiement> ************** */

$paiement = new Paiement(Paiement::TYPE_CARTE);

/* * ****** remplissage du <control> ************** */
$control3->addChild($utilisateur1);
$control3->addChild($addresse1);
$infocommande->addChild($list);
$control3->addChild($infocommande);
$control3->addChild($paiement);

$refs[] = $refid3;


//création du stack
$stack = new XMLElement('<stack></stack>');
$stack->addChild($control1);
$stack->addChild($control2);
$stack->addChild($control3);

//envoi des flux
$validstack = $sac->sendStacking($stack);

//instanciation du ValidstackResponse
$response = new ValidstackResponse($validstack->getXML());

//affichage de la réponse dans un textarea
echo "<textarea cols='100' rows='10'>$response</textarea>";

echo "<hr />";
//exemple d'exploitation du résultat
echo "<h1>Exploitation du résultat</h1>";
echo ($response->hasFatalError() ? "Une erreur fatale a été recontrée, le stack n'a pas pu être reçu par le Fscreener : " . $response->getError() : "Envoi des transactions OK");
echo "<br />";

//parcours de tous les résultats
foreach ($response->getResults() as $result) {
    //affichage de la source
    echo "<textarea cols='50' rows='6'>$result</textarea>";
    echo "<br />";
    echo "La transaction de référence <i>".$result->returnRefid()."</i> ".($result->hasError() ? "a" : "n'a pas") . " rencontré une erreur" . ($result->hasError() ? "$result->returnErrorid()" : "") .".";
    echo "<br />";
    echo "Détail sur la réception : ".$result->getDetail();
    echo "<br />";
    echo "Avancement : ".$result->returnAvancement();
    echo "<br />";
}