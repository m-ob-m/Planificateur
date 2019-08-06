<?php
/**
 * \name		TestType
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2017-10-31
 *
 * \brief 		Modele de testType
 * \details 	Modele de testType
 */

require_once __DIR__ . '/testParameter.php';
require_once __DIR__ . '/../../type/controller/typeController.php';
require_once __DIR__ . '/../../model/controller/modelController.php';
require_once __DIR__ . '/../../varmodtype/model/modeltype.php';
require_once __DIR__ . '/../../varmodtypegen/model/modelTypeGeneric.php';

class Test extends ModelTypeGeneric implements JsonSerializable
{
	private $_id;
	private $_name;
	private $_fichier_mpr;
	private $_estampille;
	private $__database_connection_locking_read_type = \MYSQLDatabaseLockingReadTypes::NONE;
	
	
	/**
	 * Test constructor
	 *
	 * @param int $id The id of the Test in the database
	 * @param string $name The name of the Test
	 * @param \Model $model The Model associated with this Test (the one that was modified)
	 * @param \Type $type The Type associated with this Test (the one that was modified)
	 * @param string $fichierMpr The contents of the .mpr file associated to this Test if not using a generic file
	 * @param string $estampille A timestamp of the last modification applied to this Test (leave null)
	 * @param string $testParameters An array containing the TestTypeParameters objects associated with this Test.
	 *
	 * @throws 
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test
	 */ 
	public function __construct(?int $id = null, ?string $name = null, ?\Model $model = null, ?\Type $type = null, 
	    ?string $fichierMpr = null, ?string $timestamp = null, array $testParameters = array())
	{
	    parent::__construct($model, $type, $testParameters);
	    $this->setName($name);
	    $this->setFichierMpr($fichierMpr);
	    $this->setTimestamp($timestamp);
	    $this->setId($id);
	}
	
	/**
	 * Test constructor using ID of existing record
	 *
	 * @param FabPlanConnection $db The database in which the record exists
	 * @param int $id The id of the record in the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test The Test associated to the specified ID in the specified database
	 */ 
	static function withID(\FabPlanConnection $db, ?int $id, int $databaseConnectionLockingReadType = 0) : ?\Test
	{ 	    
	    // Récupérer le test
	    $stmt = $db->getConnection()->prepare(
            "SELECT `t`.* FROM `test` AS `t` WHERE `t`.`id` = :id " . 
	        (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	// Récupération de l'instance de test
	    {
	        $model = \Model::withId($db, $row["door_model_id"]);
	        $type = \Type::withImportNo($db, $row["type_no"]);
	        $instance = new self($row["id"], $row["name"], $model, $type, $row["fichier_mpr"], $row["estampille"]);
	    }
	    else
	    {
	        return NULL;
	    }
	    
	    //Récupérer les paramètres
	    $stmt = $db->getConnection()->prepare(
            "SELECT `gp`.`key` AS `key`, 
				IF(`tp`.`value` IS NULL, `gp`.`value`, `tp`.`value`) AS `value`,
				`gp`.`description` AS `description`
			FROM  `test` AS `t`
			INNER JOIN `door_types` AS `dt` ON `dt`.`importNo` = `t`.`type_no`
			INNER JOIN `generics` AS `g` ON `g`.`id` = `dt`.`generic_id`
			INNER JOIN LATERAL(
				SELECT `gp`.`id` AS `id`, 
					`gp`.`parameter_key` AS `key`, 
					`gp`.`parameter_value` AS `value`, 
					`gp`.`description` AS `description`
				FROM `generic_parameters` AS `gp` 
				WHERE `gp`.`generic_id` = `g`.`id`
			) AS `gp`
			LEFT JOIN LATERAL(
				SELECT `tp`.`parameter_key` AS `key`, `tp`.`parameter_value` AS `value` 
				FROM `test_parameters` AS `tp` 
				WHERE `t`.`id` = `tp`.`test_id`
			) AS `tp` ON `tp`.`key` = `gp`.`key`
			WHERE `t`.`id` = :id
			ORDER BY `gp`.`id` ASC " . 
	        (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    while($row = $stmt->fetch())	// Récupération de l'instance TestParameter
	    {
	        $instance = $instance->addParameter(
	            new \TestParameter($id, $row["key"], $row["value"], $row["description"])
	        );
	    }
	    
	    $instance->setDatabaseConnectionLockingReadType($databaseConnectionLockingReadType);
	    return $instance;
	}

	/**
	 * Test constructor using ModelTypeGeneric
	 *
	 * @param \ModelTypeGeneric $modelTypeGeneric The ModelTypeGeneric to use as a base to create the Test
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Test The Test object
	 */ 
	static function fromModelTypeGeneric(\ModelTypeGeneric $modelTypeGeneric) : \ModelType
	{
	    $model = $modelTypeGeneric->getModel();
	    $type = $modelTypeGeneric->getType();
	    $instance = (new self())->setModel($model)->setType($type)->setParameters(array());
	    
	    foreach($modelTypeGeneric->getParameters() as $parameter)
	    {
	        $instance->addParameter(
	            new \TestParameter(null, $parameter->getKey(), $parameter->getValue(), $parameter->getDescription())
	        );
	    }
	    
	    return $instance;
	}
	
	/**
	 * Save the Test object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be saved
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test This Test (for method chaining)
	 */ 
	public function save(FabPlanConnection $db) : Test
	{
	    if($this->getId() === null)
	    {
	        $this->insert($db);
	    }
	    else
	    {
	        $dbTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $this->getTimestampFromDatabase($db));
	        $localTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $this->getTimestamp());
	        if($this->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
	        {
	            throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
	        }
	        elseif($dbTimestamp > $localTimestamp)
	        {
	            throw new \Exception(
	                "The provided " . get_class($this) . " is outdated. The last modification date of the database entry is
                    \"{$dbTimestamp->format("Y-m-d H:i:s")}\" whereas the last modification date of the local copy is
                    \"{$localTimestamp->format("Y-m-d H:i:s")}\"."
	            );
	        }
	        else
	        {
	            $this->update($db);
	        }
	    }
	    
	    // Récupération de l'estampille à jour
	    $this->setTimestamp($this->getTimestampFromDatabase($db));
	    
	    return $this;
	}
	
	/**
	 * Insert the Test object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be inserted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test This Test (for method chaining)
	 */ 
	private function insert(FabPlanConnection $db) : Test
	{
	    // Création d'un type de test
	    $stmt = $db->getConnection()->prepare("
            INSERT INTO `test` (`id`, `name`, `door_model_id`, `type_no`, `fichier_mpr`)
            VALUES (:id, :name, :door_model_id, :type_no, :fichier_mpr)
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->bindValue(':name', $this->getName(), PDO::PARAM_STR);
	    $stmt->bindValue(':door_model_id', $this->getModel()->getId(), PDO::PARAM_INT);
	    $stmt->bindValue(':type_no', $this->getType()->getImportNo(), PDO::PARAM_INT);
	    $stmt->bindValue(':fichier_mpr', $this->getFichierMpr(), PDO::PARAM_STR);
	    $stmt->execute();
	    $this->setId(intval($db->getConnection()->lastInsertId()));
        
	    $this->deleteParametersFromDatabase($db);
	    
        foreach($this->getParameters() as $parameter)
        {
            $parameter->save($db); // Mise à jour des TestParameter
        }
        
        return $this;
	}
	
	/**
	* Update the Test object in the database
	*
	* @param FabPlanConnection $db The database in which the record must be updated
	*
	* @throws
	* @author Marc-Olivier Bazin-Maurice
	* @return Test This Test (for method chaining)
	*/ 
	private function update(FabPlanConnection $db) : Test
	{
	    // Mise à jour d'un testType
	    $stmt = $db->getConnection()->prepare("
            UPDATE `test` AS `t`
            SET `name` = :name, `door_model_id` = :door_model_id, `type_no` = :type_no, `fichier_mpr` = :fichier_mpr
            WHERE `id` = :id;
        ");
        $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
        $stmt->bindValue(":name", $this->getName(), PDO::PARAM_STR);
        $stmt->bindValue(':door_model_id', $this->getModel()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':type_no', $this->getType()->getImportNo(), PDO::PARAM_INT);
        $stmt->bindValue(':fichier_mpr', $this->getFichierMpr(), PDO::PARAM_STR);
        $stmt->execute();
        
        $this->deleteParametersFromDatabase($db);
        
        foreach($this->getParameters() as $parameter)
        {
            $parameter->save($db); // Mise à jour des TestParameter
        }
        
        return $this;
	}
	
	/**
	 * Delete the Test object from the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test This Test (for method chaining)
	 */
	public function delete(FabPlanConnection $db) : Test
	{
	    if($this->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
	    {
	        throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
	    }
	    else
	    {
    	    $stmt = $db->getConnection()->prepare("DELETE FROM `test` WHERE `test`.`id` = :id;");
    	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
    	    $stmt->execute();
	    }
	    
	    return $this;
	}
	
	/**
	 * Gets the last modification date timestamp of the database instance of this object
	 *
	 * @param \FabPlanConnection $db The database from which the timestamp should be fetched.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The last modification date timestamp of the database instance of this object.
	 */
	public function getTimestampFromDatabase(\FabPlanConnection $db) : ?string
	{
	    $stmt= $db->getConnection()->prepare("
            SELECT `t`.`estampille` FROM `test` AS `t` WHERE `t`.`id` = :id;
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if($row = $stmt->fetch())
	    {
	        return $row["estampille"];
	    }
	    else
	    {
	        return null;
	    }
	}
	
	/**
	 * Load TestParameters from the database for the specified ModelTypeGeneric combination considering generic as 
	 * independent from type (due to its nature, Test is a case of ModelTypeGeneric where the generic's id might be 
	 * different from the default value which is the one specified in the type's properties).
	 *
	 * @param \FabPlanConnection $db The database containing parameters to fetch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Test This Test (for method chaining)
	 */
	public function loadParameters(\FabPlanConnection $db) : \ModelTypeGeneric
	{
	    $stmt = $db->getConnection()->prepare(
        	"SELECT `gp`.`parameter_key` AS `key`, `dmd`.`paramValue` AS `specificValue`, 
                `gp`.`parameter_value` AS `genericValue`, `gp`.`description` AS `description`
        	FROM `door_types` AS `dt`
        	INNER JOIN `generics` AS `g` ON `dt`.`generic_id` = `g`.`id` AND `dt`.`importNo` = :typeNo
        	INNER JOIN `generic_parameters` AS `gp` ON `gp`.`generic_id` = `g`.`id`
        	INNER JOIN `door_model` AS `dm` ON `dm`.`id_door_model` = :modelId
        	LEFT JOIN `door_model_data` AS `dmd` ON `dmd`.`paramKey` = `gp`.`parameter_key`
        		AND `dmd`.`fkDoorModel` = `dm`.`id_door_model` AND `dmd`.`fkDoorType` = `dt`.`importNo`
            ORDER BY `gp`.`id` ASC " . 
	        (new \MYSQLDatabaseLockingReadTypes($this->getDatabaseConnectionLockingReadType()))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':modelId', $this->getModel()->getId(), PDO::PARAM_INT);
	    $stmt->bindValue(':typeNo', $this->getType()->getImportNo(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $this->setParameters(array());
	    foreach($stmt->fetchAll() as $row)
	    {
	        $value = ($row['specificvalue'] !== null) ? $row['specificValue'] : $row['genericValue'];
	        $this->addParameter(new \TestParameter($this->getId(), $row['key'], $value));
	    }
	    
	    return $this;
	}
	
	/**
	 * Removes all TestParameters associated to this Test without deleting TestParameters in this object.
	 * This allows removal of obsolete variables that are not part of the Test object's parameters anymore, but still 
	 * subsist in the database. 
	 *
	 * @param \FabPlanConnection $db The database containing the Test and its parameters.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test This Test (for method chaining)
	 */
	private function deleteParametersFromDatabase(\FabPlanConnection $db) : Test
	{
	    $stmt = $db->getConnection()->prepare("
            DELETE FROM `test_parameters`
            WHERE `test_parameters`.`test_id` = :testId;
        ");
	    $stmt->bindValue(':testId', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    return $this;
	}
	
	public function addParameter(TestParameter $parameter) : Test
	{
	    array_push($this->_parameters, $parameter);
	    return $this;
	}
	
	/**
	 * Set the id of the Test
	 *
	 * @param int $id The new id (can be null if unknown yet)
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test This Test (for method chaining)
	 */ 
	public function setId(?int $id) : Test
	{
	    $this->_id = $id;
        
	    foreach($this->getParameters() as &$parameter)
	    {
	        $parameter->setTestId($id);
	    }
	    
	    return $this;
	}
	
	/**
	 * Set the name of the Test
	 *
	 * @param string $name The new name
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Test This Test (for method chaining)
	 */
	public function setName(?string $name) : \Test
	{
	    $this->_name = (($name === null) ? "" : $name);
	    return $this;
	}
	
	/**
	 * Set the timestamp of this Test
	 *
	 * @param string $timestamp The new timestamp
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Test This Test (for method chaining)
	 */
	public function setTimestamp(?string $timestamp) : \Test
	{
	    $this->_estampille = $timestamp;
	    return $this;
	}
	
	/**
	 * Set the content of the mpr file (use if not using a generic program)
	 *
	 * @param string $fichierMpr The contents of the file
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test This Test (for method chaining)
	 */ 
	public function setFichierMpr(?string $fichierMpr) : Test
	{
	    $this->_fichier_mpr = $fichierMpr;
	    return $this;
	}
	
	/**
	 * Get the id of this Test
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of this Test
	 */ 
	public function getId() : ?int
	{
	    return $this->_id;
	}
	
	/**
	 * Get the name of this Test
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The name of this Test
	 */
	public function getName() : ?string
	{
	    return $this->_name;
	}
	
	/**
	 * Get the last modification date timestamp of this Test
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The timestamp of the last modification date of this Test
	 */
	public function getTimestamp() : ?string
	{
	    return $this->_estampille;
	}
	
	/**
	 * Get the contents of the custom .mpr file of this Test
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The contents of the .mpr file of this Test
	 */
	public function getFichierMpr() : ?string
	{
	    return $this->_fichier_mpr;
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
	
	/**
	 * Gets the database connection locking read type applied to this object.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The database connection locking read type applied to this object.
	 */
	public function getDatabaseConnectionLockingReadType() : int
	{
	    return $this->__database_connection_locking_read_type;
	}
	
	/**
	 * Sets the database connection locking read type applied to this object.
	 * @param int $databaseConnectionLockingReadType The new database connection locking read type applied to this object.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \JobType This JobType.
	 */
	private function setDatabaseConnectionLockingReadType(int $databaseConnectionLockingReadType) : \Test
	{
	    $this->__database_connection_locking_read_type = $databaseConnectionLockingReadType;
	    return $this;
	}
}

?>