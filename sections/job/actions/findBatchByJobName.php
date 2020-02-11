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
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        // INCLUDE
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/job/controller/jobController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/batch/controller/batchController.php";

        // Initialize the session
        session_start();
                                                                                        
        // Check if the user is logged in, if not then redirect him to login page
        if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            if(!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest")
            {
                throw new \Exception("You are not logged in.");
            }
            else
            {
                header("location: /Planificateur/lib/account/logIn.php");
            }
            exit;
        }

        // Getting a connection to the database.
        $db = new \FabPlanConnection();

        // Closing the session to let other scripts use it.
        session_write_close();

        $input =  json_decode(file_get_contents("php://input"));
        
        // Vérification des paramètres
        $name = (isset($input->productionNumber) ? $input->productionNumber : null);
        
        try
        {
            $db->getConnection()->beginTransaction();
            $job = \Job::withName($db, $name);
            if($job !== null)
            {
                $stmt = $db->getConnection()->prepare("
                    SELECT `bj`.`batch_Id` AS `batchId`
                    FROM `batch_job` AS `bj`
                    INNER JOIN `job` AS `j` ON `bj`.`job_Id` = `j`.`id_Job`
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
                    throw new \Exception("There is no Batch associated to the Job \"{$name}\".");
                }
            }
            else
            {
                throw new \Exception("There is no Job named \"{$name}\".");
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
?>