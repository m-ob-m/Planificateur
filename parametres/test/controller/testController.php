<?php

/**
 * \name		TestController
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2017-10-31
 *
 * \brief 		Controlleur d'un Test
 * \details 	Controlleur d'un Test
 */

/*
 * Includes
 */
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données

include_once __DIR__ . '/../model/test.php';	// Classe d'un test

class TestController 
{
    private $_db;
    
    /**
     * TestController constructor
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return TestController This TestController
     */ 
    function __construct()
    {
        $this->connect();
    }   
    
    /**
     * Get a Test by id
     *
     * @param int $id The id of a Test
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Test The test that has this id (null if none)
     */ 
    function getTest(?int $id) : ?Test
    {
        return Test::withID($this->getDBConnection(), $id);
    }
    
    /**
     * Get a list of tests from the database
     *
     * @param int $quantity The number of records to return
     * @param int $offset The amount of records to skip from the beginning of the recordset
     * @param bool $ascending Specifies whether the results must be returned in ascending or descending order of id
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Test array The array of Test objects requested
     */ 
    function getTests(int $offset = 0, int $quantity = 0, bool $ascending = true) : array
    {
        $stmt = $this->getDBConnection()->getConnection()->prepare("
            SELECT `t`.`id` 
            FROM `test` AS `t` 
            ORDER BY `t`.`id` " . ($ascending ? "ASC" : "DESC") . " " . 
            (($quantity === 0) ? "" : " LIMIT :quantity OFFSET :offset") . " " . 
            "FOR SHARE;"
        );
        $stmt->bindValue(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $tests = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            array_push($tests, Test::withID($this->getDBConnection(), $row["id"]));
        }
        
        return $tests;
    }
    
    /**
     * Get a list of tests created or modified for the last time between two dates.
     *
     * @param string $startDate The lower bound of the time interval
     * @param string $endDate The upper bound of the time interval
     * @param bool $ascending Specifies whether the tests must be returned by ascending timestamp or not
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Test array The array of Test objects requested
     */ 
    function getTestsBetweenTwoDates($startDate, $endDate, $ascending = true)
    {
        $stmt = $this->getDBConnection()->getConnection()->prepare("
            SELECT `t`.`id`
            FROM `test` AS `t`
            WHERE `t`.`estampille` >= :startDate AND `t`.`estampille` <= :endDate
            ORDER BY `t`.`estampille` " . ($ascending ? "ASC" : "DESC") . " " . 
            "FOR SHARE;"
        );
        $stmt->bindValue(":startDate", $startDate, PDO::PARAM_STR);
        $stmt->bindValue(":endDate", $endDate, PDO::PARAM_STR);
        $stmt->execute();
        
        $tests = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            array_push($tests, Test::withID($this->getDBConnection(), $row["id"]));
        }
        
        return $tests;
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
     * @return TestController This TestController
     */ 
    function connect()
    {
        $this->_db = new FabPlanConnection();
        return $this;
    }
}

?>