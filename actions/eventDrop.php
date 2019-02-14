<?php
/**
 * \filename	eventDrop.php
 *
 * \brief 		Change la date d'un évènement lors d'un glisser-déposer sur le calendrier.
 *
 * \date		2017-01-31
 * \version 	1.0
 */

include '../lib/config.php';	// Fichier de configuration
include '../lib/connect.php';	// Classe de connection à la base de données
include '../sections/batch/model/batch.php';	// Modèle d'une batch
include '../controller/planificateur.php';		// Classe controleur de la vue

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{   
    $input = json_decode(file_get_contents('php://input'));
    
    $batch = Batch::withID($db, $input->batchId, false);
    
    //Nouveaux débuts et fins de Batch
    $debut = null;
    $duration = null;
    if($input->allDay=="true")
    {	
        $debut = DateTime::createFromFormat('Y-m-d', str_replace('T', ' ', $input->debut));
        $fin = DateTime::createFromFormat('Y-m-d', str_replace('T', ' ', $input->fin));
    } 
    else 
    {
        $debut = DateTime::createFromFormat('Y-m-d H:i:s', str_replace('T', ' ', $input->debut));
        $fin = DateTime::createFromFormat('Y-m-d H:i:s', str_replace('T', ' ', $input->fin));
    }
    
    // Modification des DateTime de début et de fin de Batch
    $db = new FabPlanConnection();
    
    try
    {
        $db->getConnection()->startTransaction();
        $batch->save();
        $db->getConnection()->commit();
    }
    catch(\Exception $e)
    {
        $db->getConnection()->rollBack();
        throw $e;
    }
    finally
    {
        $db = null;
    }
    
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = PlanificateurController::couleurEtat($batch->getStatus(), $fin);
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
