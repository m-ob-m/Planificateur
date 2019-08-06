<?php

/**
 * \name		JobController
* \author    	Marc-Olivier Bazin-Maurice
* \version		1.0
* \date       	2017-01-26
*
* \brief 		Contrôleur d'un Job
* \details 		Contrôleur d'un Job
*/

/*
 * Includes
*/
require_once __DIR__ . '/../../../lib/config.php';		// Fichier de configuration
require_once __DIR__ . '/../../../lib/connect.php';	    // Classe de connection à la base de données
require_once __DIR__ . '/../model/job.php';			// Classe d'une job

class JobController
{
    private $_db;
    
    /**
     * JobController constructor
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobController This JobController
     */
    function __construct()
    {
        $this->connect();
    }
    
    /**
     * Get a Job by id
     *
     * @param int $id The id of a Job
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Job The Job that has this id (null if none)
     */
    function getJob(int $id) : ?Job
    {
        return \Job::withID($this->getDBConnection(), $id);
    }
    
    /**
     * Get a Job by name
     *
     * @param string $name The name of a Job
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Job The Job that has this name (null if none)
     */
    function getJobByName(string $name) : ?Job
    {
        $stmt = $this->getDBConnection()->getConnection()->prepare("
            SELECT `j`.`id_job` AS `id` FROM `job` AS `j` 
            WHERE `j`.`numero` = :name;
        ");
        $stmt->bindValue(":name", $name, \PDO::PARAM_STR);
        $stmt->execute();
        
        if($row = $stmt->fetch())
        {
            return \Job::withID($this->getDBConnection(), $row["id"]);
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Get a list of Job from the database
     *
     * @param int $quantity The number of records to return
     * @param int $offset The amount of records to skip from the beginning of the recordset
     * @param bool $ascending Specifies wheter the results must be returned in ascending or descending order of id
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Job array The array of Job objects requested
     */
    function getJobs(int $offset = 0, int $quantity = 0, bool $ascending = true) : array
    {
        $stmt = $this->getDBConnection()->getConnection()->prepare("
            SELECT `j`.`id`
            FROM `job` AS `j`
            ORDER BY `j`.`id_job` " . (($ascending === true) ? "ASC" : "DESC") .
            (($quantity === 0) ? "" : " LIMIT :quantity OFFSET :offset") .
            ";"
        );
        $stmt->bindValue(":quantity", $quantity, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $jobs = array();
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC))
        {
            array_push($jobs, \Job::withID($this->getDBConnection(), $row["id"]));
        }
        
        return $jobs;
    }
    
    /**
     * Get the connection to the database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \FabplanConnection The connection to the database
     */
    function getDBConnection() : \FabPlanConnection
    {
        return $this->_db;
    }
    
    /**
     * Set the connection to the database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobController This JobController
     */
    function connect()
    {
        $this->_db = new \FabPlanConnection();
        return $this;
    }
}
?>