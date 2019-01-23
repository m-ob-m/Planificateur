<?php

/**
 * \name		ModeleType
* \author    	Marc-Olivier Bazin-Maurice
* \version		1.0
* \date       	2017-03-20
*
* \brief 		Représente toutes les valeurs d'un modèle/type
* \details 		Représente toutes les valeurs d'un modèle/type
*/

include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/modelTypeParameter.php'; // Classe de paramètres pour cet objet

class ModelType implements JsonSerializable
{
	protected $_modelId;
	protected $_typeNo;
	protected $_parameters;
    
	/**
	 * Build a new model/type combination.
	 *
	 * @param int $modelId The model id of the combination.
	 * @param int $typeId The type id of the combination.
	 * @param array $parameters The parameters of the model/type combination 
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return ModelType This ModelType (for method chaining)
	 */
	public function __construct(?int $modelId = null, ?int $typeNo = null, array $parameters = array())
	{
		$this->setModelId($modelId);
		$this->setTypeNo($typeNo);
		$this->setParameters($parameters);
	}

	/**
	 * Load test type parameters from the database  for the specified model/type combination.
	 *
	 * @param FabPlanConnection $db The database containing parameters to fetch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return ModelType This ModelType (for method chaining)
	 */
	public function loadParameters(FabPlanConnection $db)
	{
	    $stmt = $db->getConnection()->prepare("
            SELECT `dmd`.`paramKey` AS `key`, `dmd`.`paramValue` AS `value`
            FROM `fabplan`.`door_model` AS `dm` 
            INNER JOIN `fabplan`.`door_model_data` AS `dmd` 
                ON `dmd`.`fkDoorType` = :typeNo AND `dmd`.`fkDoorModel` = `dm`.`id_door_model` AND `dm`.`id_door_model` = :modelId;
        ");
	    $stmt->bindValue(':modelId', $this->getModelId(), PDO::PARAM_INT);
	    $stmt->bindValue(':typeNo', $this->getTypeNo(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $this->setParameters(array());
	    foreach($stmt->fetchAll() as $row)
	    {
	        array_push(
	            $this->_parameters, 
	            new ModelTypeParameter($row['key'], $row['value'], $this->getModelId(), $this->getTypeNo())
	        );
	    }
	    
	    return $this;
	}
    
	/**
	 * Set the type No
	 *
	 * @param int $typeNo The new type number
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return ModelType This ModelType (for method chaining)
	 */
	public function setTypeNo(?int $typeNo) : ModelType
	{
	    $this->_typeNo = $typeNo;
	    return $this;
	}
	
	/**
	 * Set the model id
	 *
	 * @param int $modelId The new model id
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return ModelType This ModelType (for method chaining)
	 */
	public function setModelId(?int $modelId) : ModelType
	{
	    $this->_modelId = $modelId;
	    return $this;
	}
	
	/**
	 * Set the parameters array of this ModelType object
	 *
	 * @param array $parameters The new parameters array of this ModelType
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return ModelType This ModelType (for method chaining)
	 */
	public function setParameters(array $parameters) : ModelType
	{
	    $this->_parameters = $parameters;
	    return $this;
	}
	
	/**
	 * Get the model id
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The new model id
	 */
	public function getModelId() : ?int
	{
	    return $this->_modelId;
	}
	
	/**
	 * Get the type number
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The new type import number
	 */
	public function getTypeNo() : ?int
	{
	    return $this->_typeNo;
	}
	
	/**
	 * Get the parameters
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return ModelTypeParameter array The parameters of this Modeltype
	 */
	public function getParameters() : array
	{
	    return $this->_parameters;
	}
	
	/**
	 * Get the parameters in the [key => value] format
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string array The parameters of this Modeltype
	 */
	public function getParametersAsKeyValuePairs() : array
	{
	    $parametersArray = array();
	    foreach($this->getParameters() as $parameter)
	    {
	        $parametersArray[$parameter->getKey()] = $parameter->getValue();
	    }
	    return $parametersArray;
	}
	
	/**
	 * Get the parameters in the [key => description] format
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string array The parameters of this Modeltype
	 */
	public function getParametersAsKeyDescriptionPairs() : array
	{
	    $parametersArray = array();
	    foreach($this->getParameters() as $parameter)
	    {
	        $parametersArray[$parameter->getKey()] = $parameter->getDescription();
	    }
	    return $parametersArray;
	}
	
	/**
	 * Get a JSON compatible representation of this object.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array This object in a JSON compatible format
	 */
	public function jsonSerialize() : ?array
	{
	    return get_object_vars($this);
	}
}

?>