<?php

/**
 * \name		modelTypeParameterController
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2017-03-28
 *
 * \brief 		Contrôleur de paramètre de combinaison modèle-type
 * \details 	Contrôleur de paramètre de combinaison modèle-type
 */

/*
 * Includes
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/model/modelTypeParameter.php";

class ModelTypeParameterController
{
    private $_db;
    
    /**
     * ModelTypeParameterController
     * @param \FabplanConnection $db A database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \ModelTypeParameterController
     */
    function __construct(\FabplanConnection $db)
    {
        $this->_db = $db;
    }
    
    /**
     * get a modelTypeParameter by import_no, model_id and parameter_key
     *
     * @param int $modelId The id of a Model
     * @param int $typeNo The importNo of a type
     * @param string $key The key of a parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \ModelTypeParameter The ModelTypeParameter object with $modelId as model id and $typeNo as type import no and $key as key
     */
    public function getModelTypeParameter(int $modelId, int $typeNo, string $key) : ?\ModelTypeParameter
    {
        return \ModelTypeParameter::withID($this->_db, $modelId, $typeNo, $key);
    }
}

?>