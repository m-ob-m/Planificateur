<?php

/**
* \name		ModelController
* \author    	Marc-Olivier Bazin-Maurice
* \version		1.0
* \date       	2017-01-27
*
* \brief 		Controlleur de modèle de porte
* \details 		Controlleur de modèle de porte
*/

/*
 * Includes
*/
require_once $_SERVER["DOCUMENT_ROOT"] .  "/Planificateur/parametres/model/model/model.php";

class ModelController {

	private $_db;
    
	function __construct(\FabplanConnection $db)
	{
		$this->_db = $db;	
	}
	
	/**
	 * Get a Model by id
	 *
	 * @param int $id The id of a Model
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Model The Model that has this id (null if none)
	 */
	function getModel(?int $id) : ?Model
	{
	    return \Model::withID($this->_db, $id);
	}
    
	/**
	 * Get a list of Model from the database
	 *
	 * @param int $quantity The number of records to return
	 * @param int $offset The amount of records to skip from the beginning of the recordset
	 * @param bool $ascending Specifies wheter the results must be returned in ascending or descending order of id
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Model[] The array of Model objects requested
	 */
	function getModels(int $offset = 0, int $quantity = 0, bool $ascending = true) : array
	{
	    $stmt = $this->_db->getConnection()->prepare("
            SELECT `dm`.`id_door_model` AS `id`
            FROM `door_model` AS `dm`
            ORDER BY `dm`.`description_model` " . (($ascending === true) ? "ASC" : "DESC") . " " . 
			(($quantity === 0) ? "" : " LIMIT :quantity OFFSET :offset") . 
			";"
	    );
	    $stmt->bindValue(":quantity", $quantity, \PDO::PARAM_INT);
	    $stmt->bindValue(":offset", $offset, \PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $models = array();
	    while($row = $stmt->fetch(\PDO::FETCH_ASSOC))
	    {
	        array_push($models, \Model::withID($this->_db, $row["id"]));
	    }
	    return $models;
	}
}
?>