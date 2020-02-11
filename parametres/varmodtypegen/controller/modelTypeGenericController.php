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
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/varmodtypegen/model/modelTypeGeneric.php";

class ModelTypeGenericController
{
    private $_db;
    
    /**
     * ModelTypeGenericController
     * @param \FabplanConnection $db A database
     * 
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \ModelTypeGenericController
     */ 
    function __construct(\FabplanConnection $db)
    {
        $this->_db = $db;
    }
    
    /**
	 * get a ModelTypeGeneric by Type import no and by Model id
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
}

?>