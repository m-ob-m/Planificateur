<?php 
/**
 * \name		save.php
 * \author    	Marc-olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-03-21
 *
 * \brief 		Sauvegarde d'un générique
 * \details 	Sauvegarde d'un générique
 */

// INCLUDE
include_once __DIR__ . "/../controller/genericController.php"; // Contrôleur de Générique
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $input =  json_decode(file_get_contents("php://input"));
    
    // Vérification des paramètres
    $id = (isset($input->id) ? $input->id : null);
    $filename = (isset($input->filename) ? $input->filename : null);
    $description = (isset($input->description) ? $input->description : null);
    $heightParameter = (isset($input->heightParameter) ? $input->heightParameter : null);
    $copyParametersFrom = (isset($input->copyParametersFrom) ? $input->copyParametersFrom : null);
    
    $generic = saveGeneric((new Generic($id, $filename, $description, $heightParameter)), $copyParametersFrom);
    
    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = $generic->getID();
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
 * Saves a Generic into the database.
 *
 * @param Generic $generic A Generic that is to receive parameters from the Generic designated by $referenceId
 * @param int $referenceId The id of an existing Generic from which parameters will be fetched to intialize the new Generic
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return 
 */
function saveGeneric(Generic $generic, ?int $referenceId) :?Generic
{
    $db = new FabPlanConnection();
    $generic->save($db, false);
    if($referenceId <> null)
    {
        // Only on insert
        $parameters = (new GenericController())->getGeneric($referenceId)->getGenericParameters();
        $generic = copyGenericParameters($generic, $parameters);
        $generic->save($db, true);
    }
    return $generic;
}

/**
 * Copies a list of GenericParameters in a Generic, setting the GenericParameters' GenericId to the right value.
 *
 * @param Generic $generic The Generic in which parameters should be inserted
 * @param GenericParameter array $parameterList The List of GenericParameters to add in the Generic object
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return Generic The modified Generic object
 */ 
function copyGenericParameters(Generic $generic, array $parameterList) : Generic
{
    if(!empty($parameterList))
    {
        foreach($parameterList as $parameter)
        {
            $generic->addGenericParameter($parameter->setGenericId($generic->getId()));
        }
    }
    return $generic;
}

?>