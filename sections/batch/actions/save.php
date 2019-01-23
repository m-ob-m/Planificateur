<?php
/**
 * \name		save.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-07-16
 *
 * \brief 		Sauvegarde un Batch
 * \details     Sauvegarde un Batch
 */

include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/../controller/batchController.php'; // Contrôleur d'un Batch
include_once __DIR__ . '/../../job/controller/jobController.php'; // Contrôleur d'un Batch


// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $db = new FabPlanConnection();
    
    $input =  json_decode(file_get_contents("php://input"));
    
    // Vérification des paramètres
    $id = preg_match("/^\d+$/", $input->id ?? null) ? intval($input->id) : null;
    $name = $input->name ?? null;
    $startDate = $input->startDate ?? null;
    $endDate = $input->endDate ?? null;
    $fullDay = $input->fullDay ?? null;
    $material = $input->material ?? null;
    $boardSize = $input->boardSize ?? null;
    $status = $input->status ?? null;
    $comments = $input->comments ?? null;
    $jobIds = $input->jobIds ?? null;
    
    // Get the information
    $batch = (new Batch($id, $material, $boardSize, $name, $startDate, $endDate, $fullDay, $comments, $status, "N"));
    foreach($jobIds as $jobId)
    {
        $batch->addJob((new JobController())->getJob($jobId));
    }
    $batch->setCarrousel()->save($db);
    
    // Retour au javascript
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