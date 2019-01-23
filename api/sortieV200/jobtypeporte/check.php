<?php
/**
 * \name		jobtypeporte\check.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-02-24
*
* \brief 		Utiliser pour mettre à jour un élément qui a été coché
* \details 		Utiliser pour mettre à jour un élément qui a été coché
*/

// INCLUDE
include '../../../lib/config.php';	// Fichier de configuration
include '../../../lib/connect.php';	// Classe de connection à la base de données

$db = new FabPlanConnection();


try{
	$db->getConnection()->beginTransaction();	// Démarrage de la transaction pour que tout soit créer dans un seul bloc ou pas du tout (ACID)
	// s'il y a eu une erreur durant les transactions (pour l'intégrité des données)

	$sth = $db->getConnection()->prepare("UPDATE job_type_porte set terminer=? WHERE id_job_type_porte=?");
	$sth->execute([$_POST["terminer"], $_POST["id"]]);

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