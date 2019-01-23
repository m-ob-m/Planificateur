<?php
/**
 * \name		save.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-03-26
 *
 * \brief 		Sauvegarde la liste de paramètres d'un générique
 * \details 	Sauvegarde la liste de paramètres d'un générique
 */

// INCLUDE
include_once __DIR__ . "/../../generic/controller/genericController.php"; // Contrôleur de Générique
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $input =  json_decode(file_get_contents("php://input"));
    
    // Vérification des paramètres
    $parameters = (isset($input->parameters) ? $input->parameters : null);
    $genericId = (isset($input->id) ? $input->id : null);
    
    $parametersArray = array();
    foreach($parameters as $parameter)
    {
        array_push(
            $parametersArray, 
            new \GenericParameter(
                null, 
                $genericId, 
                $parameter->key, 
                $parameter->value, 
                $parameter->description, 
                $parameter->quickEdit
            )
        );
    }
    
    findErrorsInParametersArray($parametersArray);
    $generic = (new GenericController())
        ->getGeneric($genericId)
        ->setGenericParameters($parametersArray)
        ->save(new FabPlanConnection(), true);
    
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

/**
 * Reports error in the parameters array
 *
 * @param array The array of parameters submitted by the user.
 *
 * @throws Exception if there is a problem with the data
 * @author Marc-Olivier Bazin-Maurice
 * @return
 */ 
function findErrorsInParametersArray(array $parameters) : void
{
    $keyRegistry = array();
    foreach($parameters as $parameter)
    {
        if($parameter->getKey() === "" || $parameter->getKey() === null)
        {
            throw new Exception("An empty key was found.");
        }
        elseif(!preg_match("/^[a-zA-Z_]\w{0,7}$/", $parameter->getKey()))
        {
            throw new Exception("The key \"{$parameter->getKey()}\" is not valid.");
        }
        elseif(array_search($parameter->getKey(), $keyRegistry, true) !== false)
        {
            throw new Exception("A duplicate of key \"{$parameter->getKey()}\" was found.");
        }
        else
        {
            array_push($keyRegistry, $parameter->getKey());
        }
    }
}
?>