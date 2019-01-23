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

	/**
	 * Model constructor
	 *
	 * @param int $id The id of the Model in the database
	 * @param string $description The description of the Model
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test
	 */
	function __construct(?int $id = null, ?string $description = null)
	{
	    $this->setId($id);
	    $this->setDescription($description);
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
	public static function withID($db, $id) :?Model
	{
	    // Récupérer le Model
	    $stmt = $db->getConnection()->prepare("SELECT `dm`.* FROM `fabplan`.`door_model` AS `dm` WHERE `dm`.`id_door_model` = :id;");
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	// Récupération de l'instance de Model
	    {
	        $instance = new self($row["id_door_model"], $row["description_model"]);
	    }
	    else
	    {
	        return null;
	    }
	    
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
	    if(self::withId($db, $this->getId()) === null)
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
	    $stmt = $db->getConnection()->prepare("
            SELECT `dmd`.`fkDoorType` AS `typeNo`, `dmd`.`paramKey` AS `parameterKey`, `dmd`.`paramValue` AS `parameterValue`
            FROM `fabplan`.`door_model_data` AS `dmd`
            WHERE `dmd`.`fkDoorModel` = :modelId;
        ");
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
	public function setId(?int $id) :Model
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
	public function setDescription(?string $description) :Model
	{
	    $this->_description = $description;
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