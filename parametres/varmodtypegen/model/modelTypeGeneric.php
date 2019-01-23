<?php

/**
 * \name		ModeleTypeGeneric
* \author    	Marc-Olivier Bazin-Maurice
* \version		1.0
* \date       	2017-03-20
*
* \brief Représente toutes les valeurs d'un modèle/type/générique (paramétrie finale de l'objet après combinaison du modèle, 
* du type et du générique sans discrimination sur la source des paramètres. Cette classe sert à faciliter la génération des objets 
* JobType et TestType)
* \details Représente toutes les valeurs d'un modèle/type/générique (paramétrie finale de l'objet après combinaison du modèle, 
* du type et du générique sans discrimination sur la source des paramètres. Cette classe sert à faciliter la génération d'objets appliqués 
* tels les JobType et TestType)
*/

include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/modelTypeGenericParameter.php'; // Classe de paramètres pour cet objet
include_once __DIR__ . '/../../varmodtype/model/modelType.php'; // Classe de combinaison modèle-type
include_once __DIR__ . '/../../type/controller/typeController.php'; // Classe de combinaison modèle-type

class ModelTypeGeneric extends ModelType implements JsonSerializable
{   
    protected $_generic_id;
    
	/**
	 * Build a new ModelTypeGeneric object.
	 *
	 * @param int $modelId The model id of the combination.
	 * @param int $typeId The type id of the combination.
	 * @param array $parameters The parameters of the model/type combination 
	 * @param int $genericId The id of the Generic associated to this Test
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return ModelTypeGeneric This ModelTypeGeneric (for method chaining)
	 */
    public function __construct(?int $modelId = null, ?int $typeNo = null, array $parameters = array(), ?int $genericId = null)
	{
	   parent::__construct($modelId, $typeNo, $parameters);
	    $this->setGenericId($genericId);
	}

	/**
	 * Load parameters from the database for the specified ModelTypeGeneric combination.
	 *
	 * @param FabPlanConnection $db The database containing parameters to fetch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return ModelTypeGeneric This ModelTypeGeneric (for method chaining)
	 */
	public function loadParameters(FabPlanConnection $db)
	{
        $modelId = $this->getModelId();
        $typeNo = $this->getTypeNo();
        
	    $stmt = $db->getConnection()->prepare("
        	SELECT `gp`.`parameter_key` AS `key`, `dmd`.`paramValue` AS `specificValue`, `gp`.`parameter_value` AS `genericValue`, 
                `gp`.`description` AS `description`
        	FROM `fabplan`.`door_types` AS `dt`
        	INNER JOIN `fabplan`.`generics` AS `g` ON `dt`.`generic_id` = `g`.`id` AND `dt`.`importNo` = :typeNo
        	INNER JOIN `generic_parameters` AS `gp` ON `gp`.`generic_id` = `g`.`id`
        	INNER JOIN `fabplan`.`door_model` AS `dm` ON `dm`.`id_door_model` = :modelId
        	LEFT JOIN `fabplan`.`door_model_data`AS `dmd` ON `dmd`.`paramKey` = `gp`.`parameter_key`
        		AND `dmd`.`fkDoorModel` = `dm`.`id_door_model` AND `dmd`.`fkDoorType` = `dt`.`importNo`
            ORDER BY `gp`.`id` ASC;
        ");
	    $stmt->bindValue(':modelId', $modelId, PDO::PARAM_INT);
	    $stmt->bindValue(':typeNo', $typeNo, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $this->setParameters(array());
	    foreach($stmt->fetchAll() as $row)
	    {
	        $key = $row['key'];
	        $specific = (($row['specificValue'] === "") ? null : $row['specificValue']);
	        $description = $row["description"];
	        $default = (($row['genericValue'] === "") ? null : $row['genericValue']);
	        array_push($this->_parameters, new ModelTypeGenericParameter($key, $specific, $modelId, $typeNo, $description, $default));
	    }
	    
	    return $this;
	}
	
	/**
	 * Set the id of the associated generic file
	 *
	 * @param int $generic The new id of the Generic
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return ModelTypeGeneric This ModelTypeGeneric (for method chaining)
	 */
	public function setGenericId(?int $genericId = null) : ModelTypeGeneric
	{
	    if($genericId === null && $this->getTypeNo() !== null)
	    {
	        $this->_generic_id = (new TypeController())->getTypeByImportNo($this->getTypeNo())->getGenericId();
	    }
	    else
	    {
	        $this->_generic_id = $genericId;
	    }
	    
	    return $this;
	}
	
	/**
	 * Get the id of the generic associated with this ModelTypeGeneric
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of the Generic associated with this ModelTypeGeneric
	 */
	public function getGenericId() : ?int
	{
	    return $this->_generic_id;
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