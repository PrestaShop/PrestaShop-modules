<?php

require_once '../../includes/includes.inc.php';

$reflist = array(
    'YFkhGJ78sV',
    'ebOfqpM8uh',
    '1fWt7NcnwV',
);

$sac = new Sac();

//récupération des éval
$stack = $sac->getValidstackByReflist($reflist);

//instanciation des résultats
$stackresponse = new StackResponse($stack->getXML());

//affichage de la réponse dans un textarea
echo "<textarea cols='100' rows='10'>$stackresponse</textarea>";

echo "<hr />";

$results = $stackresponse->getResults();

foreach ($results as $result) {
    //affichage du résultat dans un textarea
    echo "<textarea cols='100' rows='10'>$result</textarea>";
    echo "<br />";
    echo "<h1>Exploitation des résultats</h1>";
    echo "<br />";
    echo "Référence " . $result->returnRefid() . " trouvée " . $result->returnCount() . " fois.";
    echo "<br />";

    foreach ($result->getTransactions() as $transaction) {
        //affichage de la transaction dans un textarea
        echo "<textarea cols='100' rows='10'>$transaction</textarea>";
        echo "<br />";
        echo "<h2>Exploitation de la transaction</h2>";
        echo "Avancement : " . $transaction->returnAvancement();
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
        echo "<br />";
    }

    echo "<hr />";
}
