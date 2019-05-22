<?php
include_once __DIR__ . "/../../parameter/parameter.php";

/**
 * \name		ModelTypeParameter
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2017-03-21
 *
 * \brief 		Modèle de paramètre de modèle-type
 * \details 	Modèle de paramètre de modèle-type
 */

class ModelTypeParameter extends \Parameter
{   
    private $_fkDoorModel;
    private $_fkDoorType;
    
    /**
     * GenericParameter constructor
     *
     * @param string $key The key of the Parameter
     * @param string $value The value of the Parameter
     * @param int $modelId The id of the Model
     * @param int $typeNo The import number of the Type
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeParameter
     */
    function __construct(?string $key = null, ?string $value = null, ?int $modelId =null, ?int $typeNo = null)
    {
        parent::__construct($key);
        $this->setValue($value);
        $this->setModelId($modelId);
        $this->setTypeNo($typeNo);
    }
    
    /**
     * Generic constructor using enough information to identify a single existing record
     *
     * @param FabPlanConnection $db The database in which the record exists
     * @param int $modelId The id of the model in the database
     * @param int $typeNo The import number of the type in the database
     * @param string $key The key of the parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeParameter The ModelTypeParameter object retrieved from the database
     */
    static function withID(\FabplanConnection $db, int $modelId, int $typeNo, string $key) : ?\ModelTypeParameter
    {
        // Récupérer le générique
        $stmt = $db->getConnection()->prepare(
            "SELECT `dmd`.* FROM `fabplan`.`door_model_data` AS `dmd`
            WHERE `dmd`.`fkDoorModel` = :modelId AND `dmd`.`fkDoorType` = :typeNo AND `dmd`.`paramKey` = :key;"
        );
        $stmt->bindValue(":modelId", $modelId, PDO::PARAM_INT);
        $stmt->bindValue(":typeNo", $typeNo, PDO::PARAM_INT);
        $stmt->bindValue(":key", $key, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($row = $stmt->fetch())	// Récupération de l'instance de test
        {
            $instance = new self($row["paramKey"], $row["paramValue"], $row["fkDoorModel"], $row["fkDoorType"]);
        }
        else
        {
            return null;
        }
        
        return $instance;
    }
    
    /**
     * Save the ModelTypeParameter object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be saved
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeParameter This ModelTypeParameter (for method chaining)
     */
    function save(\FabPlanConnection $db) : \ModelTypeParameter
    {
        if(self::withID($db, $this->getModelId(), $this->getTypeNo(), $this->getKey()) === null)
        {
            $this->insert($db);
        }
        else
        {
            $this->update($db);
        }
        
        return $this;
    }
    
    /**
     * Insert the ModelTypeParameter object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be inserted
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeParameter This ModelTypeParameter (for method chaining)
     */
    private function insert(\FabPlanConnection $db) : \ModelTypeParameter
    {
        try
        {
            $stmt = $db->getConnection()->prepare("
                INSERT INTO `fabplan`.`door_model_data` (`paramKey`, `paramValue`, `fkDoorModel`, `fkDoorType`)
                VALUES (:key, :value, :modelId, :typeNo);"
            );
            $stmt->bindValue(':key', $this->getKey(), PDO::PARAM_STR);
            $stmt->bindValue(':value', $this->getValue(), PDO::PARAM_STR);
            $stmt->bindValue(':modelId', $this->getModelId(), PDO::PARAM_INT);
            $stmt->bindValue(':typeNo', $this->getTypeNo(), PDO::PARAM_INT);
            $stmt->execute();
            
            return $this;
        }
        catch (Exception $e)
        {
            echo json_encode($this);
            throw $e;
        }
    }
    
    /**
     * Update the ModelTypeParameter object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be updated
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeParameter This ModelTypeParameter (for method chaining)
     */
    private function update(\FabPlanConnection $db) : \ModelTypeParameter
    {
        try
        {
            $stmt = $db->getConnection()->prepare("
                UPDATE `fabplan`.`door_model_data` AS `dmd`
                SET `paramValue` = :value
                WHERE `dmd`.`fkDoorModel` = :modelId AND `dmd`.`fkDoorType` = :typeNo AND `dmd`.`paramKey` = :key;
            ");
            $stmt->bindValue(':key', $this->getKey(), PDO::PARAM_STR);
            $stmt->bindValue(':value', $this->getValue(), PDO::PARAM_STR);
            $stmt->bindValue(':modelId', $this->getModelId(), PDO::PARAM_INT);
            $stmt->bindValue(':typeNo', $this->getTypeNo(), PDO::PARAM_INT);
            $stmt->execute();
            
            return $this;
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }
    
    /**
     * Delete the ModelTypeParameter object from the database
     *
     * @param FabPlanConnection $db The database from which the record must be deleted
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeParameter This ModelTypeParameter (for method chaining)
     */
    public function delete(\FabPlanConnection $db) : \ModelTypeParameter
    {
        $stmt = $db->getConnection()->prepare("
            DELETE FROM `fabplan`.`door_model_data`
            WHERE `door_model_data`.`fkDoorModel` = :modelId AND `door_model_data`.`fkDoorType` = :typeNo 
                AND `door_model_data`.`paramKey` = :key;
        ");
        $stmt->bindValue(':key', $this->getKey(), PDO::PARAM_STR);
        $stmt->bindValue(':modelId', $this->getModelId(), PDO::PARAM_INT);
        $stmt->bindValue(':typeNo', $this->getTypeNo(), PDO::PARAM_INT);
        $stmt->execute();
        
        return $this;
    }
    
    /**
     * Get the value of the ModelTypeParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The ModelTypeParameter's value
     */
    public function getValue() : ?string
    {
        return $this->_value;
    }
    
    /**
     * Get the id of the Model to which this ModelTypeParameter belongs
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The id of the Model in the database
     */
    public function getModelId() : int
    {
        return $this->_fkDoorModel;
    }
    
     /**
     * Get the import number of the Type to which this ModelTypeParameter belongs
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The import number of the Type Type to which this ModelTypeParameter belongs in the database
     */
    public function getTypeNo() : int
    {
        return $this->_fkDoorType;
    }
    
    /**
     * Set the id of the Model to which this ModelTypeParameter belongs
     * 
     * @param int $modelId The id of the Model to which this ModelTypeParameter belongs
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeParameter This ModelTypeParameter (for method chaining)
     */
    public function setModelId($modelId) : \ModelTypeParameter
    {
        $this->_fkDoorModel = $modelId;
        return $this;
    }
    
    /**
     * Set the import number of the Type to which this ModelTypeParameter belongs
     * 
     * @param int $typeNo The import number of the Type to which this ModelTypeParameter belongs
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeParameter This ModelTypeParameter (for method chaining)
     */
    public function setTypeNo($typeNo) : \ModelTypeParameter
    {
        $this->_fkDoorType= $typeNo;
        return $this;
    }
    
    /**
     * Set the value of the ModelTypeParameter
     *
     * @param string $value The new value of the ModelTypeParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeParameter This ModelTypeParameter (for method chaining)
     */
    public function setValue(?string $value) : \ModelTypeParameter
    {
        $this->_value = $value;
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
}
?>