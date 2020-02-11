<?php

/**
 * \name		ModelTypeGenericParameterController
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2017-03-28
 *
 * \brief 		Contrôleur de paramètre de combinaison modèle-type-générique
 * \details 	Contrôleur de paramètre de combinaison modèle-type-générique
 */

/*
 * Includes
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/varmodtypegen/model/modelTypeGenericParameter.php";

class ModelTypeGenericParameterController
{
    private $_db;
    
    /**
     * ModelTypeGenericParameterController
     * @param \FabplanConnection $db A database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \ModelTypeGenericParameterController
     */
    function __construct(\FabplanConnection $db)
    {
        $this->_db = $db;
    }
    
    /**
     * get a ModelTypeGenericParameter by Type import number, Model id and parameter key
     *
     * @param int $modelId The id of a Model
     * @param int $typeNo The importNo of a Type
     * @param string $key The key of a ModelTypeGenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \ModelTypeGenericParameter The ModelTypeGenericParameter object with $modelId as Model id and $typeNo as Type 
     *                                    import number and $key as key
     */
    public function getModelTypeGenericParameter(int $modelId, int $typeNo, string $key) : ?\ModelTypeGenericParameter
    {
        return \ModelTypeGenericParameter::withID($this->_db, $modelId, $typeNo, $key);
    }
}

?>