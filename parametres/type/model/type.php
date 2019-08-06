<?php
require_once __DIR__ . "/../../varmodtype/model/modelTypeParameter.php";
require_once __DIR__ . "/../../generic/model/generic.php";

/**
 * \name		type.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-01-27
*
* \brief 		Modele de la table door_types
* \details 		Modele de la table door_types
*/

class Type implements \JsonSerializable
{

	private $_id;
	private $_importNo;
	private $_description;
	private $_generic;
	private $_timestamp;
	private $__database_connection_locking_read_type = \MYSQLDatabaseLockingReadTypes::NONE;

	/**
	 * Type constructor
	 *
	 * @param int $id The id of the Type in the database
	 * @param int $importNo The import number of this Type
	 * @param string $description The description of this generic
	 * @param \Generic $generic The Generic associated to this Type
	 * @param string $timestamp The timestamp of the last modification date of this object in the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type
	 */
	function __construct(?int $id = null, ?int $importNo = null, ?string $description = null, ?\Generic $generic = null, 
	    ?string $timestamp = null)
	{
	    $this->setId($id);
	    $this->setImportNo($importNo);
	    $this->setDescription($description);
	    $this->setGeneric($generic);
	    $this->setTimestamp($timestamp);
	}
	
	/**
	 * Type constructor that accepts an id as an input
	 *
	 * @param FabPlanConnection $db The database in which the record exists
	 * @param int $id The id of the record in the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type The Type associated to the specified ID in the specified database
	 */
	public static function withId(\FabplanConnection $db, int $id, int $databaseConnectionLockingReadType = 0) : ?\Type
	{
	    // Récupérer le test
	    $stmt = $db->getConnection()->prepare(
	        "SELECT `dt`.* FROM `door_types` AS `dt` WHERE `dt`.`id` = :id " . 
	        (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	// Récupération de l'instance de matériel
	    {
	        $generic = \Generic::withID($db, $row["generic_id"]);
	        $instance = new self($row["id"], $row["importNo"], $row["description"], $generic, $row["timestamp"]);
	    }
	    else
	    {
	        return null;
	    }
	    
	    $instance->setDatabaseConnectionLockingReadType($databaseConnectionLockingReadType);
	    return $instance;
	}
	
	/**
	 * Type constructor that accepts an import number as an input
	 *
	 * @param \FabPlanConnection $db The database in which the record exists
	 * @param int $importNo The import number of the type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Type The Type associated to the specified import number in the specified database
	 */
	public static function withImportNo(\FabplanConnection $db, int $importNo, int $dbConnectionLockingReadType = 0) : ?\Type
	{
	    // Récupérer le test
	    $stmt = $db->getConnection()->prepare(
	        "SELECT `dt`.* FROM `door_types` AS `dt` 
            WHERE `dt`.`importNo` = :importNo " .
	        (new \MYSQLDatabaseLockingReadTypes($dbConnectionLockingReadType))->toLockingReadString() . ";"
	    );
	    $stmt->bindValue(':importNo', $importNo, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	// Récupération de l'instance de matériel
	    {
	        $generic = \Generic::withID($db, $row["generic_id"]);
	        $instance = new self($row["id"], $row["importNo"], $row["description"], $generic, $row["timestamp"]);
	    }
	    else
	    {
	        return null;
	    }
	    
	    $instance->setDatabaseConnectionLockingReadType($dbConnectionLockingReadType);
	    return $instance;
	}
	
	/**
	 * Save the Type object in the database
	 *
	 * @param \FabPlanConnection $db The database in which the record must be saved
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Type This Type (for method chaining)
	 */
	function save(\FabPlanConnection $db) : \Type
	{
	    if($this->_id === null)
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
	                "The provided " . get_class($this) . " is outdated. The last modification date of the database entry
                    is \"{$dbTimestamp->format("Y-m-d H:i:s")}\" whereas the last modification date of the local copy is
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
	 * Insert the Type object in the database
	 *
	 * @param \FabPlanConnection $db The database in which the record must be inserted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Type This Type (for method chaining)
	 */
	private function insert(\FabPlanConnection $db) : \Type
	{
        // Création d'un test
        $stmt = $db->getConnection()->prepare("
            INSERT INTO `door_types` (`importNo`, `description`, `generic_id`)
            VALUES (:importNo, :description, :generic_id);
        ");
        $stmt->bindValue(':importNo', $this->getImportNo(), PDO::PARAM_INT);
        $stmt->bindValue(':description', $this->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(":generic_id", $this->getGeneric()->getId(), PDO::PARAM_INT);
        $stmt->execute();
        $this->_id = intval($db->getConnection()->lastInsertId());
        
        return $this;
	}
	
	/**
	 * Update the Type object in the database
	 *
	 * @param \FabPlanConnection $db The database in which the record must be updated
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Type This Type (for method chaining)
	 */
	private function update(\FabPlanConnection $db) :Type
	{ 
        // Mise à jour d'un test
        $stmt = $db->getConnection()->prepare("
            UPDATE `door_types`
            SET `importNo` = :importNo, `description` = :description, `generic_id` = :genericId
            WHERE `id` = :id;
        ");
        $stmt->bindValue(':importNo', $this->getImportNo(), PDO::PARAM_INT);
        $stmt->bindValue(':description', $this->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(":genericId", $this->getGeneric()->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
        $stmt->execute();
        
        return $this;
	}
	
	/**
	 * Delete the Type object from the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type This Type (for method chaining)
	 */
	public function delete(\FabPlanConnection $db) : \Type
	{
	    if($this->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
	    {
	        throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
	    }
	    else
	    {
    	    $stmt = $db->getConnection()->prepare("
                DELETE FROM `door_model_data` WHERE `fkDoorType` = :importNo;
            ");
    	    $stmt->bindValue(':importNo', $this->getImportNo(), PDO::PARAM_INT);
    	    $stmt->execute();
    	    
            $stmt = $db->getConnection()->prepare("
                DELETE FROM `door_types` WHERE `id` = :id;
            ");
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
            SELECT `dt`.`timestamp` FROM `door_types` AS `dt` WHERE `dt`.`id` = :id;
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
	 * Get the id of this Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of this Type in the database
	 */
	public function getId() : ?int
	{
	    return $this->_id;
	}
	
	/**
	 * Get the SIA import number associated to this type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The import number of this Type.
	 */
	public function getImportNo() : ?int
	{
	    return $this->_importNo;
	}
	
	/**
	 * Get the description of this Generic
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The description of this Type
	 */
	public function getDescription() : ?string
	{
	    return $this->_description;
	}
	
	/**
	 * Get the Generic associated to this Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Generic The id of the Generic associated to this Type
	 */
	public function getGeneric() : ?\Generic
	{
	    return $this->_generic;
	}
	
	/**
	 * Get the last modification date timestamp of this Type in the database.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The timestamp of the last modification date of this Type
	 */
	public function getTimestamp() : ?string
	{
	    return $this->_timestamp;
	}
	
	/**
	 * Get all ModelTypeParameters for this Type
	 *
	 * @param FabplanConnection $db The database from which data must be retrieved
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array[ModelTypeParameter] The array of ModelTypeParameter objects for this Type
	 */
	public function getModelTypeParametersForAllModels(\FabplanConnection $db) : ?array
	{
	    $stmt = $db->getConnection()->prepare("
            SELECT `dmd`.`fkDoorModel` AS `modelId`, `dmd`.`paramKey` AS `parameterKey`, 
                `dmd`.`paramValue` AS `parameterValue`
            FROM `door_model_data` AS `dmd`
            WHERE `dmd`.`fkDoorType` = :typeNo;
        ");
	    $stmt->bindValue(":typeNo", $this->getImportNo(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $modelTypeParameters = array();
	    while($row = $stmt->fetch())
	    {
	        array_push(
	            $modelTypeParameters, 
	            new \ModelTypeParameter($row["parameterKey"], $row["parameterValue"], $row["modelId"], $this->getImportNo())
	        );
	    }
	    
	    return $modelTypeParameters;
	}
	
	/**
	 * Set the id of this Type
	 *
	 * @param string $id The id of this Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type This Type (for method chaining)
	 */
	public function setId(?int $id) : Type
	{
	    $this->_id = $id;
	    return $this;
	}
	
	/**
	 * Set the import number of this Type
	 *
	 * @param string $importNo The import number of this Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type This Type (for method chaining)
	 */
	public function setImportNo(?int $importNo) : Type
	{
	    $this->_importNo = $importNo;
	    
	    return $this;
	}
	
	/**
	 * Set the SIA import number of this Type
	 *
	 * @param string $importNo The import number of this Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type This Type (for method chaining)
	 */
	public function setDescription(?string $description) : Type
	{
	    $this->_description = $description;
	    return $this;
	}
	
	/**
	 * Set the Generic associated to this Type
	 *
	 * @param \Generic $generic The Generic associated to this Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Type This Type (for method chaining)
	 */
	public function setGeneric(?\Generic $generic) : Type
	{
	    $this->_generic = $generic;
	    return $this;
	}
	
	/**
	 * Set the timestamp of this Type
	 *
	 * @param string $timestamp The new timestamp
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Type This \Type (for method chaining)
	 */
	public function setTimestamp(?string $timestamp) : \Type
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
	private function setDatabaseConnectionLockingReadType(int $databaseConnectionLockingReadType) : \Type
	{
	    $this->__database_connection_locking_read_type = $databaseConnectionLockingReadType;
	    return $this;
	}
}