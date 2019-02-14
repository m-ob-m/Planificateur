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
include_once __DIR__ . "/../../../lib/fileFunctions/fileFunctions.php";	// Classe de fonctions liées aux fichiers

//Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $input =  json_decode(file_get_contents("php://input"));
    
    // Vérification des paramètres
    $testId = (isset($input->testId) ? $input->testId : null);
    $testName = (isset($input->testName) ? $input->testName : null);
    $modelId = (isset($input->modelId) ? $input->modelId : null);
    $typeNo = (isset($input->typeNo) ? $input->typeNo : null);
    $mpr = (isset($input->mpr) ? $input->mpr : null);
    $newParameters = (isset($input->parameters) ? $input->parameters : array());
    
    $testName = pathinfo(
        \FileFunctions\PathSanitizer::sanitize(
            "{$testName}.mpr", 
            array(
                "inputPathDelimiter" => "",
                "allowSlashesInFilename" => false,
                "transliterate" => true,
                "fullyPortable" => true
            )
        ), 
        PATHINFO_FILENAME
    );
    
    $test = null;
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        if(!empty($testId))
        {
            $test = \Test::withID($db, $testId)
                ->setName($testName)
                ->setModelId($modelId)
                ->setTypeNo($typeNo)
                ->setFichierMpr($mpr)
                ->save($db);
        }
        else
        {
            $test = (new \Test(null, $testName, $modelId, $typeNo, $mpr))->save($db);
        }
        saveModifiedParameters($newParameters, $test, $db);
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
    $responseArray["success"]["data"] = $test->getId();
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
 * Saves the modified parameters to the database.
 *
 * @param array $newParameters The parameters to save.
 * @param \Test $test A test from the database (must have an id)
 * @param \FabPlanConnection $db A connection to the database
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return
 */ 
function saveModifiedParameters(array $newParameters, Test $test, \FabPlanConnection $db)
{
    foreach($newParameters as $newParameter)
    {
        $testParameter = new TestParameter($test->getId(), $newParameter->key, $newParameter->value, $newParameter->description);
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