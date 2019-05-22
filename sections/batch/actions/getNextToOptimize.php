<?php
/**
 * \name		getSummary.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-06-05
 *
 * \brief 		Détermine si une job existe.
 * \details     Détermine si une job existe.
 */

include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/../model/batch.php';		// Modèle d'une Job


// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $db = new \FabPlanConnection();
    
    $batch = null;
    try
    {
        $db = new \FabPlanConnection();
        $db->getConnection()->beginTransaction();
        $stmt = $db->getConnection()->prepare("
            SELECT `b`.`id_batch` AS `id`, `b`.`nom_batch` AS `name`, `b`.`panneaux` AS `pannels`, SUM(`jtp`.`quantite`) AS `quantity
            FROM `fabplan`.`batch` AS `b`
            INNER JOIN `fabplan`.`job` AS `j` ON `b`.`id_batch` = `j`.`batch_id`
            INNER JOIN `fabplan`.`job_type` AS `jt` ON `j`.`id_job` = `jt`.`job_id`
            INNER JOIN `fabplan`.`job_type_porte` AS `jtp` ON `jt`.`id_job_type` = `jtp`.`job_type_id`
            WHERE `b`.`etat_mpr` = 'A'
            GROUP BY `b`.`id_batch`, `b`.`nom_batch`, `b`.`panneaux` 
            ORDER BY `quantity` ASC
            LIMIT 1;
        ");
        $stmt->execute();
        
        // Fetch results
        if($row = $stmt->fetch())
        {
            $batch = (object) array("id" => $row["id"], "name" => $row["name"], "pannels" => $row["pannels"]);
        }
        
        $db->getConnection()->commit();
    }
    catch(\Exception $e)
    {
        $db->getConnection()->rollback();
        throw $e;
    }
    finally
    {
        $db = null;
    }

    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = $batch;
}
catch(Exception $e)
{
    $responseArray["status"] = "failure";
    $responseArray["failure"]["message"] = $e->getMessage();
}
finally
{
    echo json_encode($responseArray);
}
?>