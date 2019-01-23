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
include_once __DIR__ .  '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ .  '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ .  '/../model/model.php';	// Classe de modèle

class ModelController {

	private $_db;
    
	function __construct()
	{
		$this->connect();	
	}
	
	/**
	 * Get a Model by id
	 *
	 * @param int $id The id of a Model
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Model The Model that has this id (null if none)
	 */
	function getModel(?int $id) : ?Model
	{
	    return Model::withID($this->getDBConnection(), $id);
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
	 * @return Model array The array of Model objects requested
	 */
	function getModels(int $offset = 0, int $quantity = 0, bool $ascending = true) : array
	{
	    $stmt = $this->getDBConnection()->getConnection()->prepare("
            SELECT `dm`.`id_door_model` AS `id`
            FROM `fabplan`.`door_model` AS `dm`
            ORDER BY `dm`.`description_model` " . (($ascending === true) ? "ASC" : "DESC") .
	        (($quantity === 0) ? "" : " LIMIT :quantity OFFSET :offset") .
	        ";"
	        );
	    $stmt->bindValue(":quantity", $quantity, PDO::PARAM_INT);
	    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $models = array();
	    while($row = $stmt->fetch(PDO::FETCH_ASSOC))
	    {
	        array_push($models, Model::withID($this->getDBConnection(), $row["id"]));
	    }
	    return $models;
	}
	
	/**
	 * Connect to the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return TestController This TestController
	 */ 
	private function connect()
	{
	    $this->_db = new FabPlanConnection();
	    return $this;
	}

	/**
	 * Get the connection to the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return FabplanConnection The connection to the database
	 */ 
    public function getDBConnection() :FabPlanConnection
    {
        return $this->_db;
    }
}
?>