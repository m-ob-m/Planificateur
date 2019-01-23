<?php
/**
 * \name		Job
 * \author    	Mathieu Grenier
 * \version		1.0
 * \date       	2017-01-18
 *
 * \brief 		Modele de la table job
 * \details 	Modele de la table job
 */

include_once __DIR__ . '/job_type.php';	// Classe d'un type de job
include_once __DIR__ . '/job_type_porte.php';	// Classe d'une porte de type
include_once __DIR__ . "/../../batch/model/batch.php"; // Classe d'une batch

class Job implements JsonSerializable
{	
	private $_id;
	private $_name;
	private $_deliveryDate;
	private $_status;
	private $_timestamp;
	
	private $_jobTypes;	// Array de job types
	
	/**
	 * Job constructor
	 *
	 * @param int $id The id of the Job in the database
	 * @param int $name The name of the Job (must be unique)
	 * @param int $deliveryDate The delivery date of the job
	 * @param int $status The status of the Job ("E" = Entered, "G" = Generated, "T" = Done)
	 * @param string $timestamp A timestamp of the last modification applied to this Job (leave null)
	 * @param string $parameters An array containing the JobTypeParameters objects associated with this JobType.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return JobType
	 */
	public function __construct(?int $id = null, ?string $name = null, ?string $deliveryDate = null, ?string $status = null,
	    ?string $timestamp = null, array $jobTypes = array())
	{
	    $this->setId($id);
	    $this->setName($name);
	    $this->setDeliveryDate($deliveryDate);
	    $this->setStatus($status);
	    $this->setTimestamp($timestamp);
	    $this->setJobtypes($jobTypes);
	}
	
	/**
	 * Job constructor using ID of existing record
	 *
	 * @param FabPlanConnection $db The database in which the record exists
	 * @param int $id The id of the record in the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Job The Job associated to the specified ID in the specified database
	 */
	static function withID(FabPlanConnection $db, int $id) :?Job
	{
	    // Récupérer le test
	    $stmt = $db->getConnection()->prepare("
            SELECT `j`.`numero` AS `name`, `j`.`date_livraison` AS `deliveryDate`, `j`.`etat` AS `status`, 
                `j`.`estampille` AS `timestamp`
            FROM `fabplan`.`job` AS `j` WHERE `j`.`id_job` = :id;
        ");
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())
	    {
	        $instance = new self($id, $row["name"], $row["deliveryDate"], $row["status"], $row["timestamp"]);
	    }
	    else
	    {
	        return null;
	    }
	    
	    //Récupérer les paramètres
	    $stmt = $db->getConnection()->prepare("
            SELECT `jt`.`id_job_type` AS `id` 
            FROM `fabplan`.`job_type` AS `jt` 
            WHERE `jt`.`job_id` = :id 
            ORDER BY `jt`.`type_no` ASC, `jt`.`door_model_id` ASC;
        ");
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    while($row = $stmt->fetch())	// Récupération des paramètres
	    {
	        array_push($instance->_jobTypes, Jobtype::withID($db, $row["id"]));
	    }
	    
	    return $instance;
	}
	
	/**
	 * Job constructor using name of existing Job
	 *
	 * @param FabPlanConnection $db The database in which the record exists
	 * @param string $name The name of the Job in the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Job The Job associated to the specified name in the specified database
	 */
	static function withName(FabPlanConnection $db, string $name) :?Job
	{
	    // Récupérer le Job
	    $stmt = $db->getConnection()->prepare("
            SELECT `j`.`id_job` AS `id`, `j`.`date_livraison` AS `deliveryDate`, `j`.`etat` AS `status`, 
                `j`.`estampille` AS `timestamp`
            FROM `fabplan`.`job` AS `j` WHERE `j`.`numero` = :name;
        ");
	    $stmt->bindValue(':name', $name, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())
	    {
	        $instance = new self($row["id"], $name, $row["deliveryDate"], $row["status"], $row["timestamp"]);
	    }
	    else
	    {
	        return null;
	    }
	    
	    //Récupérer les JobType
	    $stmt = $db->getConnection()->prepare("
            SELECT `jt`.`id_job_type` AS `id` 
            FROM `fabplan`.`job_type` AS `jt` 
            WHERE `jt`.`job_id` = :id 
            ORDER BY `jt`.`type_no` ASC, `jt`.`door_model_id` ASC;
        ");
	    $stmt->bindValue(':id', $instance->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    while($row = $stmt->fetch())	// Récupération des paramètres
	    {
	        array_push($instance->_jobTypes, Jobtype::withID($db, $row["id"]));
	    }
	    
	    return $instance;
	}
	
	/**
	 * Save the Job object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be saved
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Job This Job (for method chaining)
	 */
	public function save(FabPlanConnection $db) : Job
	{
	    $stmt = $db->getConnection()->prepare("
                SELECT `j`.* FROM `fabplan`.`job` AS `j`
                WHERE `j`.`id_job` = :id LIMIT 1;
            ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    $this->emptyInDatabase($db);
	    if($stmt->fetch(PDO::FETCH_ASSOC) == null)
	    {
	        $this->insert($db);
	    }
	    else
	    {
	        $this->update($db);
	    }
	    
	    return $this;
	}
	
	/**
	 * Insert the Job object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be inserted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Job This Job (for method chaining)
	 */
	private function insert(FabPlanConnection $db) : Job
	{
	    $stmt = $db->getConnection()->prepare("
            INSERT INTO `fabplan`.`job` (`numero`, `date_livraison`, `etat`)
            VALUES (:name, :deliveryDate, :status);
        ");
	    $stmt->bindValue(':name', $this->getName(), PDO::PARAM_STR);
	    $stmt->bindValue(':deliveryDate', $this->getDeliveryDate(), PDO::PARAM_STR);
	    $stmt->bindValue(':status', $this->getStatus(), PDO::PARAM_STR);
	    $stmt->execute();
	    $this->setId($db->getConnection()->lastInsertId());
	    
	    foreach($this->_jobTypes as $jobType)
	    {
	        $jobtype->save($db);
	    }
	    
	    return $this;
	}
	
	/**
	 * Update the Job object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be updated
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Job This Job (for method chaining)
	 */
	private function update(FabPlanConnection $db) : Job
	{
	    $stmt = $db->getConnection()->prepare("
            UPDATE `fabplan`.`job`
            SET `numero` = :name, `date_livraison` = :deliveryDate, `etat` = :status
            WHERE `id_job` = :id;
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->bindValue(':name', $this->getName(), PDO::PARAM_STR);
	    $stmt->bindValue(':deliveryDate', $this->getDeliveryDate(), PDO::PARAM_STR);
	    $stmt->bindValue(':status', $this->getStatus(), PDO::PARAM_STR);
	    $success = $stmt->execute();
	    
	    foreach($this->_jobTypes as $jobType)
	    {
	        $jobType->save($db);
	    }
	    
	    return $this;
	}
	
	/**
	 * Delete the JobType object from the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return JobType This JobType (for method chaining)
	 */
	public function delete(FabPlanConnection $db) : Job
	{   
	    $this->emptyInDatabase($db);
	    
	    $stmt = $db->getConnection()->prepare("DELETE FROM `fabplan`.`job` WHERE `id_job` = :id;");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    return $this;
	}
	
	/**
	 * Empties a job in the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return JobType This JobType (for method chaining)
	 */
	public function emptyInDatabase(FabPlanConnection $db) : Job
	{
	    foreach($this->_jobTypes as $jobType)
	    {
	        $jobType->delete($db);
	    }
	    
	    return $this;
	}
	
	
	public function getParentBatch(FabPlanConnection $db) : ?Batch
	{
	    $stmt = $db->getConnection()->prepare("
            SELECT `b`.`id_batch` AS `batchId` 
            FROM `fabplan`.`job` AS `j`
            INNER JOIN `fabplan`.`batch_job` AS `bj` ON `bj`.`job_id` = `j`.`id_job`
            INNER JOIN `fabplan`.`batch` AS `b` ON `b`.`id_batch` = `bj`.`batch_id`
            WHERE `j`.`id_job` = :id;
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if($row = $stmt->fetch())
	    {
	        return Batch::withID($db, $row["batchId"]);
	    }
	    else
	    {
	        return null;
	    }
	    
	    return $this;
	}
	
	/**
	 * Set the id of this Job.
	 *
	 * @param int $id The id of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Job This Job (for method chaining)
	 */
	public function setId(?int $id = null) : Job
	{
	    $this->_id = $id;
	    return $this;
	}
	
	/**
	 * Set the name of this Job.
	 *
	 * @param int $name The name of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Job This Job (for method chaining)
	 */
	public function setName(?string $name = null) : Job
	{
	    $this->_name = $name;
	    return $this;
	}
	
	/**
	 * Set the delivery date of this Job.
	 *
	 * @param int $deliveryDate The delivery date of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Job This Job (for method chaining)
	 */
	public function setDeliveryDate(?string $deliveryDate = null) : Job
	{
	    $this->_deliveryDate = $deliveryDate;
	    return $this;
	}
	
	/**
	 * Set the status of this Job.
	 *
	 * @param int $status The status of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Job This Job (for method chaining)
	 */
	public function setStatus(?string $status = null) : Job
	{
	    $this->_status = $status;
	    return $this;
	}
	
	/**
	 * Set the last modification date timestamp of this Job.
	 *
	 * @param string $timestamp The last modification date timestamp of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Job This Job (for method chaining)
	 */
	private function setTimestamp(?string $timestamp = null) : Job
	{
	    $this->_timestamp = $timestamp;
	    return $this;
	}
	
	/**
	 * Set the array of JobType of this Job.
	 *
	 * @param JobType[] $parts The array of JobType of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Job This Job (for method chaining)
	 */
	public function setJobTypes(array $jobTypes = array()) : Job
	{
	    $this->_jobTypes = $jobTypes;
	    return $this;
	}
	
	/**
	 * Get the id of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of this Job
	 */
	public function getId() : ?int
	{
	    return $this->_id;
	}
	
	/**
	 * Get the name of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The name of this Job
	 */
	public function getName() : ?string
	{
	    return $this->_name;
	}
	
	/**
	 * Get the delivery date of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The delivery date of this Job
	 */
	public function getDeliveryDate() : ?string
	{
	    return $this->_deliveryDate;
	}
	
	/**
	 * Get the timestamp of the last modification date of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The timestamp of the last modification date of this Job
	 */
	public function getTimestamp() : ?string
	{
	    return $this->_timestamp;
	}
	
	/**
	 * Get the status of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The status of this Job
	 */
	public function getStatus() : ?string
	{
	    return $this->_status;
	}
	
	/**
	 * Get the array of JobType of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return JobType[] The array of JobType of this Job
	 */
	public function getJobTypes() :array
	{
	    return $this->_jobTypes;
	}
	
	/**
	 * Get a JSON compatible representation of this object.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array This object in a JSON compatible format
	 */
	public function jsonSerialize() : ?array
	{
	    return get_object_vars($this);
	}
}
?>