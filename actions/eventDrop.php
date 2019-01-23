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
    $db = new FabPlanConnection();
    
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
    $db->getConnection()->beginTransaction();	
    
    $batch->setStart($debut->format('Y-m-d H:i:s'))->setEnd($fin->format('Y-m-d H:i:s'));
    $stmt = $db->getConnection()->prepare("
        UPDATE `fabplan`.`batch` 
        SET `date_debut` = :startDate, `date_fin` = :endDate, `estampille` = CURRENT_TIMESTAMP 
        WHERE `id_batch` = :batchId;
    ");
    $stmt->bindValue(":startDate", $batch->getStart(), PDO::PARAM_STR);
    $stmt->bindValue(":endDate", $batch->getEnd(), PDO::PARAM_STR);
    $stmt->bindValue(":batchId", $batch->getId(), PDO::PARAM_INT);
    $stmt->execute();
    
    // Envoi des transactions à la BD
    $db->getConnection()->commit();	
    
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = PlanificateurController::couleurEtat($batch->getStatus(), $fin);
    
    // Fermeture de la connection
    $db = NULL;
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
