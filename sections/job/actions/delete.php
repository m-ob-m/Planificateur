<?php 
    /**
     * \name		create.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2019-04-25
     *
     * \brief 		Deletes a job
     * \details     Deletes a job
     */

    // INCLUDE
    include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
    include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
    include_once __DIR__ . '/../model/job.php';

    //Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        $input =  json_decode(file_get_contents("php://input"));
        
        // Vérification des paramètres
        $jobName = $input->name ?? null;
        $jobId = $input->id ?? null;
        
        $db = new \FabPlanConnection();
        try
        {
            $job = null;
            $db->getConnection()->beginTransaction();
            if(!empty($jobId))
            {
                // Get job by id
                $job = \Job::withID($db, $jobId, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
            }
            elseif(!empty($jobName))
            {
                // Get job by name
                $job = \Job::withName($db, $jobName, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
            }
            else
            {
                throw new \Exception("No job identifier provided.");
            }
            
            if($job !== null)
            {
                if($job->getParentBatch($db) === null)
                {
                    $job->delete($db);
                }
                else
                {
                    throw new \Exception("Job \"{$name}\" is already linked to a batch and cannot be deleted.");
                }
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
        $responseArray["success"]["data"] = null;
    }
    catch(\Exception $e)
    {
        $responseArray["status"] = "failure";
        $responseArray["failure"]["message"] = $e->getMessage();
    }
    finally
    {
        echo json_encode($responseArray);
    }
?>