<?php
include_once __DIR__ . "/../../parameter/parameter.php";

/**
 * \name		GenericParameter
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2017-03-21
 *
 * \brief 		Modele de paramètre de programme générique
 * \details 	Modele de paramètre de programme générique
 */

class GenericParameter extends Parameter implements JsonSerializable
{
    private $_id;
    private $_description;
    private $_generic_id;
    private $_quick_edit;
    private $__database_connection_locking_read_type = \MYSQLDatabaseLockingReadTypes::NONE;
    
    /**
     * GenericParameter constructor
     *
     * @param int $id The id of the Generic Parameter (used to sort parameters in order to respect inter-parameter dependency)
     * @param int $genericId The id of the Generic to which this parameter belongs
     * @param string $parameterKey The name of the test
     * @param mixed $parameterValue The value of the parameter
     * @param string $description The description of the parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter
     */
    function __construct(?int $id, ?int $genericId = null, ?string $parameterKey = null, $parameterValue = null, 
        ?string $description = null, int $quickEdit = 0)
    {
        parent::__construct($parameterKey);
        $this->setId($id);
        $this->setValue($parameterValue);
        $this->setDescription($description);
        $this->setGenericId($genericId);
        $this->setQuickEdit($quickEdit);
    }
    
    /**
     * Generic constructor using enough information to identify a single existing record
     *
     * @param FabPlanConnection $db The database in which the record exists
     * @param int $id The id of the record in the database
     * @param string $parameterKey The name of the parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter The GenericParameter object retrieved from the database
     */
    static function withGenericID(FabplanConnection $db, int $genericId, string $parameterKey, int $dbCLRT = 0) : GenericParameter
    {
        // Récupérer le générique
        $stmt = $db->getConnection()->prepare(
            "SELECT `gp`.* FROM `fabplan`.`generic_parameters` AS `gp` 
            WHERE `gp`.`generic_id` = :generic_id AND `gp`.`parameter_key` = :parameter_key " . 
            (new \MYSQLDatabaseLockingReadTypes($dbCLRT))->toLockingReadString() . ";"
        );
        $stmt->bindValue(":generic_id", $genericId, PDO::PARAM_INT);
        $stmt->bindValue(":parameter_key", $parameterKey, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($row = $stmt->fetch())	// Récupération de l'instance de paramètre de générique
        {
            $instance = new self($row["id"], $row["generic_id"], $row["parameter_key"], $row["parameter_value"], 
                $row["description"], $row["quick_edit"]);
        }
        else
        {
            return null;
        }
        
        $this->setDatabaseConnectionLockingReadType($dbCLRT);
        return $instance;
    }
    
    /**
     * Save the GenericParameter object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be saved
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter This GenericParameter (for method chaining)
     */
    public function save(\FabPlanConnection $db) : \GenericParameter
    {
        $stmt = $db->getConnection()->prepare("
            SELECT `gp`.* FROM `fabplan`.`generic_parameters` AS `gp` 
            WHERE `gp`.`generic_id` = :genericId AND `gp`.`parameter_key` = :parameterKey;
        ");
        $stmt->bindValue(":genericId", $this->getGenericId(), PDO::PARAM_INT);
        $stmt->bindValue(":parameterKey", $this->getKey(), PDO::PARAM_STR);
        $stmt->execute();
        
        if(!$stmt->fetch())
        {
            $this->insert($db);
        }
        else
        {
            if($this->getDatabaseConnectionReadingLockType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
            {
                throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
            }
            else
            {
                $this->update($db);
            }
        }
        
        return $this;
    }
    
    /**
     * Insert the GenericParameter object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be inserted
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter This GenericParameter (for method chaining)
     */
    private function insert(\FabPlanConnection $db) : \GenericParameter
    {     
        $stmt = $db->getConnection()->prepare("
            INSERT INTO `generic_parameters` (`generic_id`, `parameter_key`, `parameter_value`, `description`, `quick_edit`) 
            VALUES (:genericId, :parameterKey, :parameterValue, :description, :quickEdit);");
        $stmt->bindValue(':genericId', $this->getGenericId(), PDO::PARAM_INT);
        $stmt->bindValue(':parameterKey', $this->getKey(), PDO::PARAM_STR);
        $stmt->bindValue(':parameterValue', $this->getValue(), PDO::PARAM_STR);
        $stmt->bindValue(':description', $this->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(':quickEdit', $this->getQuickEdit(), PDO::PARAM_INT);
        $stmt->execute();
        
        $this->setId($db->getConnection()->lastInsertId());
        
        return $this;
    }
    
    /**
     * Update the GenericParameter object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be updated
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter This GenericParameter (for method chaining)
     */
    private function update(FabPlanConnection $db) : GenericParameter
    {
        $stmt = $db->getConnection()->prepare("
            UPDATE `generic_parameters` 
            SET `parameter_value` = :parameterValue, `description` = :description, `quick_edit` = :quickEdit
            WHERE `generic_id` = :genericId AND `parameter_key` = :parameterKey;
        ");
        $stmt->bindValue(':genericId', $this->getGenericId(), PDO::PARAM_INT);
        $stmt->bindValue(':parameterKey', $this->getKey(), PDO::PARAM_STR);
        $stmt->bindValue(':parameterValue', $this->getValue(), PDO::PARAM_STR);
        $stmt->bindValue(':description', $this->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(':quickEdit', $this->getQuickEdit(), PDO::PARAM_INT);
        $stmt->execute();
        
        return $this;
    }
    
    /**
     * Delete the GenericParameter object from the database
     *
     * @param FabPlanConnection $db The database from which the record must be deleted
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter This GenericParameter (for method chaining)
     */
    public function delete(FabPlanConnection $db) : GenericParameter
    {
        $stmt = $db->getConnection()->prepare("
            DELETE FROM `generic_parameters` 
            WHERE `generic_parameters`.`generic_id` = :genericId AND `generic_parameters`.`parameter_key` = :parameterKey;
        ");
        $stmt->bindValue(':genericId', $this->getGenericId(), PDO::PARAM_INT);
        $stmt->bindValue(':parameterKey', $this->getKey(), PDO::PARAM_STR);
        $stmt->execute();
        
        return $this;
    }
    
    /**
     * Get the id of the GenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The id of the GenericParameter in the database
     */
    public function getId() : ?int
    {
        return $this->_id;
    }
    
    /**
     * Get the id of the Generic to which this GenericParameter belongs
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The id of the Generic in the database
     */
    public function getGenericId() : ?int
    {
        return $this->_generic_id;
    }
    
    /**
     * Get the description of the GenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The description of the GenericParameter.
     */
    public function getDescription() : string
    {
        return $this->_description;
    }
    
    /**
     * Get the value of the GenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The GenericParameter's value
     */
    public function getValue() : ?string
    {
        return $this->_value;
    }
    
    /**
     * Get the value of the quick edition parameter of the GenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The value of the quick edition parameter of the GenericParameter
     */
    public function getQuickEdit() : ?int
    {
        return $this->_quick_edit;
    }
    
    /**
     * Set the id of the GenericParameter
     *
     * @param int $id The id of the GenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter This GenericParameter (for method chaining)
     */
    public function setId(?int $id) : GenericParameter
    {
        $this->_id = $id;
        return $this;
    }
    
    /**
     * Set the generic's id of the GenericParameter
     *
     * @param int $genericId The id of the Generic
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter This GenericParameter (for method chaining)
     */
    public function setGenericId(?int $genericId) : GenericParameter
    {
        $this->_generic_id = $genericId;
        return $this;
    }
    
    /**
     * Set the description of the GenericParameter
     *
     * @param string $description The description of the parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter This GenericParameter (for method chaining)
     */
    public function setDescription(?string $description) : GenericParameter
    {
        $this->_description = $description;
        return $this;
    }
    
    /**
     * Set the value of the GenericParameter
     *
     * @param string $value The new value of the GenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter This GenericParameter (for method chaining)
     */
    public function setValue(?string $value) : GenericParameter
    {
        $this->_value = $value;
        return $this;
    }
    
    /**
     * Set the value of the quick edition parameter of the GenericParameter
     *
     * @param int $quickEdit The new value of the quick edition parameter of the GenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter This GenericParameter (for method chaining)
     */
    public function setQuickEdit(?int $quickEdit) : GenericParameter
    {
        $this->_quick_edit = $quickEdit;
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


?>