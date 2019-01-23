<?php
/**
 * \name		reprise\insert.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-02-24
*
* \brief 		Ajoute une reprise au système
* \details 		Ajoute une reprise au système. Présentement, chaque reprise représente 
* 				une job de 1 seule porte. (Pour raison de compatibilité)
*/

// INCLUDE
include '../../../lib/config.php';	// Fichier de configuration
include '../../../lib/connect.php';	// Classe de connection à la base de données

$db = new FabPlanConnection();

try{
	$db->getConnection()->beginTransaction();	// Démarrage de la transaction pour que tout soit créer dans un seul bloc ou pas du tout (ACID)
	// s'il y a eu une erreur durant les transactions (pour l'intégrité des données)

	// Création de la job avec #commande_dechex(rand(4097, 65535)) << Permet d'avoir une commande unique par porte
	$commande = $_POST["noCommande"] . "_" . dechex(rand(4097, 65535));
	$db->getConnection()->prepare("INSERT INTO job(numero,date_livraison,etat)
	SELECT	?, j.date_livraison, j.etat
			from job_type_porte jtp inner join job_type jt on jtp.job_type_id=jt.id_job_type
			INNER JOIN job j on jt.job_id=j.id_job
			WHERE jtp.id_job_type_porte=?")
		->execute([$commande,$_POST["idJobTypePorte"]]);
	
	$idJob = $db->getConnection()->lastInsertId();
	
	
	
	// Création de job_type
	$db->getConnection()->prepare("INSERT INTO job_type(job_id,door_model_id,type_no,fichier_mpr)
	SELECT	?, jt.door_model_id,jt.type_no,jt.fichier_mpr
			from job_type_porte jtp inner join job_type jt on jtp.job_type_id=jt.id_job_type
			WHERE jtp.id_job_type_porte=?")
	->execute([$idJob,$_POST["idJobTypePorte"]]);
	
	$idJobType = $db->getConnection()->lastInsertId();					
	
	
	// Création de job_type_params
	$db->getConnection()->prepare("INSERT INTO job_type_params(job_type_id,param_key,param_value)
			SELECT	?, param_key,param_value
			from job_type_porte jtp inner join job_type jt on jtp.job_type_id=jt.id_job_type
			inner join job_type_params jpar on jt.id_job_type=jpar.job_type_id
			WHERE jtp.id_job_type_porte=?")			
		->execute([$idJobType,$_POST["idJobTypePorte"]]);
							
		
	// Création de job_type_porte
	$db->getConnection()->prepare("INSERT INTO job_type_porte(job_type_id,quantite,longueur,largeur,grain)
		SELECT	?, 1, jtp.longueur, jtp.largeur, jtp.grain
		from job_type_porte jtp WHERE jtp.id_job_type_porte=?")
		->execute([$idJobType,$_POST["idJobTypePorte"]]);
	
	
	// Création de reprise
		$db->getConnection()->prepare("INSERT INTO reprise(job_id,raison,commentaire) values(?,?,?)")
		->execute([$idJob,urldecode($_POST["raison"]),urldecode($_POST["commentaire"])]);
	
	$idReprise = $db->getConnection()->lastInsertId();
	
	$db->getConnection()->commit();	// Envoi des transactions à la BD

	// Fermeture de la connection
	$db = NULL;

	echo $idReprise;

}catch (Exception $e){	// Erreur

	$db->getConnection()->rollback();	// Annulation des transactions
	$db = NULL;	// Fermeture de la connection

	echo "Une erreur est survenue lors de la sauvegarde des donn&eacute;es.<br><br>Code d'erreur : " . $e->getCode() . "<br>Message :" . $e->getMessage();

	throw $e;

}
?>