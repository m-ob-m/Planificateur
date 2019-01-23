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

include_once __DIR__ . '/testParameter.php';
include_once __DIR__ . '/../../type/controller/typeController.php';
include_once __DIR__ . '/../../varmodtype/model/modeltype.php';
include_once __DIR__ . '/../../varmodtypegen/model/modelTypeGeneric.php';

class Test extends ModelTypeGeneric implements JsonSerializable
{
	private $_id;
	private $_name;
	private $_fichier_mpr;
	private $_estampille;
	
	
	/**
	 * Test constructor
	 *
	 * @param int $id The id of the Test in the database
	 * @param string $name The name of the Test
	 * @param int $modelId The id of the Model associated with this Test (the one that was modified)
	 * @param int $typeNo The importNumber of the Type associated with this Test (the one that was modified)
	 * @param string $fichierMpr The contents of the .mpr file associated to this Test if not using a generic file
	 * @param int $genericId The id of the Generic associated to this Test
	 * @param string $estampille A timestamp of the last modification applied to this Test (leave null)
	 * @param string $testParameters An array containing the TestTypeParameters objects associated with this Test.
	 *
	 * @throws 
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test
	 */ 
	public function __construct(?int $id = null, ?string $name = null, ?int $modelId = null, ?int $typeNo = null, 
	    ?string $fichierMpr = null, ?int $genericId = null, ?string $timestamp = null, array $testParameters = array())
	{
	    parent::__construct($modelId, $typeNo, $testParameters, $genericId);
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
	static function withID(FabPlanConnection $db, ?int $id) :?Test
	{ 	    
	    // Récupérer le test
	    $stmt = $db->getConnection()->prepare("SELECT `t`.* FROM `fabplan`.`test` AS `t` WHERE `t`.`id` = :id;");
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	// Récupération de l'instance de test
	    {
	        $instance = new self($row["id"], $row["name"], $row["door_model_id"], $row["type_no"], $row["fichier_mpr"], 
	            $row["generic_id"], $row["estampille"]);
	    }
	    else
	    {
	        return NULL;
	    }
	    
	    //Récupérer les paramètres
	    $stmt = $db->getConnection()->prepare("
            SELECT `tp`.* 
            FROM `fabplan`.`test_parameters` AS `tp` 
            INNER JOIN `fabplan`.`test` AS `t` ON `tp`.`test_id` = `t`.`id`
            INNER JOIN `fabplan`.`generics` AS `g` ON `g`.`id` = `t`.`generic_id`
        	INNER JOIN `fabplan`.`generic_parameters` AS `gp` 
                ON `gp`.`generic_id` = `g`.`id` AND `gp`.`parameter_key` = `tp`.`parameter_key`
            WHERE `t`.`id` = :id
            ORDER BY `gp`.`id` ASC;
        ");
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    while($row = $stmt->fetch())	// Récupération de l'instance TestParameter
	    {
	        $instance = $instance->addParameter(
	            new TestParameter($id, $row["parameter_key"], $row["parameter_value"], $row["parameter_description"])
	        );
	    }
	    
	    return $instance;
	}

	static function fromModelTypeGeneric(ModelTypeGeneric $modelTypeGeneric) : Test
	{
	    $modelId = $modelTypeGeneric->getModelId();
	    $typeNo = $modelTypeGeneric->getTypeNo();
	    $instance = (new self(null, null, $modelId, $typeNo))->setParameters(array());
	    
	    foreach($modelTypeGeneric->getParameters() as $parameter)
	    {
	        $instance->addParameter(
	            new TestParameter($instance->getId(), $parameter->getKey(), $parameter->getValue(), $parameter->getDescription())
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
	        $this->update($db);
	    }
	    
	    // Récupération de l'estampille
	    $stmt = $db->getConnection()->prepare("SELECT `t`.`estampille` FROM `test` AS `t` WHERE `t`.`id` = :id;");
	    $stmt->bindValue(":id", $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	
	    {
	        $this->_estampille = $row["estampille"];
	    }
	    else
	    {
	        $this->_estampille = null;
	    }
	    
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
            INSERT INTO `fabplan`.`test` (`id`, `name`, `door_model_id`, `type_no`, `fichier_mpr`, `generic_id`)
            VALUES (:id, :name, :door_model_id, :type_no, :fichier_mpr, :genericId)
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->bindValue(':name', $this->getName(), PDO::PARAM_STR);
	    $stmt->bindValue(':door_model_id', $this->getModelId(), PDO::PARAM_INT);
	    $stmt->bindValue(':type_no', $this->getTypeNo(), PDO::PARAM_INT);
	    $stmt->bindValue(':fichier_mpr', $this->getFichierMpr(), PDO::PARAM_STR);
	    $stmt->bindValue(':genericId', $this->getGenericId(), PDO::PARAM_INT);
	    $stmt->execute();
	    $this->setId($db->getConnection()->lastInsertId());
        
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
            UPDATE `fabplan`.`test` AS `t`
            SET `name` = :name, `door_model_id` = :door_model_id, `type_no` = :type_no, `fichier_mpr` = :fichier_mpr, 
                `generic_id` = :genericId
            WHERE `id` = :id;
        ");
        $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
        $stmt->bindValue(":name", $this->getName(), PDO::PARAM_STR);
        $stmt->bindValue(':door_model_id', $this->getModelId(), PDO::PARAM_INT);
        $stmt->bindValue(':type_no', $this->getTypeNo(), PDO::PARAM_INT);
        $stmt->bindValue(':fichier_mpr', $this->getFichierMpr(), PDO::PARAM_STR);
        $stmt->bindValue(':genericId', $this->getGenericId(), PDO::PARAM_INT);
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
	    $stmt = $db->getConnection()->prepare("DELETE FROM `fabplan`.`test_parameters` WHERE `test_id` = :testId;");
	    $stmt->bindValue(':testId', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $stmt = $db->getConnection()->prepare("DELETE FROM `test` WHERE `test`.`id` = :id;");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    foreach($this->getParameters() as $parameter)
	    {
	        $parameter->delete($db);
	    }
	    
	    return $this;
	}
	
	/**
	 * Load TestParameters from the database for the specified ModelTypeGeneric combination considering generic as independent from 
	 * type (due to its nature, Test is a case of ModelTypeGeneric where the generic's id might be different from the default value 
	 * which is the one specified in the type's properties).
	 *
	 * @param FabPlanConnection $db The database containing parameters to fetch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test This Test (for method chaining)
	 */
	public function loadParameters(FabPlanConnection $db)
	{
	    $stmt = $db->getConnection()->prepare("
        	SELECT `gp`.`parameter_key` AS `key`, `dmd`.`paramValue` AS `specificValue`, `gp`.`parameter_value` AS `genericValue`, 
                `gp`.`description` AS `description`
        	FROM `fabplan`.`door_types` AS `dt`
        	INNER JOIN `fabplan`.`generics` AS `g` ON `dt`.`generic_id` = `g`.`id` AND `dt`.`importNo` = :typeNo
        	INNER JOIN `generic_parameters` AS `gp` ON `gp`.`generic_id` = `g`.`id`
        	INNER JOIN `fabplan`.`door_model` AS `dm` ON `dm`.`id_door_model` = :modelId
        	LEFT JOIN `fabplan`.`door_model_data` AS `dmd` ON `dmd`.`paramKey` = `gp`.`parameter_key`
        		AND `dmd`.`fkDoorModel` = `dm`.`id_door_model` AND `dmd`.`fkDoorType` = `dt`.`importNo`
            ORDER BY `gp`.`id` ASC;
        ");
	    $stmt->bindValue(':modelId', $this->getModelId(), PDO::PARAM_INT);
	    $stmt->bindValue(':typeNo', $this->getTypeNo(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $this->setParameters(array());
	    foreach($stmt->fetchAll() as $row)
	    {
	        $value = ($row['specificvalue'] !== null) ? $row['specificValue'] : $row['genericValue'];
	        $this->addParameter(new TestParameter($this->getId(), $row['key'], $value));
	    }
	    
	    return $this;
	}
	
	/**
	 * Removes all TestParameters associated to this Test without deleting TestParameters in this object.
	 * This allows removal of obsolete variables that are not part of the Test object's parameters anymore, but still 
	 * subsist in the database. 
	 *
	 * @param FabPlanConnection $db The database containing the Test and its parameters.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test This Test (for method chaining)
	 */
	private function deleteParametersFromDatabase(FabPlanConnection $db) : Test
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
	 * @return Test This Test (for method chaining)
	 */
	public function setName(?string $name) : Test
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
	 * @return Test This Test (for method chaining)
	 */
	public function setTimestamp(?string $timestamp) : Test
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
	 * @return int The timestamp of the last modification date of this Test
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
}

?>