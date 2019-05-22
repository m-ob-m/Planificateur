<?php
/**
 * \name		getSummary.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-06-05
 *
 * \brief 		Détermine si une job est liée à une Batch.
 * \details     Détermine si une job est liée à une Batch.
 */

include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/../model/job.php';		// Modèle d'une Job


// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $db = new \FabPlanConnection();
    
    // Vérification des paramètres
    $jobName = $_GET["name"] ?? null;
    $jobId = $_GET["id"] ?? null;
    
    $isLinked = null;
    try
    {
        $db = new \FabPlanConnection();
        $db->getConnection()->beginTransaction();
        
        $job = null;
        if(!empty($jobId))
        {
            // Get job by id
            $job = \Job::withID($db, $jobId);
            if($job === null)
            {
                throw new \Exception("There is no job with the id \"{$jobId}\".");
            }
        }
        elseif(!empty($jobName))
        {
            // Get job by name
            $job = \Job::withName($db, $jobName);
            if($job === null)
            {
                throw new \Exception("There is no job with the name \"{$jobName}\".");
            }
        }
        else
        {
            throw new \Exception("No job identifier provided.");
        }

        $isLinked = ($job->getParentBatch($db) !== null);

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
    $responseArray["success"]["data"] = $isLinked;
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