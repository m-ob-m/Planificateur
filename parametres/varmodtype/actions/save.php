<?php
/**
 * \name		save.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-04-13
 *
 * \brief 		Sauvegarde les paramètres d'un modèle-type
 * \details     Sauvegarde les paramètres d'un modèle-type
 */

include '../../../lib/config.php';	// Fichier de configuration
include '../../../lib/connect.php';	// Classe de connection � la base de donn�es
include '../controller/modelTypeController.php';		// Controleur des param�tres de base des portes


// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $input =  json_decode(file_get_contents("php://input"));
    $db = new FabPlanConnection();
    
    // Vérification des paramètres
    $modelId = $input->modelId ?? null;
    $typeNo = $input->typeNo ?? null;
    $newParameters = $input->parameters ?? array();
    
    if(is_scalar($modelId) && ctype_digit((string)$modelId) && (int)$modelId > 0)
    {
        $modelId = (int)$modelId;
    }
    else
    {
        throw new \Exception("L'identifiant unique de modèle fourni \"{$modelId}\" n'est pas valide.");
    }
    
    if(is_scalar($typeNo) && ctype_digit((string)$typeNo) && (int)$typeNo >= 0)
    {
        $typeNo = (int)$typeNo;
    }
    else
    {
        throw new \Exception("Le numéro d'importation de type fourni \"{$typeNo}\" n'est pas valide.");
    }
    
    // Save the information
    $parameters = array();
    foreach($newParameters as $newParameter)
    {
        $modelTypeParameter = new \ModelTypeParameter($newParameter->key, $newParameter->value, $modelId, $typeNo);
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            if($newParameter->value === null || $newParameter->value === "")
            {
                $modelTypeParameter->delete($db);
            }
            else
            {
                $modelTypeParameter->save($db);
            }
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