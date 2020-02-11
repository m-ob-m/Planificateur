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
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/type/model/type.php";

class TypeController {

	private $_db;
	
	/**
	 * Get a Type by id
	 *
	 * @param \FabplanConnection $db A database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \TypeController This TypeController
	 */
	function __construct(\FabplanConnection $db)
	{
		$this->_db = $db;	
	}
	
	/**
	 * Get a Type by id
	 *
	 * @param int $id The id of a Type
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Type The Type that has this id (null if none)
	 */
	function getType(?int $id) : ?\Type
	{
	    return \Type::withID($this->_db, $id);
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
	 * @return \Type array The array of Type objects requested
	 */
	function getTypes(int $offset = 0, int $quantity = 0, bool $ascending = true) : array
	{
	    $stmt = $this->_db->getConnection()->prepare("
            SELECT `dt`.`id` AS `id`
            FROM `door_types` AS `dt`
            ORDER BY `dt`.`importNo` " . (($ascending === true) ? "ASC" : "DESC") .
	        (($quantity === 0) ? "" : " LIMIT :quantity OFFSET :offset") .
	        ";"
	    );
	    $stmt->bindValue(":quantity", $quantity, \PDO::PARAM_INT);
	    $stmt->bindValue(":offset", $offset, \PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $types = array();
	    while($row = $stmt->fetch(\PDO::FETCH_ASSOC))
	    {
	        array_push($types, \Type::withID($this->_db, $row["id"]));
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
	 * @return \Type The Type that has this import number (null if none)
	 */
	function getTypeByImportNo(?int $importNo) : ?\Type
	{
	    $stmt = $this->_db->getConnection()->prepare("
            SELECT `dt`.`id` AS `id`
            FROM `door_types` AS `dt`
            WHERE `dt`.`importNo` = :importNo
            ;"
	    );
	    $stmt->bindValue(":importNo", $importNo, \PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if($row = $stmt->fetch())
	    {
	       return $this->getType($row["id"]);
	    }
	    else
	    {
	        throw new \Exception("No type with import number \" {$importNo}\" was found.");
	    }
	}
}
?>