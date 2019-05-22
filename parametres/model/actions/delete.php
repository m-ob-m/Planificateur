<?php 
/**
 * \name		delete.php
 * \author    	Marc-olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-03-21
 *
 * \brief 		Suppression d'un modèle
 * \details 	Suppression d'un modèle
 */

// INCLUDE
include_once __DIR__ . "/../controller/modelController.php";

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $input =  json_decode(file_get_contents("php://input"));
    
    // Vérification des paramètres
    $id = (isset($input->id) ? $input->id : null);
    
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $model = \Model::withID($db, $id, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
        if($model === null)
        {
            throw new \Exception("Il n'y a aucun modèle possédant l'identifiant unique \"{$id}\".");
        }
        
        $model->delete($db);
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
    $responseArray["success"]["message"] = null;
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