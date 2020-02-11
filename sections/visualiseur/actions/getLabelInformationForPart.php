<?php
/**
 * \name		fetchProperties.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-09-11
 *
 * \brief 		Fetch the interesting properties of a door (and its associated JobType, Job, etc.)
 * \details     Fetch the interesting properties of a door (and its associated JobType, Job, etc.)
 */

// INCLUDE
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/numberFunctions/numberFunctions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/job/controller/jobController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/type/controller/typeController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/model/controller/modelController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/generic/controller/genericController.php";

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

//Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    // Vérification des paramètres
    $id = $_GET["id"] ?? null;
    
    try
    {
        $db->getConnection()->beginTransaction();
        $jobTypePorte = \JobTypePorte::withID($db, $id);
        $jobType = \JobType::withID($db, $jobTypePorte->getJobTypeId());
        $job = \Job::withID($db, $jobType->getJobId());
        $model = \Model::withID($db, $jobType->getModel()->getId());
        $type = \Type::withImportNo($db, $jobType->getType()->getImportNo());
        $generic = $type->getGeneric();
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
    
    $grain = null;
    if($jobTypePorte->getGrain() === "X")
    {
        $grain = "Horizontal";
    }
    elseif($jobTypePorte->getGrain() === "Y")
    {
        $grain = "Vertical";
    }
    else
    {
        $grain = "Aucun";
    }
    
    $data = array(
        "id" => $id,
        "orderName" => $job->getName(), 
        "modelName" => $model->getDescription(), 
        "height" => toMixedNumber($jobTypePorte->getLength() / 25.4, 16, true),
        "width" => toMixedNumber($jobTypePorte->getWidth() / 25.4, 16, true),
        "customerPO" => $job->getCustomerPurchaseOrderNumber()
    );
    
    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = $data;
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