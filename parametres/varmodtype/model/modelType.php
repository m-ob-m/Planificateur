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

require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
require_once __DIR__ . '/modelTypeParameter.php'; // Classe de paramètres pour cet objet

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
	 * Saves the model/type parameters to the database for the specified model/type combination.
	 *
	 * @param \FabPlanConnection $db The database where the parameters must be written.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \ModelType This \ModelType (for method chaining)
	 */
	public function save(\FabPlanConnection $db)
	{
		$model = $this->getModel();
		$modelDatabaseTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $model->getTimestampFromDatabase($db));
		$modelLocalTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $model->getTimestamp());

		$type = $this->getType();
		$typeDatabaseTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $type->getTimestampFromDatabase($db));
		$typeLocalTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $type->getTimestamp());
		
		if($this->getModel()->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
		{
			throw new \Exception("Model {$model->getDescription()} is not locked for update.");
		}
		
		if($this->getType()->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
		{
			throw new \Exception("Type {$type->getDescription()} is not locked for update.");
		}

		if($modelDatabaseTimestamp > $modelLocalTimestamp)
		{
			throw new \Exception(
				"Model {$model->getDescription()} is outdated. The last modification date of the database entry is
				\"{$modelDatabaseTimestamp->format("Y-m-d H:i:s")}\" whereas the last modification date of the local copy is
				\"{$modelLocalTimestamp->format("Y-m-d H:i:s")}\"."
			);
		}
		
		if($typeDatabaseTimestamp > $typeLocalTimestamp)
		{
			throw new \Exception(
				"Type {$type->getDescription()} is outdated. The last modification date of the database entry is
				\"{$typeDatabaseTimestamp->format("Y-m-d H:i:s")}\" whereas the last modification date of the local copy is
				\"{$typeLocalTimestamp->format("Y-m-d H:i:s")}\"."
			);
		}

		$this->emptyInDatabase($db);
		/* @var $parameter \ModelTypeParameter */
		foreach($this->getParameters() as $parameter)
		{
			$parameter->save($db);
		}
		
	    return $this;
	}

	/**
	 * Load model/type parameters from the database  for the specified model/type combination.
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
            FROM `door_model` AS `dm` 
            INNER JOIN `door_model_data` AS `dmd` 
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
	 * Deletes model/type parameters from the database for the specified model/type combination.
	 *
	 * @param \FabPlanConnection $db The database containing parameters to delete.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \ModelType This \ModelType (for method chaining)
	 */
	private function emptyInDatabase(\FabPlanConnection $db) : \ModelType
	{
	    $stmt = $db->getConnection()->prepare(
            "DELETE `door_model_data` FROM `door_model_data` 
			WHERE `door_model_data`.`fkDoorModel` = :modelId AND `door_model_data`.`fkDoorType` = :typeNo;"
        );
	    $stmt->bindValue(':modelId', $this->getModel()->getId(), \PDO::PARAM_INT);
	    $stmt->bindValue(':typeNo', $this->getType()->getImportNo(), \PDO::PARAM_INT);
	    $stmt->execute();
	    
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