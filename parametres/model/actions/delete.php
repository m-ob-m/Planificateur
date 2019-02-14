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
    
    if($id <> null)
    {
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            \Model::withID($id)->delete($db);
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
    }
    else
    {
        throw new Exception("Cannot delete a model that has a null id.");
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