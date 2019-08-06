<?php

/**
 * \name		modelTypeController
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2017-03-27
 *
 * \brief 		Contrôleur de combinaison modèle-type
 * \details 	Contrôleur de combinaison modèle-type
 */

/*
 * Includes
 */
require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
require_once __DIR__ . '/../model/modelType.php';	// Classe de parametre
require_once __DIR__ . "/../../model/controller/modelController.php"; // Classe contrôleur de Modèle

class ModelTypeController
{
    private $_db;
    private $_modelType;
    
    /**
     * ModelTypeController
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeController
     */ 
    function __construct()
    {
        $this->_db = new FabPlanConnection();
    }
    
    /**
	 * get a modelType by import_no and by model_id
	 *
	 * @param int $modelId The id of a Model
	 * @param int $typeNo The importNo of a type
	 *
	 * @throws 
	 * @author Marc-Olivier Bazin-Maurice
	 * @return ModelType The modelType object with $modelId as model id and $typeNo as type import no
	 */ 
    public function getModelType(?int $modelId, ?int $typeNo) : \ModelType
    {
        $model = \Model::withID($this->_db, $modelId);
        if($model === null)
        {
            throw new \Exception(
                "Il n'y a aucun modèle possédant l'identifiant numérique unique \"{$modelId}\"."
            );
        }
        
        $type = \Type::withImportNo($this->_db, $typeNo);
        if($type === null)
        {
            throw new \Exception(
                "Il n'y a aucun type possédant le numéro d'importation \"{$typeNo}\"."
            );
        }
        
        $this->_modelType = (new ModelType())->setModel($model)->setType($type)->loadParameters($this->_db);
        return $this->_modelType;
    }
    
    public function connexion()
    {
        return $this->_db;
    }
}

?>