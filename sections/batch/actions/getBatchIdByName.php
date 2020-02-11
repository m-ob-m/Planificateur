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
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/batch/controller/batchController.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/job/controller/jobController.php";
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

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

    try
    {        
        // Vérification des paramètres
        if(isset($_GET["name"]) && trim($_GET["name"]) !== "")
        {
            $name = trim($_GET["name"]);
        }
        else
        {
            throw new \Exception("An invalid batch name was provided.");
        }

        $batch = \Batch::withName($db, $name);
        if($batch === null)
        {
            throw new \Exception("There is no batch with the name \"{$name}\".");
        }

        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = $batch->getId();
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