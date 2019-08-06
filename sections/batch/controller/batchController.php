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
require_once __DIR__ . '/../../../lib/config.php';		// Fichier de configuration
require_once __DIR__ . '/../../../lib/connect.php';	    // Classe de connection à la base de données
require_once __DIR__ . '/../model/batch.php';			// Classe d'une batch

class BatchController
{
    private $_db;
    
    /**
     * BatchController constructor
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return BatchController This BatchController
     */
    function __construct()
    {
        $this->connect();
    }
    
    /**
     * Get a Batch by id
     *
     * @param int $id The id of a Batch
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Batch The Batch that has this id (null if none)
     */
    function getBatch(?int $id) : ?Batch
    {
        return Batch::withID($this->getDBConnection(), $id);
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
     * @return Batch array The array of Batch objects requested
     */
    function getBatches(int $offset = 0, int $quantity = 0, bool $ascending = true) : array
    {
        $stmt = $this->getDBConnection()->getConnection()->prepare("
            SELECT `b`.`id`
            FROM `batch` AS `b`
            ORDER BY `t`.`id` " . (($ascending === true) ? "ASC" : "DESC") .
            (($quantity === 0) ? "" : " LIMIT :quantity OFFSET :offset") .
            "FOR SHARE;"
        );
        $stmt->bindValue(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $batches = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            array_push($batches, Batch::withID($this->getDBConnection(), $row["id"]));
        }
        
        return $batches;
    }
    
    /**
     * Get the connection to the database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return FabplanConnection The connection to the database
     */
    function getDBConnection() : FabPlanConnection
    {
        return $this->_db;
    }
    
    /**
     * Set the connection to the database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return BatchController This BatchController
     */
    function connect()
    {
        $this->_db = new FabPlanConnection();
        return $this;
    }
}
?>