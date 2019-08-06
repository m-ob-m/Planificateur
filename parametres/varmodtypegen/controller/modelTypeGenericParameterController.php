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
require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
require_once __DIR__ . '/../model/modelTypeGenericParameter.php';	// Classe de parametre

class ModelTypeGenericParameterController
{
    private $_db;
    private $_modelTypeGeneric;
    
    /**
     * ModelTypeGenericParameterController
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return modelTypeGenericParameterController
     */
    function __construct()
    {
        $this->_db = new FabPlanConnection();
    }
    
    /**
     * get a ModelTypeGenericParameter by import_no, model_id and parameter_key
     *
     * @param int $modelId The id of a Model
     * @param int $typeNo The importNo of a type
     * @param string $key The key of a parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeGenericParameter The ModelTypeGenericParameter object with $modelId as model id and $typeNo as type import no and 
     *                                   $key as key
     */
    public function getModelTypeGenericParameter(int $modelId, int $typeNo, string $key) : ?ModelTypeGenericParameter
    {
        $this->_modelTypeGeneric = ModelTypeGenericParameter::withID($this->connexion(), $modelId, $typeNo, $key);
        return $this->_modelTypeGeneric;
    }
    
    public function connexion()
    {
        return $this->_db;
    }
}

?>