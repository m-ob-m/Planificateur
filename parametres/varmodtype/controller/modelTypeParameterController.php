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
require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection � la base de donn�es
require_once __DIR__ . '/../model/modelTypeParameter.php';	// Classe de parametre

class modelTypeParameterController
{
    private $_db;
    private $_modelType;
    
    /**
     * ModelTypeParameterController
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
     * get a modelTypeParameter by import_no, model_id and parameter_key
     *
     * @param int $modelId The id of a Model
     * @param int $typeNo The importNo of a type
     * @param string $key The key of a parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeParameter The ModelTypeParameter object with $modelId as model id and $typeNo as type import no and $key as key
     */
    public function getModelTypeParameter(int $modelId, int $typeNo, string $key) : ?ModelTypeParameter
    {
        $this->_modelType = ModelTypeParameter::withID($this->connexion(), $modelId, $typeNo, $key);
        return $this->_modelType;
    }
    
    public function connexion()
    {
        return $this->_db;
    }
}

?>