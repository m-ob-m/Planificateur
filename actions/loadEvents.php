<?php
/**
 * \name		loadEvents.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-08-15
 *
 * \brief 		Charge les Batch (évènements) afin de les insérer dans le calendrier
 * \details     Charge les Batch (évènements) afin de les insérer dans le calendrier
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/controller/planificateur.php";    

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

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $start = isset($_GET["start"]) ? $_GET["start"] : null;
    $end = isset($_GET["start"]) ? $_GET["end"] : null;
    
    $planificateur = (new PlanificateurController($db))->fetchBatch($start, $end);
    
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = $planificateur->batchEvents();
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