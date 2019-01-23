<?php
/**
 * \name		getParameters.php
 * \author    	Marc-olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-03-27
 *
 * \brief 		Retourne la liste de paramètres d'un modèle/type
 * \details 	Retourne la liste de paramètres d'un modèle/type 
 */

// INCLUDE
include_once __DIR__ . '/../../generic/controller/genericController.php'; //Contrôleur de générique
include_once __DIR__ . '/../../type/controller/typeController.php'; //Contrôleur de type
include_once __DIR__ . '/../controller/modelTypeController.php'; // Contrôleur de Modèle-Type
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    // Vérification des paramètres
    $modelId = $_GET["modelId"] ?? null;
    $typeNo = $_GET["typeNo"] ?? null;
    
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
    
    // Get the information
    $modelType = (new ModelTypeController())->getModelType($modelId, $typeNo);
    $parameters = createModelTypeParametersView($modelType);
    
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
 * Generate a view for the ModelType interface
 *
 * @param ModelType $modelType The modelType object for which the view must be created
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return array The array containing the fields of the view.
 */ 
function createModelTypeParametersView(ModelType $modelType) : array
{
    $type = (new TypeController())->getTypeByImportNo($modelType->getTypeNo());
    $generic = (new GenericController())->getGeneric($type->getGenericId());
    
    $parameters = array();
    foreach($generic->getGenericParameters() as $genericParameter)
    {
        $key = $genericParameter->getKey();
        $value = null;
        foreach($modelType->getParameters() as $modelTypeParameter)
        {
            if($modelTypeParameter->getKey() === $key)
            {
                $value = $modelTypeParameter->getValue();
                break;
            }
        }
        
        // Fill the result array
        array_push($parameters, array(
                "key" => $key, 
                "value" => $value, 
                "description" => $genericParameter->getDescription(),
                "defaultValue" => $genericParameter->getValue()
            )
        );
    }
    
    return $parameters;
}
?>