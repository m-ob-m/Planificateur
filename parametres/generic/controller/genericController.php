<?php

/**
 * \name		GenericController
* \author    	Marc-olivier Bazin-Maurice
* \version		1.0
* \date       	2018-04-19
*
* \brief 		Contrôleur de Generic
* \details 		Contrôleur de Generic
*/

/*
 * Includes
*/
require_once __DIR__ .  '/../../../lib/config.php';	// Fichier de configuration
require_once __DIR__ .  '/../../../lib/connect.php';	// Classe de connection à la base de données
require_once __DIR__ .  '/../model/generic.php';	// Classe de matériel

class GenericController
{
	private $_db;

	function __construct()
	{
		$this->connect();	
	}
	
	/**
	 * Get a Generic by id
	 *
	 * @param int $id The id of a Generic
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Generic The Generic that has this id (null if none)
	 */
	function getGeneric(?int $id) : ?Generic
	{
	    return \Generic::withID($this->getDBConnection(), $id);
	}
    
	/**
	 * Get a list of Generic from the database
	 *
	 * @param int $quantity The number of records to return
	 * @param int $offset The amount of records to skip from the beginning of the recordset
	 * @param bool $ascending Specifies wheter the results must be returned in ascending or descending order of id
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Generic array The array of Generic objects requested
	 */
	function getGenerics(int $offset = 0, int $quantity = 0, bool $ascending = true) : array
	{
	    $stmt = $this->getDBConnection()->getConnection()->prepare("
            SELECT `g`.`id`
            FROM `generics` AS `g`
            ORDER BY `g`.`id` " . (($ascending === true) ? "ASC" : "DESC") . " " . 
	        (($quantity === 0) ? "" : "LIMIT :quantity OFFSET :offset") . " " . 
	        "FOR SHARE;"
	    );
	    $stmt->bindValue(":quantity", $quantity, PDO::PARAM_INT);
	    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $generics = array();
	    while($row = $stmt->fetch(PDO::FETCH_ASSOC))
	    {
	        array_push($generics, \Generic::withID($this->getDBConnection(), $row["id"]));
	    }
	    
	    return $generics;
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