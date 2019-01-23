<?php 
/**
 * \name		save.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-04-13
 *
 * \brief 		Sauvegarde un test
 * \details     Sauvegarde un test
 */

// INCLUDE
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/../controller/testController.php';

//Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $db = new FabPlanConnection();
    
    // Vérification des paramètres
    $id = isset($_GET["id"]) && !empty($_GET["id"]) ? $_GET["id"] : null;
    
    $test = (new TestController())->getTest($id);
    
    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = $test === null ? null : $test->getFichierMpr();
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
 * Save parameters to the database
 *
 * @param array $newParameters The parameters to save.
 * @param Test $test A test from the database (must have an id)
 * @param FabPlanConnection $db A connection to the database
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return
 */ 
function saveParameters(array $newParameters, Test $test, FabPlanConnection $db)
{
    $parameters = array();
    foreach($newParameters as $newParameter)
    {
        $testParameter = new TestParameter($test->getId(), $newParameter->key, $newParameter->value);
        if($newParameter->value === null)
        {
            $testParameter->delete($db);
        }
        else
        {
            $testParameter->save($db);
        }
    }
}
?>