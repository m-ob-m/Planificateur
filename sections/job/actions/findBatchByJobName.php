<?php 
/**
 * \name		findBatchByJobName.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-05-11
 *
 * \brief 		Find a batch by one of its jobs' production number
 * \details     Find a batch by one of its jobs' production number
 */

// INCLUDE
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/../controller/jobController.php';
include_once __DIR__ . '/../../batch/controller/batchController.php';

//Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $input =  json_decode(file_get_contents("php://input"));
    
    // Vérification des paramètres
    $name = (isset($input->productionNumber) ? $input->productionNumber : null);
    
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $job = Job::withName($db, $name);
        $stmt = $db->getConnection()->prepare("
            SELECT `bj`.`batch_Id` AS `batchId`
            FROM `fabplan`.`batch_job` AS `bj`
            INNER JOIN `fabplan`.`job` AS `j` ON `bj`.`job_Id` = `j`.`id_Job`
            WHERE `j`.`id_job` = :jobId
            LIMIT 1
            FOR SHARE;
        ");
        $stmt->bindValue(":jobId", $job->getId(), PDO::PARAM_INT);
        $stmt->execute();
        
        $id = null;
        if($row = $stmt->fetch())
        {
            $id = $row["batchId"];
        }
        else
        {
            throw new \Exception("There is no Job with the name \"{$name}\".");
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
    $responseArray["success"]["data"] = $id;
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