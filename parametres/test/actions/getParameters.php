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
include_once __DIR__ . '/../controller/testController.php'; //Contrôleur de test
include_once __DIR__ . '/../../varmodtypegen/controller/modelTypeGenericController.php';
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    // Vérification des paramètres
    $testId = $_GET["testId"] ?? null;
    
    // Get the information
    $parameters = createTestParametersView($testId);
    
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
 * Generate a view for the Test interface
 *
 * @param int $testId The unique numerical identifier of the Test for which the view must be created
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return array The array containing the fields of the view.
 */ 
function createTestParametersView(int $testId) : array
{
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $test = \Test::withID($db, $testId);
        $modelTypeGeneric = (new \ModelTypeGeneric($test->getModel(), $test->getType()))->loadParameters($db);  
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
    foreach($test->getParameters() as $testParameter)
    {
        $key = $testParameter->getKey();
        $defaultValue = null;
        
        // Fill the result array
        foreach($modelTypeGeneric->getParameters() as $modelTypeGenericParameter)
        {
            if($modelTypeGenericParameter->getKey() === $key)
            {
                $specific = $modelTypeGenericParameter->getSpecificValue();
                $default = $modelTypeGenericParameter->getDefaultValue();
                $defaultValue = ($specific !== null) ? $specific : $default;
                break;
            }
        }
        
        array_push($parameters, 
            array(
                "key" => $key, 
                "specificValue" => $testParameter->getValue(), 
                "description" => $testParameter->getDescription(),
                "defaultValue" => $defaultValue
            )
        );
    }
    
    return $parameters;
}
?>