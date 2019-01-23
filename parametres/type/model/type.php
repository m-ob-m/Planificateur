<?php
include_once __DIR__ . "/../../varmodtype/model/modelTypeParameter.php";

/**
 * \name		type.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-01-27
*
* \brief 		Modele de la table door_types
* \details 		Modele de la table door_types
*/

class Type implements JsonSerializable
{

	private $_id;
	private $_importNo;
	private $_description;
	private $_genericId;

	/**
	 * Type constructor
	 *
	 * @param int $id The id of the Type in the database
	 * @param int $importNo The import number of this Type
	 * @param string $description The description of this generic
	 * @param int $genericId The id of the Generic associated to this Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type
	 */
	function __construct(?int $id = null, ?int $importNo = null, ?string $description = null, ?int $genericId = null)
	{
	    $this->setId($id);
	    $this->setImportNo($importNo);
	    $this->setDescription($description);
	    $this->setGenericId($genericId);
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
	public static function withId($db, $id) :?Type
	{
	    // Récupérer le test
	    $stmt = $db->getConnection()->prepare("SELECT `dt`.* FROM `fabplan`.`door_types` AS `dt` WHERE `dt`.`id` = :id;");
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	// Récupération de l'instance de matériel
	    {
	        $instance = new self($row["id"], $row["importNo"], $row["description"], $row["generic_id"]);
	    }
	    else
	    {
	        return null;
	    }
	    
	    return $instance;
	}
	
	/**
	 * Save the Type object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be saved
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type This Type (for method chaining)
	 */
	function save(FabPlanConnection $db) : Type
	{
	    if($this->_id === null)
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
	 * Insert the Type object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be inserted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type This Type (for method chaining)
	 */
	private function insert(FabPlanConnection $db) : Type
	{
        // Création d'un test
        $stmt = $db->getConnection()->prepare("
            INSERT INTO `fabplan`.`door_types` (`importNo`, `description`, `generic_id`)
            VALUES (:importNo, :description, :generic_id);
        ");
        $stmt->bindValue(':importNo', $this->getImportNo(), PDO::PARAM_INT);
        $stmt->bindValue(':description', $this->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(":generic_id", $this->getGenericId(), PDO::PARAM_INT);
        $stmt->execute();
        $this->_id = $db->getConnection()->lastInsertId();
        
        return $this;
	}
	
	/**
	 * Update the Type object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be updated
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type This Type (for method chaining)
	 */
	private function update(FabPlanConnection $db) :Type
	{ 
        // Mise à jour d'un test
        $stmt = $db->getConnection()->prepare("
            UPDATE `fabplan`.`door_types`
            SET `importNo` = :importNo, `description` = :description, `generic_id` = :genericId
            WHERE `id` = :id;
        ");
        $stmt->bindValue(':importNo', $this->getImportNo(), PDO::PARAM_INT);
        $stmt->bindValue(':description', $this->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(":genericId", $this->getGenericId(), PDO::PARAM_INT);
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
	public function delete(FabPlanConnection $db) : Type
	{
	    $stmt = $db->getConnection()->prepare("
            DELETE FROM `fabplan`.`door_model_data` WHERE `fkDoorType` = :importNo;
        ");
	    $stmt->bindValue(':importNo', $this->getImportNo(), PDO::PARAM_INT);
	    $stmt->execute();
	    
        $stmt = $db->getConnection()->prepare("
            DELETE FROM `fabplan`.`door_types` WHERE `id` = :id;
        ");
        $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
        $stmt->execute();
        
        return $this;
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
	 * Get the id of the Generic associated to this Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of the Generic associated to this Type
	 */
	public function getGenericId() : ?int
	{
	    return $this->_genericId;
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
	public function getModelTypeParametersForAllModels(FabplanConnection $db) : ?array
	{
	    $stmt = $db->getConnection()->prepare("
            SELECT `dmd`.`fkDoorModel` AS `modelId`, `dmd`.`paramKey` AS `parameterKey`, `dmd`.`paramValue` AS `parameterValue`
            FROM `fabplan`.`door_model_data` AS `dmd`
            WHERE `dmd`.`fkDoorType` = :typeNo;
        ");
	    $stmt->bindValue(":typeNo", $this->getImportNo(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $modelTypeParameters = array();
	    while($row = $stmt->fetch())
	    {
	        array_push(
	            $modelTypeParameters, 
	            new ModelTypeParameter($row["parameterKey"], $row["parameterValue"], $row["modelId"], $this->getImportNo())
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
	 * Set the id of the generic associated to this Type
	 *
	 * @param int $genericId The id of the new generic associated to this Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type This Type (for method chaining)
	 */
	public function setGenericId(?int $genericId) : Type
	{
	    $this->_genericId = $genericId;
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