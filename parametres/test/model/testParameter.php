<?php
/**
 * \name		TestTypeParameter
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2017-10-31
 *
 * \brief 		Modele de testTypeParameter
 * \details 	Modele de testTypeParameter
 */

include_once __DIR__ . "/../../varmodtypegen/model/modelTypeGenericParameter.php";

class TestParameter  extends Parameter implements JsonSerializable
{
	private $_test_id;
	private $_description;
	
	/**
	 * Main constructor
	 *
	 * @param int $testId The id of the Test object to which this TestParameter belongs
	 * @param string $key The key of the TestParameter
	 * @param string $value The value of the TestParameter
	 * @param string $description The description of this TestParameter
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return TestParameter This TestParameter
	 */
	function __construct(?int $testId = null, ?string $key = null, ?string $value = null, ?string $description = null)
	{
	    parent::__construct($key);
	    $this->setTestId($testId);
	    $this->setValue($value);
	    $this->setDescription($description);
	}
	
	/**
	 * Constructor that retrieves an instance from the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be retrieved
	 * @param int $testId The id of the Test to which  this TestParameter belongs
	 * @param string $key The key of the current TestParameter
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return TestParameter This TestParameter
	 */
	static function withID(FabplanConnection $db, int $testId, string $key) :TestParameter
	{  
	    $stmt = $db->getConnection()->prepare("
            SELECT `tp`.* FROM `test_parameters` AS `tp`
            WHERE `tp`.`test_id` = :test_id AND `tp`.`parameter_key` = :key;
        ");
	    $stmt->bindValue(':test_id', $testId, PDO::PARAM_INT);
	    $stmt->bindValue(':key', $key, PDO::PARAM_STR);
	    $stmt->execute();
	    
	    $instance = null;
	    if($row = $stmt->fetch())	// Récupération de l'instance TestParameter
	    {
	        $instance = new self($testId, $key, $row["parameter_value"], $row["parameter_description"]);
	    }
	    
	    return $instance;
	}
	
	/**
	 * Save the TestParameter object in the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be saved
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return TestParameter This TestParameter (for method chaining)
	 */
	public function save(FabPlanConnection $db) : TestParameter
	{ 
	    if($this->withID($db, $this->getId(), $this->getKey()) == null)
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
	 * Insert the TestParameter object in the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be inserted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return TestParameter This TestParameter (for method chaining)
	 */
	private function insert(FabPlanConnection $db) : TestParameter
	{
	    // Création d'un type de test
	    $stmt = $db->getConnection()->prepare("
            INSERT INTO `test_parameters` (`test_id`, `parameter_key`, `parameter_value`, `parameter_description`)
            VALUES (:test_id, :key, :value, :description);
        ");
	    $stmt->bindValue(':test_id', $this->getTestId(), PDO::PARAM_INT);
	    $stmt->bindValue(':key', $this->getKey(), PDO::PARAM_STR);
	    $stmt->bindValue(':value', $this->getValue(), PDO::PARAM_STR);
	    $stmt->bindValue(':description', $this->getDescription(), PDO::PARAM_STR);
	    $success = $stmt->execute();
        
        return $this;
	}
	
	/**
	 * Update the TestParameter object in the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be updated
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return TestParameter This TestParameter (for method chaining)
	 */
	private function update(FabPlanConnection $db) : TestParameter
	{
	    // Mise à jour d'un test
	    $stmt = $db->getConnection()->prepare("
            UPDATE `test_parameters`
            SET `parameter_value` = :value, `parameter_description` = :description
            WHERE `test_id` = :test_id AND `parameter_key` = :key;
        ");
	    $stmt->bindValue(':test_id', $this->getTestId(), PDO::PARAM_INT);
	    $stmt->bindValue(':key', $this->getKey(), PDO::PARAM_STR);
	    $stmt->bindValue(':value', $this->getValue(), PDO::PARAM_STR);
	    $stmt->bindValue(':description', $this->getDescription(), PDO::PARAM_STR);
	    $success = $stmt->execute();
	    
	    return $this;
	}
	
	/**
	 * Delete the TestParameter object from the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return TestParameter This TestParameter (for method chaining)
	 */
	public function delete(FabPlanConnection $db) : TestParameter
	{
	    $stmt = $db->getConnection()->prepare("
            DELETE FROM `test_parameters` 
            WHERE `test_id` = :test_id AND `parameter_key` = :key;
        ");
	    $stmt->bindValue(':test_id', $this->getTestId(), PDO::PARAM_INT);
	    $stmt->bindValue(':key', $this->getKey(), PDO::PARAM_STR);
	    $stmt->execute();
	    
	    return $this;
	}
	
	/**
	 * Set the test id of this TestParameter.
	 *
	 * @param int $testId The id of the Test to which this TestParameter is related.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return TestParameter This TestParameter (for method chaining)
	 */ 
	public function setTestId(?int $testId) : TestParameter
	{
	    $this->_test_id = $testId;
	    return $this;
	}
    
	/**
	 * Set the value of the TestParameter
	 *
	 * @param string $value The new value of the TestParameter
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return TestParameter This TestParameter (for method chaining)
	 */
	public function setValue(?string $value) : TestParameter
	{
	    $this->_value = $value;
	    return $this;
	}
	
	/**
	 * Set the description of the TestParameter
	 *
	 * @param string $description The description of the TestParameter
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return TestParameter This TestParameter (for method chaining)
	 */
	public function setDescription(?string $description) : TestParameter
	{
	    $this->_description = $description;
	    return $this;
	}
	
	/**
	 * Get the test id of this TestParameter.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The test id of this TestParameter
	 */ 
	public function getTestId() : int
	{
	    return $this->_test_id;
	}
	
	/**
	 * Get the value of the TestParameter
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The TestParameter's value
	 */
	public function getValue() : ?string
	{
	    return $this->_value;
	}
	
	/**
	 * Get the description of the TestParameter
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The TestParameter's description
	 */
	public function getDescription() : ?string
	{
	    return $this->_description;
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