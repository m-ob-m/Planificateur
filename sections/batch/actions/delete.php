<?php
/**
 * \name		delete.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-07-16
 *
 * \brief 		Supprime un Batch
 * \details     Supprime un Batch
 */

include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/../controller/batchController.php'; // Contrôleur d'un Batch
include_once __DIR__ . '/../../job/controller/jobController.php'; // Contrôleur d'un Batch


// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $input =  json_decode(file_get_contents("php://input"));
    
    $batchId = $input->batchId ?? null;
    
    if(is_scalar($batchId) && ctype_digit((string)$batchId) && (int)$batchId > 0)
    {
        $batchId = (int)$batchId;
    }
    else
    {
        throw new \Exception("L'identifiant unique fourni n'est pas valide.");
    }
    
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        \Batch::withID($db, $batchId)->delete($db);
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
    $responseArray["success"]["data"] = null;
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