<?php
/**
 * \name		getParameters.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-04-26
 *
 * \brief 		Retourne la liste de paramètres d'un modèle/type/générique
 * \details 	Retourne la liste de paramètres d'un modèle/type/générique
 */

// INCLUDE
include_once __DIR__ . '/../controller/modelTypeGenericController.php'; // Contrôleur de Modèle-Type
include_once __DIR__ . '/../../generic/controller/genericController.php'; // Contrôleur de Générique
include_once __DIR__ . '/../../model/controller/modelController.php'; // Contrôleur de Modèle
include_once __DIR__ . '/../../type/controller/typeController.php'; // Contrôleur de Type
include_once __DIR__ . '/../../../lib/numberFunctions/numberFunctions.php';	// Fonctions sur les nombres
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{    
    $modelId = is_positive_integer_or_equivalent_string($_GET["modelId"], true, true) ? intval($_GET["modelId"]) : null;
    $typeNo = is_positive_integer_or_equivalent_string($_GET["typeNo"], true, true) ? intval($_GET["typeNo"]) : null;
    
    // Get the information
    $parameters = array();
    if($modelId !== "" && $modelId !== null && $typeNo !== "" && $typeNo !== null)
    {
        $parameters = createModelTypeGenericParametersView($modelId, $typeNo);
    }
    
    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = $parameters;
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

/**
 * Generate a view for the ModelTypeGeneric interface.
 *
 * @param int $modelId The unique numerical identifier of the model to create the view for.
 * @param int $typeNo The import number of the type to create the view for.
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return array The array containing the fields of the view.
 */
function createModelTypeGenericParametersView(int $modelId, int $typeNo) : array
{
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        
        $model = \Model::withID($db, $modelId);
        if($model === null)
        {
            throw \Exception("Il n'y a pas de modèle avec l'identifiant unique \"{$modelId}\".");
        }
        
        $type = \Type::withImportNo($db, $typeNo);
        if($type === null)
        {
            throw \Exception("Il n'y a pas de type avec le numéro d'importation \"{$typeNo}\".");
        }
        
        $modelTypeGeneric = (new \ModelTypeGeneric($model, $type))->loadParameters($db);
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
    
    $parameters = array();
    foreach($modelTypeGeneric->getParameters() as $modelTypeGenericParameter)
    {   
        // Fill the result array
        $key = $modelTypeGenericParameter->getKey();
        array_push($parameters, 
            array(
                "key" => $modelTypeGenericParameter->getKey(),
                "value" => $modelTypeGenericParameter->getValue(),
                "description" => $modelTypeGenericParameter->getDescription(),
                "defaultValue" => $modelTypeGenericParameter->getDefaultValue(),
                "specificValue" => $modelTypeGenericParameter->getSpecificValue(),
                "quickEdit" => $modelTypeGeneric->getType()->getGeneric()->getParameterByKey($key)->getQuickEdit()
            )
        );
    }
    
    return $parameters;
}
?>