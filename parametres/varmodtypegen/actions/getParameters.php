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
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $input =  json_decode(file_get_contents("php://input"));
    
    // Vérification des paramètres
    if(!isset($_GET["modelId"]))
    {
        $modelId = null;
    }
    elseif(preg_match("/^\d+$/", $_GET["modelId"]))
    {
        $modelId = intval($_GET["modelId"]);
    }
    else
    {
        throw new \Exception("An invalid model id was provided.");
    }
    
    // Vérification des paramètres
    if(!isset($_GET["typeNo"]))
    {
        $typeNo = null;
    }
    elseif(preg_match("/^\d+$/", $_GET["typeNo"]))
    {
        $typeNo = intval($_GET["typeNo"]);
    }
    else
    {
        throw new \Exception("An invalid type import number was provided.");
    }
    
    // Get the information
    $parameters = createModelTypeGenericParametersView($modelId, $typeNo);
    
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
        $modelTypeGeneric = (new \ModelTypeGeneric($modelId, $typeNo))->loadParameters($db);
        $generic = Generic::withID($db, $modelTypeGeneric->getGenericId());
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
                "quickEdit" => $generic->getGenericParameterByKey($key)->getQuickEdit()
            )
        );
    }
    
    return $parameters;
}
?>