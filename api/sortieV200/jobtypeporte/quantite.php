<?php
/**
 * \name		jobtypeporte\quantite.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-02-24
*
* \brief 		Met à jour les quantités des portes 
* \details 		Met à jour les quantités des portes
*/

// INCLUDE
include '../../../lib/config.php';	// Fichier de configuration
include '../../../lib/connect.php';	// Classe de connection à la base de données

$db = new FabPlanConnection();

$ids = explode(",",$_POST["id"]);
$qtes = explode(",",$_POST["qte"]);

try{
	$db->getConnection()->beginTransaction();	// Démarrage de la transaction pour que tout soit créer dans un seul bloc ou pas du tout (ACID)
	// s'il y a eu une erreur durant les transactions (pour l'intégrité des données)

	for($i = 0; $i < count($ids); $i++){
		$sth = $db->getConnection()->prepare("UPDATE job_type_porte set qte_produite=? WHERE id_job_type_porte=?");
		$sth->execute([$qtes[$i], $ids[$i]]);
	}

	$db->getConnection()->commit();	// Envoi des transactions à la BD

	// Fermeture de la connection
	$db = NULL;

	echo "ok";

}catch (Exception $e){	// Erreur

	$db->getConnection()->rollback();	// Annulation des transactions
	$db = NULL;	// Fermeture de la connection

	echo "Une erreur est survenue lors de la sauvegarde des donn&eacute;es.<br><br>Code d'erreur : " . $e->getCode() . "<br>Message :" . $e->getMessage();

	throw $e;

}
?>