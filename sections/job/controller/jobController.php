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
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/job/model/job.php";

class JobController
{
    private $_db;
    
    /**
     * JobController constructor
     *
     * @param \FabplanConnection $db A database
     * 
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobController This JobController
     */
    function __construct(\FabplanConnection $db)
    {
        $this->_db = $db;
    }
    
    /**
     * Get a Job by id
     *
     * @param int $id The id of a Job
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Job The Job that has this id (null if none)
     */
    function getJob(int $id) : ?Job
    {
        return \Job::withID($this->_db, $id);
    }
    
    /**
     * Get a Job by name
     *
     * @param string $name The name of a Job
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Job The Job that has this name (null if none)
     */
    function getJobByName(string $name) : ?\Job
    {
        $stmt = $this->_db->getConnection()->prepare("
            SELECT `j`.`id_job` AS `id` FROM `job` AS `j` 
            WHERE `j`.`numero` = :name;
        ");
        $stmt->bindValue(":name", $name, \PDO::PARAM_STR);
        $stmt->execute();
        
        if($row = $stmt->fetch())
        {
            return \Job::withID($this->_db, $row["id"]);
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
        $stmt = $this->_db->getConnection()->prepare("
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
            array_push($jobs, \Job::withID($this->_db, $row["id"]));
        }
        
        return $jobs;
    }
}
?>