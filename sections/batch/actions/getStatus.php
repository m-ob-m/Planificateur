<?php
/**
 * \name		getStatus.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-09-11
 *
 * \brief 		Récupère le statut d'une Batch
 * \details     Récupère le statut d'une Batch
 */

include_once __DIR__ . '/../controller/batchController.php'; // Contrôleur d'un Batch

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $batchId = isset($_GET["batchId"]) ? intval($_GET["batchId"]) : null;
    
    // Get the information
    $batch = (new \BatchController())->getBatch($batchId);
    
    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = $batch->getStatus();
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