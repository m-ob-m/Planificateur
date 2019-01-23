<?php
/**
 * \name		jobtypeporte\quantite.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-02-24
*
* \brief 		Met � jour les quantit�s des portes 
* \details 		Met � jour les quantit�s des portes
*/

// INCLUDE
include '../../../lib/config.php';	// Fichier de configuration
include '../../../lib/connect.php';	// Classe de connection � la base de donn�es

$db = new FabPlanConnection();

$ids = explode(",",$_POST["id"]);
$qtes = explode(",",$_POST["qte"]);

try{
	$db->getConnection()->beginTransaction();	// D�marrage de la transaction pour que tout soit cr�er dans un seul bloc ou pas du tout (ACID)
	// s'il y a eu une erreur durant les transactions (pour l'int�grit� des donn�es)

	for($i = 0; $i < count($ids); $i++){
		$sth = $db->getConnection()->prepare("UPDATE job_type_porte set qte_produite=? WHERE id_job_type_porte=?");
		$sth->execute([$qtes[$i], $ids[$i]]);
	}

	$db->getConnection()->commit();	// Envoi des transactions � la BD

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