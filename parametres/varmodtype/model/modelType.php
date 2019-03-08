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

class ModelType implements \JsonSerializable
{
	protected $_model;
	protected $_type;
	protected $_parameters;
    
	/**
	 * Build a new model/type combination.
	 *
	 * @param \Model $model The model of the combination.
	 * @param \type $type The type of the combination.
	 * @param array $parameters The parameters of the model/type combination 
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return ModelType This ModelType (for method chaining)
	 */
	public function __construct(?\Model $model = null, ?\Type $type = null, array $parameters = array())
	{
		$this->setModel($model);
		$this->setType($type);
		$this->setParameters($parameters);
	}

	/**
	 * Load test type parameters from the database  for the specified model/type combination.
	 *
	 * @param \FabPlanConnection $db The database containing parameters to fetch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \ModelType This \ModelType (for method chaining)
	 */
	public function loadParameters(\FabPlanConnection $db)
	{
	    $stmt = $db->getConnection()->prepare(
            "SELECT `dmd`.`paramKey` AS `key`, `dmd`.`paramValue` AS `value`
            FROM `fabplan`.`door_model` AS `dm` 
            INNER JOIN `fabplan`.`door_model_data` AS `dmd` 
            ON `dmd`.`fkDoorType` = :typeNo AND `dmd`.`fkDoorModel` = `dm`.`id_door_model` 
                AND `dm`.`id_door_model` = :modelId;"
        );
	    $stmt->bindValue(':modelId', $this->getModel()->getId(), \PDO::PARAM_INT);
	    $stmt->bindValue(':typeNo', $this->getType()->getImportNo(), \PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $this->setParameters(array());
	    foreach($stmt->fetchAll() as $row)
	    {
	        $modelId = $this->getModel()->getId();
	        $typeNo = $this->getType()->getImportNo();
	        array_push(
	            $this->_parameters, 
	            new \ModelTypeParameter($row['key'], $row['value'], $modelId, $typeNo)
	        );
	    }
	    
	    return $this;
	}
    
	/**
	 * Set the type No
	 *
	 * @param \Type $type The new type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \ModelType This \ModelType (for method chaining)
	 */
	public function setType(?\Type $type) : \ModelType
	{
	    $this->_type = $type;
	    return $this;
	}
	
	/**
	 * Set the model
	 *
	 * @param \Model $model The new model
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \ModelType This \ModelType (for method chaining)
	 */
	public function setModel(?\Model $model) : \ModelType
	{
	    $this->_model = $model;
	    return $this;
	}
	
	/**
	 * Set the parameters array of this ModelType object
	 *
	 * @param array $parameters The new parameters array of this ModelType
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \ModelType This \ModelType (for method chaining)
	 */
	public function setParameters(array $parameters) : \ModelType
	{
	    $this->_parameters = $parameters;
	    return $this;
	}
	
	/**
	 * Get the model
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Model The model
	 */
	public function getModel() : ?\Model
	{
	    return $this->_model;
	}
	
	/**
	 * Get the type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Type The type
	 */
	public function getType() : ?\Type
	{
	    return $this->_type;
	}
	
	/**
	 * Get the parameters
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array[\ModelTypeParameter] The parameters of this \Modeltype
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
	 * @return string array The parameters of this \Modeltype
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
	 * @return string array The parameters of this \Modeltype
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