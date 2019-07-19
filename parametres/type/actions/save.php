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
    include_once __DIR__ . '/../controller/typeController.php'; // Contrôlleur de Type
    include_once __DIR__ . "/../../../lib/numberFunctions/numberFunctions.php";
    
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
        if(is_positive_integer_or_equivalent_string($input->copyParametersFrom ?? null, true, true))
        {
            $copyParametersFrom = intval($input->copyParametersFrom);
        }
        
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            if($id === null)
            {
                $type = new \Type();
            }
            else
            {
                $type = \Type::withID($db, $id, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
                if($type === null)
                {
                    throw new \Exception(
                        "Il n'y a aucun type possédant l'identifiant numérique unique \"{$id}\"."
                    );
                }
            }
            
            $generic = \Generic::withID($db, $genericId);
            $type->setImportNo($importNo)->setDescription($description)->setGeneric($generic);
            $type = saveType($type, $copyParametersFrom, $db);
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
     * @param \Type $type The Type to save
     * @param int $referenceId The id of the Type to copy parameters from
     * @param \FabplanConnection $db The database where the record should be created or updated.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Type The saved Type
     */
    function saveType(\Type $type, ?int $referenceId = null, \FabplanConnection $db) : \Type
    {
        $create = ($type->getId() === null) ? true : false;
        $type->save($db);
        if($create && $referenceId !== null)
        {
            $firstParameter = true;
            
            $query = "INSERT INTO `door_model_data` (`fkDoorModel`, `fkDoorType`, `paramKey`, `paramValue`) VALUES ";
            /* @var $parameter \ModelTypeParameter */
            foreach(\Type::withId($db, $referenceId)->getModelTypeParametersForAllModels($db) as $parameter)
            {
                if(!$firstParameter)
                {
                    $query .= ", ";
                }
                $firstParameter = false;

                $modelId = $db->getConnection()->quote($parameter->getModelId());
                $typeNo = $db->getConnection()->quote($type->getImportNo());
                $key = $db->getConnection()->quote($parameter->getKey());
                $value = $db->getConnection()->quote($parameter->getValue());
                $query .= "({$modelId}, {$typeNo}, {$key}, {$value})";
            }
            $query .= ";";
            
            $stmt = $db->getConnection()->prepare($query);
            $stmt->execute();
        }
            
        return $type;
    }
?>