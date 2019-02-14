<?php
/**
 * \name		save.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-04-18
 *
 * \brief 		Sauvegarde un Materiel
 * \details     Sauvegarde un Materiel
 */

// INCLUDE
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/../controller/typeController.php';

//Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $input =  json_decode(file_get_contents("php://input"));
    
    $id = (isset($input->id) ? intval($input->id) : null);
    $importNo = (isset($input->importNo) ? intval($input->importNo) : null);
    $description = (isset($input->description) ? $input->description : null);
    $genericId = (isset($input->genericId) ? $input->genericId : null);
    
    $copyParametersFrom = null;
    if(isset($input->copyParametersFrom))
    {
        $copyParametersFrom = $input->copyParametersFrom;
        if(intval($copyParametersFrom) > 0 || $copyParametersFrom === null)
        {
            // Do nothing
        }
        elseif($copyParametersFrom === "")
        {
            $copyParametersFrom = null;
        }
        else
        {
            throw new \Exception("The id of the Type to copy should be a positive integer or null.");
        }
    }
    
    $type = saveType(new Type($id, $importNo, $description, $genericId), $copyParametersFrom);
    
    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = $type->getId();
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
 * Saves the Type
 *
 * @param Type $type The Type to save
 * @param int $referenceId The id of the Type to copy parameters from
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return Type The saved Type
 */
function saveType(\Type $type, ?int $referenceId = null) : \Type
{
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $type->save($db);
        if($referenceId !== null)
        {
            // Only on insert
            $parameters = \Type::withId($db, $referenceId)->getModelTypeParametersForAllModels($db);
            foreach($parameters as $parameter)
            {
                $parameter->setTypeNo($type->getImportNo())->save($db);
            }
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
    
    return $type;
}
?>