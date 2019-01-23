<?php
/**
 * \name		cnc\batchs.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-03-03
*
* \brief 		Liste des batchs à effectuer (non-terminées)
* \details 		Liste des batchs à effectuer (non-terminées)
*/

// INCLUDE
include '../../../lib/config.php';	// Fichier de configuration
include '../../../lib/connect.php';	// Classe de connection à la base de données

$db = new FabPlanConnection();

$sth = $db->getConnection()->prepare("SELECT b.* from batch b
		inner join materiel m on b.materiel_id=m.id_materiel
		WHERE b.etat<>'T' AND m.est_mdf='Y' order by nom_batch ASC");
$sth->execute();

$result = $sth->fetchAll();
echo "{ \"batchs\":" . json_encode($result) . "}";

?>