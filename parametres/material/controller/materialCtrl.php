<?php

/**
 * \name		MaterialController
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-01-18
*
* \brief 		Contrôleur de matériel
* \details 		Contrôleur de matériel
*/

/*
 * Includes
*/
require_once __DIR__ .  '/../../../lib/config.php';	// Fichier de configuration
require_once __DIR__ .  '/../../../lib/connect.php';	// Classe de connection à la base de données
require_once __DIR__ .  '/../model/material.php';	// Classe de matériel

class MaterialController
{
	private $_db;

	function __construct()
	{
		$this->connect();	
	}
	
	/**
	 * Get a Material by id
	 *
	 * @param int $id The id of a Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material The Material that has this id (null if none)
	 */
	function getMaterial(?int $id) : ?Material
	{
	    return Material::withID($this->getDBConnection(), $id);
	}
    
	/**
	 * Get a list of Material from the database
	 *
	 * @param int $quantity The number of records to return
	 * @param int $offset The amount of records to skip from the beginning of the recordset
	 * @param bool $ascending Specifies wheter the results must be returned in ascending or descending order of id
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material array The array of Material objects requested
	 */
	function getMaterials(int $offset = 0, int $quantity = 0, bool $ascending = true) : array
	{
	    $stmt = $this->getDBConnection()->getConnection()->prepare("
            SELECT `m`.`id`
            FROM `material` AS `m`
            ORDER BY `m`.`id` " . (($ascending === true) ? "ASC" : "DESC") . " " . 
	        (($quantity === 0) ? "" : " LIMIT :quantity OFFSET :offset") . " " . 
	        "FOR SHARE;"
	        );
	    $stmt->bindValue(":quantity", $quantity, PDO::PARAM_INT);
	    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $materials = array();
	    while($row = $stmt->fetch(PDO::FETCH_ASSOC))
	    {
	        array_push($materials, Material::withID($this->getDBConnection(), $row["id"]));
	    }
	    
	    return $materials;
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