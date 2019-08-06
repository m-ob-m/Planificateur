<?php

/**
 * \name		modelTypeGenericController
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
require_once __DIR__ . '/../model/modelTypeGeneric.php';	// Classe de modèle-type-générique

class ModelTypeGenericController
{
    private $_db;
    
    /**
     * ModelTypeGenericController
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeGenericController
     */ 
    function __construct()
    {
        $this->_db = new FabPlanConnection();
    }
    
    /**
	 * get a ModelTypeGeneric by import_no and by model_id
	 *
	 * @param int $modelId The id of a Model
	 * @param int $typeNo The importNo of a type
	 *
	 * @throws 
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \ModelTypeGeneric The ModelTypeGeneric object with $modelId as model id and $typeNo as type import number
	 */ 
    public function getModelTypeGeneric(int $modelId, int $typeNo) : \ModelTypeGeneric
    {
        $model = \Model::withID($this->_db, $modelId);
        $type = \Type::withImportNo($this->_db, $typeNo);
        return (new \ModelTypeGeneric($model, $type))->loadParameters($this->_db);
    }
    
    public function connexion()
    {
        return $this->_db;
    }
}

?>