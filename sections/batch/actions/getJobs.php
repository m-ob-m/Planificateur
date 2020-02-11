<?php
    /**
     * \name		getJobs.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-07-12
     *
     * \brief 		Récupère les Job contenus dans un Batch
     * \details     Récupère les Job contenus dans un Batch
     */
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/batch/controller/batchController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/job/controller/jobController.php";
    
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
        
        // Vérification des paramètres
        if(!isset($_GET["batchId"]))
        {
            $batchId = null;
        }
        elseif(preg_match("/^\d+$/", $_GET["batchId"]))
        {
            $batchId = intval($_GET["batchId"]);
        }
        else
        {
            $batchId = null;
        }
        
        // Get the information
        $batch = null;
        try
        {
            $db->getConnection()->beginTransaction();
            $batch = \Batch::withID($db, $batchId);
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
        
        $responseArray["success"]["data"] = array();
        if($batch !== null)
        {
            foreach($batch->getJobs() as $job)
            {
                array_push($responseArray["success"]["data"], $job->getId());
            }
        }
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