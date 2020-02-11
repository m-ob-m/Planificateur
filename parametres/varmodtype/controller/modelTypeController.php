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
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/varmodtype/model/modelType.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/model/controller/modelController.php";

class ModelTypeController
{
    private $_db;
    
    /**
     * ModelTypeController
     * @param \FabplanConnection $db A database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \ModelTypeController This ModelTypeController
     */ 
    function __construct(\FabplanConnection $db)
    {
        $this->_db = $db;
    }
    
    /**
	 * get a ModelType by Type import number and by Model id
	 *
	 * @param int $modelId The id of a Model
	 * @param int $typeNo The importNo of a Type
	 *
	 * @throws 
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \ModelType The ModelType object with $modelId as Model id and $typeNo as Type import number
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
        
        return (new \ModelType())->setModel($model)->setType($type)->loadParameters($this->_db);
    }
}

?>