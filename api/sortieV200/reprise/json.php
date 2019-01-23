<?php
/**
 * \name		reprise\json.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-02-24
*
* \brief 		Crée un fichier JSON pour les reprises
* \details 		Crée un fichier JSON pour les reprises
*/

// INCLUDE
include '../../../lib/config.php';	// Fichier de configuration
include '../../../lib/connect.php';	// Classe de connection à la base de données

$db = new FabPlanConnection();

$sth = $db->getConnection()->prepare("SELECT
		r.id_reprise,
		jtp.id_job_type_porte,
		j.numero,
		jt.door_model_id,
		jt.type_no,
		jtp.quantite,
		jtp.qte_produite,
		jtp.longueur,
		jtp.largeur,
		jtp.grain,
		jtp.terminer,
		jtp.estampille,
		r.raison,
		r.commentaire

		from job_type_porte jtp INNER JOIN job_type jt on jtp.job_type_id=jt.id_job_type
		inner JOIN job j on jt.job_id=j.id_job
		inner join reprise r on j.id_job=r.job_id
		left join batch_job bj on j.id_job=bj.job_id

		Where bj.batch_id is null and jtp.estampille>?

		ORDER BY j.numero, jt.door_model_id, jt.type_no ASC");
$sth->execute([(isset($_POST["estampille"]) ? $_POST["estampille"] : "2010-01-01 00:00:00")]);

$result = $sth->fetchAll();

if(count($result) > 0)
	echo "{ \"reprises\":" . json_encode($result) . "}";
else
	echo "-1";
?>