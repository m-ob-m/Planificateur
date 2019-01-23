<?php
/**
 * \name		reprise\delete.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-02-24
*
* \brief 		Supprime une entrée de reprise
* \details 		Supprime une entrée de reprise
*/

// INCLUDE
include '../../../lib/config.php';	// Fichier de configuration
include '../../../lib/connect.php';	// Classe de connection à la base de données

$db = new FabPlanConnection();


try{
	$db->getConnection()->beginTransaction();	// Démarrage de la transaction pour que tout soit créer dans un seul bloc ou pas du tout (ACID)
	// s'il y a eu une erreur durant les transactions (pour l'intégrité des données)

	$idReprise = $_POST["idReprise"];
	
	// Acquérir idJob
	$stmt = $db->getConnection()->query('SELECT j.id_job from job j 
			inner join reprise r on j.id_job=r.job_id WHERE r.id_reprise=' . $idReprise);
	if($row = $stmt->fetch())
		$idJob = $row["id_job"];	
	
	
	// Supprimer job_type_porte
	$sth = $db->getConnection()->prepare("DELETE jtp.* FROM job_type_porte jtp
			inner join job_type jt on jtp.job_type_id=jt.id_job_type
			inner join job j on jt.job_id=j.id_job
			WHERE j.id_job=?");
	$sth->execute([$idJob]);
	
	// Supprimer job_type_params
	$sth = $db->getConnection()->prepare("DELETE jtp.* FROM job_type_params jtp
			inner join job_type jt on jtp.job_type_id=jt.id_job_type
			inner join job j on jt.job_id=j.id_job
			WHERE j.id_job=?");
	$sth->execute([$idJob]);
	
	// Supprimer job_type
	$sth = $db->getConnection()->prepare("DELETE jt.* FROM job_type jt
			inner join job j on jt.job_id=j.id_job
			WHERE j.id_job=?");
	$sth->execute([$idJob]);
	
	// Supprimer reprise
	$sth = $db->getConnection()->prepare("DELETE FROM reprise WHERE id_reprise=?");
	$sth->execute([$idReprise]);
		
	// Supprimer batch_job
	$sth = $db->getConnection()->prepare("DELETE FROM batch_job WHERE job_id=?");
	$sth->execute([$idJob]);
	
	// Supprimer job
	$sth = $db->getConnection()->prepare("DELETE FROM job WHERE id_job=?");
	$sth->execute([$idJob]);
		
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