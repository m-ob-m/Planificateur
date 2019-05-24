<?php
include_once __DIR__ . "/../../varmodtype/model/modelTypeParameter.php";

/**
 * \name		ModelTypeGenericParameter
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2017-03-21
 *
 * \brief 		Modèle de paramètre de modèle-type-générique
 * \details 	Modèle de paramètre de modèle-type-générique
 */
class ModelTypeGenericParameter extends \Parameter implements \JsonSerializable
{   
    private $_fkDoorModel;
    private $_fkDoorType;
    private $_description;
    private $_defaultValue;
    private $_specificValue;
    
    /**
     * ModelTypeGenericParameter constructor
     *
     * @param string $key The name of the test
     * @param string $specific The value of the parameter
     * @param int $fkdoorModel The id of the Model
     * @param int $fkdoorType The import number of the Type
     * @param string $defaultValue The default value of the parameter in the corresponding Generic
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeGenericParameter
     */
    public function __construct(?string $key = null, ?string $specific = null, ?int $modelId = null, ?int $typeNo = null, 
        ?string $description = null, ?string $default = null)
    {
        parent::__construct($key, null);
        $this->setDescription($description);
        $this->setSpecificValue($specific);
        $this->setDefaultValue($default);
        $this->setModelId($modelId);
        $this->setTypeNo($typeNo);
    }
    
    /**
     * Generic constructor using enough information to identify a single existing record
     *
     * @param FabPlanConnection $db The database in which the record exists
     * @param int $modelId The id of the model in the database
     * @param int $typeNo The import number of the type in the database
     * @param string $parameterKey The name of the parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeGenericParameter The ModelTypeGenericParameter object retrieved from the database
     */
    public static function withID(\FabplanConnection $db, int $modelId, int $typeNo, string $key) : ?\ModelTypeGenericParameter
    {
        $stmt = $db->getConnection()->prepare(
            "SELECT `dmd`.`paramValue` AS `specificValue`, `gp`.`parameter_value` AS `genericValue`, 
                `gp`.`description` AS `description`
            FROM `door_types` AS `dt`
            INNER JOIN `generics` AS `g` ON `dt`.`generic_id` = `g`.`id` AND `dt`.`importNo` = :typeNo
            INNER JOIN `generic_parameters` AS `gp` ON `gp`.`generic_id` = `g`.`id` AND `gp`.`parameter_key` = :parameterKey
            INNER JOIN `door_model` AS `dm` ON `dm`.`id_door_model` = :modelId
            LEFT JOIN `door_model_data`AS `dmd` ON `dmd`.`paramKey` = `gp`.`parameter_key` 
            	AND `dmd`.`fkDoorModel` = `dm`.`id_door_model` AND `dmd`.`fkDoorType` = `dt`.`importNo`;"
        );
        $stmt->bindValue(":modelId", $modelId, PDO::PARAM_INT);
        $stmt->bindValue(":typeNo", $typeNo, PDO::PARAM_INT);
        $stmt->bindValue(":parameterKey", $key, PDO::PARAM_STR);
        $stmt->execute();
        
        if($row = $stmt->fetch())
        {
            $instance = new self($key, $row["specificValue"], $modelId, $typeNo, $row["description"], $row["genericValue"]);
        }
        else 
        {
            return null;
        }
        
        return $instance;
    }
    
    /**
     * Set the description of the ModelTypeGenericParameter
     *
     * @param int $description The new description of the ModelTypeGenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeGenericParameter This ModelTypeGenericParameter (for method chaining)
     */
    public function setDescription(?string $description) : \ModelTypeGenericParameter
    {
        $this->_description = $description;
        return $this;
    }
    
    /**
     * Set the default value of this ModelTypeGenericParameter
     *
     * @param string $defaultValue The new default value of the ModelTypeGenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeGenericParameter This ModelTypeGenericParameter (for method chaining)
     */
    public function setDefaultValue(?string $defaultValue) : \ModelTypeGenericParameter
    {
        $this->_defaultValue = $defaultValue;
        $this->setValue($this->_specificValue ?? $this->_defaultValue ?? null);
        return $this;
    }
    
    /**
     * Set the specific value of this ModelTypeGenericParameter
     *
     * @param string $specificValue The new specific value of the ModelTypeGenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeGenericParameter This ModelTypeGenericParameter (for method chaining)
     */
    public function setSpecificValue(?string $specificValue) : \ModelTypeGenericParameter
    {
        $this->_specificValue = $specificValue;
        $this->setValue($this->_specificValue ?? $this->_defaultValue ?? null);
        return $this;
    }
    
    /**
     * Set the value of the ModelTypeGenericParameter
     *
     * @param string $value The new value of the ModelTypeGenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeGenericParameter This ModelTypeGenericParameter (for method chaining)
     */
    private function setValue(?string $value) : \ModelTypeGenericParameter
    {
        $this->_value = $value;
        return $this;
    }
    
    /**
     * Get the id of the Model to which this ModelTypeGenericParameter belongs
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
     * Get the import number of the Type to which this ModelTypeGenericParameter belongs
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The import number of the Type Type to which this ModelTypeGenericParameter belongs in the database
     */
    public function getTypeNo() : int
    {
        return $this->_fkDoorType;
    }
    
    /**
     * Set the id of the Model to which this ModelTypeGenericParameter belongs
     *
     * @param int $modelId The id of the Model to which this ModelTypeGenericParameter belongs
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeGenericParameter This ModelTypeGenericParameter (for method chaining)
     */
    public function setModelId($modelId) : \ModelTypeGenericParameter
    {
        $this->_fkDoorModel = $modelId;
        return $this;
    }
    
    /**
     * Set the import number of the Type to which this ModelTypeGenericParameter belongs
     *
     * @param int $typeNo The import number of the Type to which this ModelTypeGenericParameter belongs
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return ModelTypeGenericParameter This ModelTypeGenericParameter (for method chaining)
     */
    public function setTypeNo($typeNo) : \ModelTypeGenericParameter
    {
        $this->_fkDoorType= $typeNo;
        return $this;
    }
    
    /**
     * Get the description of this ModelTypeGenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The description of this ModelTypeGenericParameter
     */
    public function getDescription() : ?string
    {
        return $this->_description;
    }
    
    /**
     * Get the default value of this ModelTypeGenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The default value of this ModelTypeGenericParameter
     */
    public function getDefaultValue() : ?string
    {
        return $this->_defaultValue;
    }
    
    /**
     * Get the specific value of this ModelTypeGenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The specific value of this ModelTypeGenericParameter
     */
    public function getSpecificValue() : ?string
    {
        return $this->_specificValue;
    }
    
    /**
     * Get the value of the ModelTypeGenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The ModelTypeGenericParameter's value
     */
    public function getValue() : ?string
    {
        return $this->_value;
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