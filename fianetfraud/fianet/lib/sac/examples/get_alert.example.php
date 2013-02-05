<?php

require_once '../../includes/includes.inc.php';

$sac = new Sac();

//récupération des éval
$stack = $sac->getAlert('all');

//instanciation des résultats
$response = new ResultResponse($stack->getXML());

//affichage de la réponse dans un textarea
echo "<textarea cols='100' rows='10'>$response</textarea>";

echo "<hr />";

echo $response->returnCount() . " transactions réévaluées";

echo "<hr />";

foreach ($response->getTransactions() as $transaction) {
    //affichage de la transaction dans un textarea
    echo "<textarea cols='100' rows='10'>$transaction</textarea>";
    echo "<br />";
    echo "<h2>Exploitation de la transaction</h2>";
    echo "Référence : " . $transaction->returnRefid();
    echo "<br />";
    echo "Score : " . $transaction->getEval();
    echo "<br />";
    echo "Détail : " . $transaction->getDetail();
    echo "<br />";
    echo "Profil déclenché : " . $transaction->getEvalInfo();
    echo "<br />";
    echo "Date de l'évaluation : " . $transaction->getEvalDate();
    echo "<br />";
    echo "Classement de la transaction : " . $transaction->getClassementLabel() . " (" . $transaction->getClassementID() . ")";
    echo "<hr />";
}

echo "<hr />";