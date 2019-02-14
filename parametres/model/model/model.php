<?php
include_once __DIR__ . "/../../varmodtype/model/modelTypeParameter.php";

/**
 * \name		model.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-01-27
*
* \brief 		Modèle de la table door_model
* \details 		Modèle de la table door_model
*/

class Model implements JsonSerializable
{

	private $_id;
	private $_description;
	private $_timestamp;
	private $__database_connection_locking_read_type = \MYSQLDatabaseLockingReadTypes::NONE;

	/**
	 * Model constructor
	 *
	 * @param int $id The id of the Model in the database
	 * @param string $description The description of the Model
	 * @param string $timestamp The timestamp of the last modification of this object
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test
	 */
	function __construct(?int $id = null, ?string $description = null, ?string $timestamp)
	{
	    $this->setId($id);
	    $this->setDescription($description);
	    $this->setTimestamp($timestamp);
	}
	
	/**
	 * Model constructor using ID of existing record
	 *
	 * @param FabPlanConnection $db The database in which the record exists
	 * @param int $id The id of the record in the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Model The Model associated to the specified ID in the specified database
	 */
	public static function withID($db, $id, int $databaseConnectionLockingReadType = 0) : ?\Model
	{
	    // Récupérer le Model
	    $stmt = $db->getConnection()->prepare(
            "SELECT `dm`.* FROM `fabplan`.`door_model` AS `dm` 
            WHERE `dm`.`id_door_model` = :id " . 
	        (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	// Récupération de l'instance de Model
	    {
	        $instance = new self($row["id_door_model"], $row["description_model"], $row["timestamp"]);
	    }
	    else
	    {
	        return null;
	    }
	    
	    $this->setDatabaseConnectionLockingReadType($databaseConnectionLockingReadType);
	    return $instance;
	}
	
	/**
	 * Save the Model object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be saved
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Model This Model (for method chaining)
	 */
	public function save(FabPlanConnection $db) : Model
	{   
	    if($this->getId() === null)
	    {
	        $this->insert($db);
	    }
	    else
	    {
	        $dbTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $this->getTimestampFromDatabase($db), "America/Montreal");
	        $localTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $this->getTimestamp(), "America/Montreal");
	        if($this->getDatabaseConnectionReadingLockType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
	        {
	            throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
	        }
	        elseif($databaseTimestamp > $localTimestamp)
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
	 * Insert the Model object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be inserted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Model This Model (for method chaining)
	 */
	private function insert(FabPlanConnection $db) : Model
	{
	    // Création d'un Model
	    $stmt = $db->getConnection()->prepare("
            INSERT INTO `fabplan`.`door_model`(`id_door_model`, `description_model`)
            VALUES (:id, :description);
        ");
	    $stmt->bindValue(":id", $this->getId(), PDO::PARAM_INT);
	    $stmt->bindValue(":description", $this->getDescription(), PDO::PARAM_STR);
	    $stmt->execute();
	    
	    $this->setId($db->getConnection()->lastInsertId());
	    return $this;
	}
	
	/**
	 * Update the Model object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be updated
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Model This Model (for method chaining)
	 */
	private function update(FabPlanConnection $db) : Model
	{
	    // Mise à jour d'un Model
	    $stmt = $db->getConnection()->prepare("
            UPDATE `fabplan`.`door_model` SET `description_model` = :description WHERE `id_door_model` = :id;
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->bindValue(":description", $this->getDescription(), PDO::PARAM_STR);
	    $stmt->execute();
	    return $this;
	}
	
	/**
	 * Delete the Model object from the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Model This Model (for method chaining)
	 */
	public function delete(FabPlanConnection $db) : Model
	{
	    $stmt = $db->getConnection()->prepare("DELETE FROM `fabplan`.`door_model_data` WHERE `fkDoorModel` = :modelId;");
	    $stmt->bindValue(':modelId', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $stmt = $db->getConnection()->prepare("DELETE FROM `fabplan`.`door_model` WHERE `id_door_model` = :id;");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
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
            SELECT `dm`.`timestamp` FROM `fabplan`.`door_model` AS `dm` WHERE `dm`.`id_door_model` = :id;
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if($row = $stmt->fetch())
	    {
	        return $row["timestamp"];
	    }
	    else
	    {
	        return null;
	    }
	}
	
	/**
	 * Get the id of this Model
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of this Model
	 */
	public function getId() :?int
	{
	    return $this->_id;
	}
	
	/**
	 * Get the description of this Model
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The description of this Model
	 */
	public function getDescription() :?string
	{
	    return $this->_description;
	}
	
	/**
	 * Gets the last modification timestamp of this Model
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The last modification timestamp of this Model
	 */
	public function getTimestamp() :?string
	{
	    return $this->_timestamp;
	}
	
	/**
	 * Get all ModelTypeParameters for this Model
	 * 
	 * @param FabplanConnection $db The database from which data must be retrieved
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array[ModelTypeParameter] The array of ModelTypeParameter objects for this Model
	 */
	public function getModelTypeParametersForAllTypes(FabplanConnection $db) : ?array
	{
	    $stmt = $db->getConnection()->prepare(
            "SELECT `dmd`.`fkDoorType` AS `typeNo`, `dmd`.`paramKey` AS `parameterKey`, `dmd`.`paramValue` AS `parameterValue`
            FROM `fabplan`.`door_model_data` AS `dmd`
            WHERE `dmd`.`fkDoorModel` = :modelId " . 
	        (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(":modelId", $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $modelTypeParameters = array();
	    while($row = $stmt->fetch())
	    {
	        array_push(
	           $modelTypeParameters, 
	            new ModelTypeParameter($row["parameterKey"], $row["parameterValue"], $this->getId(), $row["typeNo"])
	        );
	    }
	    
	    return $modelTypeParameters;
	}
	
	/**
	 * Set the id of this Model
	 *
	 * @param int $id The new id for this Model
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Model This Model
	 */
	public function setId(?int $id) :\Model
	{
	    $this->_id = $id;
	    return $this;
	}
	
	/**
	 * Set the description of this Model
	 *
	 * @param string $description The new description for this Model
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Model This Model
	 */
	public function setDescription(?string $description) :\Model
	{
	    $this->_description = $description;
	    return $this;
	}
	
	/**
	 * Sets the last modification timestamp of this Model
	 *
	 * @param string $timestamp The last modification timestamp of this Model
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Model This Model
	 */
	public function setTimestamp(?string $timestamp) :\Model
	{
	    $this->_timestamp = $timestamp;
	    return $this;
	}
	
	/**
	 * Get a JSON compatible representation of this object.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array This object in a JSON compatible format
	 */
	public function jsonSerialize()
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
	private function getDatabaseConnectionLockingReadType() : int
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
	private function setDatabaseConnectionLockingReadType(int $databaseConnectionLockingReadType) : \JobType
	{
	    $this->__database_connection_locking_read_type = $databaseConnectionLockingReadType;
	    return $this;
	}
}