Class XMLElement

Auteur : ESPIAU Nicolas, chargé de suivi technique Fia-Net
Ligne directe : 01 77 488 949
Email : nicolas.espiau[at]fia-net.com

----------------------------------------------

La classe XMLElement est une surclasse de la classe PHP native
SimpleXMLElement.

J'ai choisi de créer cette classe car la classe native ne répondait pas aux
besoins de l'API : pas de navigation aisée dans un flux XML, difficultés
pour attachers des noeuds enfants avec sous-enfants, pas d'accesseurs
(ni getters ni setters).
Avec cette classe on pourra lire et ajouter facilement des attributs, des
valeurs, des noms de balises, des enfants et des sous-enfants.
Elle dispose en plus de méthode permettant de récupérer un tableau d'objets
correspondant à une recherche type getElementByTagName, plus une méthode
permettant de spécifier non seulement le nom de la balise cherchée, mais en 
plus la présence d'un attribut et aussi la valeur de cette attribut.

En résumé, elle facilite la manipulation des objets XML, par la maléabilité au
niveau de l'instanciation des objets, de l'affectations des attributs, valeurs
 et enfants, de la navigation à l'intérieur du flux.


Vous en apprendrez beaucoup plus en regardant le fichier XMLElemen.examples.php
dans le dossier /examples




Pour toute question, suggestion d'amélioration, correction n'hésitez pas à me
contacter, mes coordonnées sont au début de ce README.