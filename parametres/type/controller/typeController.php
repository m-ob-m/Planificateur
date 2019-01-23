<?php

/**
* \name		TypeController
* \author    	Marc-Olivier Bazin-Maurice
* \version		1.0
* \date       	2017-01-27
*
* \brief 		Contrôleur de type de porte
* \details 		Contrôleur de type de porte
*/

/*
 * Includes
*/
include_once __DIR__ .  '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ .  '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ .  '/../model/type.php';	// Classe de Type

class TypeController {

	private $_db;
    
	function __construct()
	{
		$this->connect();	
	}
	
	/**
	 * Get a Type by id
	 *
	 * @param int $id The id of a Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type The Type that has this id (null if none)
	 */
	function getType(?int $id) : ?Type
	{
	    return Type::withID($this->getDBConnection(), $id);
	}
    
	/**
	 * Get a list of Type from the database
	 *
	 * @param int $quantity The number of records to return
	 * @param int $offset The amount of records to skip from the beginning of the recordset
	 * @param bool $ascending Specifies wheter the results must be returned in ascending or descending order of id
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type array The array of Type objects requested
	 */
	function getTypes(int $offset = 0, int $quantity = 0, bool $ascending = true) : array
	{
	    $stmt = $this->getDBConnection()->getConnection()->prepare("
            SELECT `dt`.`id` AS `id`
            FROM `fabplan`.`door_types` AS `dt`
            ORDER BY `dt`.`importNo` " . (($ascending === true) ? "ASC" : "DESC") .
	        (($quantity === 0) ? "" : " LIMIT :quantity OFFSET :offset") .
	        ";"
	        );
	    $stmt->bindValue(":quantity", $quantity, PDO::PARAM_INT);
	    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $types = array();
	    while($row = $stmt->fetch(PDO::FETCH_ASSOC))
	    {
	        array_push($types, Type::withID($this->getDBConnection(), $row["id"]));
	    }
	    return $types;
	}
	
	/**
	 * Get a Type by import number
	 *
	 * @param int $id The import number of a Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Type The Type that has this import number (null if none)
	 */
	function getTypeByImportNo(?int $importNo) : ?Type
	{
	    $stmt = $this->getDBConnection()->getConnection()->prepare("
            SELECT `dt`.`id` AS `id`
            FROM `fabplan`.`door_types` AS `dt`
            WHERE `dt`.`importNo` = :importNo
            ;"
	    );
	    $stmt->bindValue(":importNo", $importNo, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if($row = $stmt->fetch())
	    {
	       return $this->getType($row["id"]);
	    }
	    else
	    {
	        throw new Exception("No type with import number \" {$importNo}\" was found.");
	    }
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