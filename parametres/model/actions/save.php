<?php 
    /**
     * \name		save.php
     * \author    	Marc-olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-03-21
     *
     * \brief 		Sauvegarde d'un modèle
     * \details 	Sauvegarde d'un modèle
     */
    
    // INCLUDE
    include_once __DIR__ . "/../controller/modelController.php"; // Contrôleur de Model
    include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
    include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    try
    {
        $input =  json_decode(file_get_contents("php://input"));
        
        // Vérification des paramètres
        $id = (isset($input->id) ? $input->id : null);
        $description = (isset($input->description) ? $input->description : null);
        $copyParametersFrom = (isset($input->copyParametersFrom) ? $input->copyParametersFrom : null);
        
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            if($id === null)
            {
                $model = new \Model();
            }
            else
            {
                $model = \Model::withID($db, $id, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
                if($model === null)
                {
                    throw new \Exception("Il n'y a aucun modèle possédant l'identifiant numérique unique \"{$id}\".");
                }
            }
            
            $model = saveModel($model->setDescription($description), $copyParametersFrom, $db);
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
        $responseArray["success"]["data"] = $model->getId();
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
     * Saves the Model
     *
     * @param \Model $model The Model to save
     * @param int $referenceId The id of the Model to copy parameters from
     * @param \Fabplanconnection The database in which the record should be saved
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Model The saved Model
     */
    function saveModel(\Model $model, ?int $referenceId = null, \FabplanConnection $db) : \Model
    {
        $create = ($model->getId() === null) ? true : false;
        $model->save($db);
        
        if($create && $referenceId !== null)
        {
            // Only on insert, fetch parameters from a reference model.
            $model = \Model::withID($db, $model->getId(), \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
            $referenceModel = \Model::withID($db, $referenceId, \MYSQLDatabaseLockingReadTypes::FOR_SHARE);
            
            /* @var $parameter \ModelTypeParameter */
            foreach($referenceModel->getModelTypeParametersForAllTypes($db) as $parameter)
            {
                $parameter->setModelId($model->getId())->save($db);
            }
        }
    
        return $model;
    }
?>