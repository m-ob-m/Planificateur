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

include_once __DIR__ . '/../controller/planificateur.php';    

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $start = isset($_GET['start']) ? $_GET['start'] : null;
    $end = isset($_GET['start']) ? $_GET['end'] : null;
    
    $planificateur = (new PlanificateurController())->fetchBatch($start, $end);
    
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