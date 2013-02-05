Fia-Net : API PHP V3.1
Module : SAC

Installation :
Pour installer le module SAC, placer le contenu de l'archive  dans le dossier lib/ de manière à obtenir cette hiériarchie :
lib/sac/const
lib/sac/examples
lib/sac/lib
lib/sac/includes.inc.php

Décommenter ou ajouter la ligne suivante dans le fichier lib/includes/includes.inc.php :
require_once ROOT_DIR . '/lib/sac/includes.inc.php';


Pour faire fonctionner les services sur votre site, pensez à bien reporter les informations privées dans le fichier lib/sac/const/site_params.yml