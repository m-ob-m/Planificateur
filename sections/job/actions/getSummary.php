<?php
    /**
     * \name		getSummary.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-06-05
     *
     * \brief 		Récupère un résumé bref de la job
     * \details     Récupère un résumé bref de la job
     */
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
        require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
        require_once __DIR__ . '/../controller/jobController.php';		// Controleur des paramètres de base des portes
    
        // Initialize the session
        session_start();
                                                                                                
        // Check if the user is logged in, if not then redirect him to login page
        if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            {
                throw new \Exception("You are not logged in.");
            }
            else
            {
                header("location: /Planificateur/lib/account/logIn.php");
            }
            exit;
        }
    
        // Closing the session to let other scripts use it.
        session_write_close();

        $db = new FabPlanConnection();
        
        // Vérification des paramètres
        $jobName = $_GET["name"] ?? null;
        $jobId = $_GET["id"] ?? null;
        if(empty($jobId) && empty($jobName))
        {
            throw new \Exception("No job identifier provided.");
        }
        
        $job = null;
        $batch = null;
        $partsAmount = 0;
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
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
            
            $partsAmount = 0;
            foreach($job->getJobTypes() as $jobType)
            {
                foreach($jobType->getParts() as $part)
                {
                    $partsAmount += $part->getQuantityToProduce();
                }
            }
            
            $batch = $job->getParentBatch($db);
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
        
        $jobSummary = (object)array(
            "id" => $job->getId(), 
            "name" => $job->getName(), 
            "deliveryDate" => $job->getDeliveryDate(), 
            "partsAmount" => $partsAmount,
            "belongsToBatch" => (($batch !== null) ? $batch->getName() : null)
        );
        
        // Retour au javascript
        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = $jobSummary;
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