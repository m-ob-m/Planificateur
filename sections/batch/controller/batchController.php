<?php

/**
 * \name		BatchController
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-01-26
*
* \brief 		Contrôleur d'une batch
* \details 		Contrôleur d'une batch
*/

/*
 * Includes
*/
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/batch/model/batch.php";

class BatchController
{
    private $_db;
    
    /**
     * BatchController constructor
     * @param \FabplanConnection A database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \BatchController This BatchController
     */
    function __construct(\FabplanConnection $db)
    {
        $this->_db = $db;
    }
    
    /**
     * Get a Batch by id
     *
     * @param int $id The id of a Batch
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Batch The Batch that has this id (null if none)
     */
    function getBatch(?int $id) : ?\Batch
    {
        return Batch::withID($this->_db, $id);
    }
    
    /**
     * Get a list of Batch from the database
     *
     * @param int $quantity The number of records to return
     * @param int $offset The amount of records to skip from the beginning of the recordset
     * @param bool $ascending Specifies wheter the results must be returned in ascending or descending order of id
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Batch[] The array of Batch objects requested
     */
    function getBatches(int $offset = 0, int $quantity = 0, bool $ascending = true) : array
    {
        $stmt = $this->_db->getConnection()->prepare("
            SELECT `b`.`id`
            FROM `batch` AS `b`
            ORDER BY `t`.`id` " . (($ascending === true) ? "ASC" : "DESC") .
            (($quantity === 0) ? "" : " LIMIT :quantity OFFSET :offset") .
            "FOR SHARE;"
        );
        $stmt->bindValue(":quantity", $quantity, \PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        $batches = array();
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC))
        {
            array_push($batches, \Batch::withID($this->_db, $row["id"]));
        }
        
        return $batches;
    }
}
?>