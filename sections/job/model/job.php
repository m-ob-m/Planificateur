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

require_once __DIR__ . '/job_type.php';	// Classe d'un type de job
require_once __DIR__ . '/job_type_porte.php';	// Classe d'une porte de type
require_once __DIR__ . "/../../batch/model/batch.php"; // Classe d'une batch

class Job implements \JsonSerializable
{	
	private $_id;
	private $_name;
	private $_deliveryDate;
	private $_customerPurchaseOrderNumber;
	private $_status;
	private $_timestamp;
	private $_jobTypes;	// Array de job types
	private $__database_connection_locking_read_type = \MYSQLDatabaseLockingReadTypes::NONE;
	
	/**
	 * Job constructor
	 *
	 * @param int $id The id of the Job in the database
	 * @param string $name The name of the Job (must be unique)
	 * @param string $deliveryDate The delivery date of the job
	 * @param string $customerPurchaseOrderNumber The customer's purchase order number for this Job
	 * @param string $status The status of the Job ("E" = Entered, "G" = Generated, "T" = Done)
	 * @param string $timestamp A timestamp of the last modification applied to this Job (leave null)
	 * @param string $parameters An array containing the JobTypeParameters objects associated with this JobType.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Job
	 */
	public function __construct(?int $id = null, ?string $name = null, ?string $deliveryDate = null, 
		?string $customerPurchaseOrderNumber  = null, ?string $status = null, ?string $timestamp = null, array $jobTypes = array())
	{
	    $this->setId($id);
	    $this->setName($name);
		$this->setDeliveryDate($deliveryDate);
		$this->setCustomerPurchaseOrderNumber($customerPurchaseOrderNumber);
	    $this->setStatus($status);
	    $this->setTimestamp($timestamp);
	    $this->setJobtypes($jobTypes);
	}
	
	/**
	 * Job constructor using ID of existing record
	 *
	 * @param FabPlanConnection $db The database in which the record exists
	 * @param int $id The id of the record in the database
	 * @param int $databaseConnectionLockingReadType The type of lock to apply to the selected record 
	 *            (see \MYSQLDatabaseLockingReadTypes)
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Job The Job associated to the specified ID in the specified database
	 */
	static function withID(FabPlanConnection $db, int $id, int $databaseConnectionLockingReadType = 0) : ?\Job
	{
	    // Récupérer le test
	    $stmt = $db->getConnection()->prepare(
            "SELECT `j`.`numero` AS `name`, `j`.`date_livraison` AS `deliveryDate`, `j`.`customerPO` AS `customerPO`, 
				`j`.`etat` AS `status`, `j`.`estampille` AS `timestamp`
            FROM `job` AS `j` WHERE `j`.`id_job` = :id " . 
	        (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())
	    {
	        $instance = new self($id, $row["name"], $row["deliveryDate"], $row["customerPO"], $row["status"], $row["timestamp"]);
	    }
	    else
	    {
	        return null;
	    }
	    
	    //Récupérer les paramètres
	    $stmt = $db->getConnection()->prepare(
            "SELECT `jt`.`id_job_type` AS `id` 
            FROM `job_type` AS `jt` 
            WHERE `jt`.`job_id` = :id 
            ORDER BY `jt`.`id_job_type` ASC " . 
	        (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    while($row = $stmt->fetch())	// Récupération des paramètres
	    {
	        array_push($instance->_jobTypes, Jobtype::withID($db, $row["id"]));
	    }
	    
	    $instance->setDatabaseConnectionLockingReadType($databaseConnectionLockingReadType);
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
	 * @return \Job The Job associated to the specified name in the specified database
	 */
	static function withName(FabPlanConnection $db, string $name, int $databaseConnectionLockingReadType = 0) : ?\Job
	{
	    // Récupérer le Job
	    $stmt = $db->getConnection()->prepare("
			SELECT `j`.`id_job` AS `id`, `j`.`date_livraison` AS `deliveryDate`, `j`.`customerPO` AS `customerPO`, 
				`j`.`etat` AS `status`, `j`.`estampille` AS `timestamp`
            FROM `job` AS `j` WHERE `j`.`numero` = :name " . 
            (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':name', $name, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())
	    {
	        $instance = new self($row["id"], $name, $row["deliveryDate"], $row["customerPO"], $row["status"], $row["timestamp"]);
	    }
	    else
	    {
	        return null;
	    }
	    
	    //Récupérer les JobType
	    $stmt = $db->getConnection()->prepare(
            "SELECT `jt`.`id_job_type` AS `id` 
            FROM `job_type` AS `jt` 
            WHERE `jt`.`job_id` = :id 
            ORDER BY `jt`.`type_no` ASC, `jt`.`door_model_id` ASC " . 
	        (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':id', $instance->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    while($row = $stmt->fetch())	// Récupération des paramètres
	    {
	        array_push($instance->_jobTypes, \Jobtype::withID($db, $row["id"]));
	    }
	    
	    $instance->setDatabaseConnectionLockingReadType($databaseConnectionLockingReadType);
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
            SELECT `j`.* FROM `job` AS `j` WHERE `j`.`id_job` = :id LIMIT 1;
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if($stmt->fetch(PDO::FETCH_ASSOC) == null)
	    {
	        $this->insert($db);
	    }
	    else
	    {
	        $dbTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $this->getTimestampFromDatabase($db));
	        $localTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $this->getTimestamp());
	        if($this->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
	        {
	            throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
	        }
	        elseif($dbTimestamp > $localTimestamp)
	        {
	            throw new \Exception(
	                "The provided " . get_class($this) . " is outdated. The last modification date of the database entry is
                    \"{$dbTimestamp->format("Y-m-d H:i:s")}\" whereas the last modification date of the local copy is
                    \"{$localTimestamp->format("Y-m-d H:i:s")}\"."
	            );
	        }
	        else
	        {
	            $this->emptyInDatabase($db)->update($db);
	        }
	    }
	    
	    // Récupération de l'estampille à jour
	    $this->setTimestamp($this->getTimestampFromDatabase($db));
	    
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
            INSERT INTO `job` (`numero`, `date_livraison`, `customerPO`, `etat`)
            VALUES (:name, :deliveryDate, :customerPO, :status);
        ");
	    $stmt->bindValue(':name', $this->getName(), PDO::PARAM_STR);
		$stmt->bindValue(':deliveryDate', $this->getDeliveryDate(), PDO::PARAM_STR);
		$stmt->bindValue(':customerPO', $this->getCustomerPurchaseOrderNumber(), \PDO::PARAM_STR);
	    $stmt->bindValue(':status', $this->getStatus(), PDO::PARAM_STR);
	    $stmt->execute();
	    $this->setId(intval($db->getConnection()->lastInsertId()));
		
		/* @var \JobType $jobType */
	    foreach($this->getJobTypes() as $jobType)
	    {
	        $jobType->setJobId($this->getId())->save($db);
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
	 * @return \Job This Job (for method chaining)
	 */
	private function update(\FabPlanConnection $db) : \Job
	{
	    $stmt = $db->getConnection()->prepare("
            UPDATE `job`
            SET `numero` = :name, `date_livraison` = :deliveryDate, `customerPO` = :customerPO, `etat` = :status
            WHERE `id_job` = :id;
        ");
	    $stmt->bindValue(':id', $this->getId(), \PDO::PARAM_INT);
	    $stmt->bindValue(':name', $this->getName(), \PDO::PARAM_STR);
		$stmt->bindValue(':deliveryDate', $this->getDeliveryDate(), \PDO::PARAM_STR);
		$stmt->bindValue(':customerPO', $this->getCustomerPurchaseOrderNumber(), \PDO::PARAM_STR);
	    $stmt->bindValue(':status', $this->getStatus(), \PDO::PARAM_STR);
	    $stmt->execute();
	    
	    /* @var \JobType $jobType */
	    foreach($this->_jobTypes as $jobType)
	    {
	       $jobType->save($db);
	    }
	    
	    return $this;
	}
	
	/**
	 * Delete the JobType object from the database
	 *
	 * @param \FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \JobType This JobType (for method chaining)
	 */
	public function delete(\FabPlanConnection $db) : \Job
	{   
	    
	    if($this->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
	    {
	        throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
	    }
	    else
	    {
    	    $stmt = $db->getConnection()->prepare("DELETE FROM `job` WHERE `id_job` = :id;");
    	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
    	    $stmt->execute();
	    }
	    
	    return $this;
	}
	
	/**
	 * Gets the last modification date timestamp of the database instance of this object
	 *
	 * @param \FabPlanConnection $db The database from which the timestamp should be fetched.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The last modification date timestamp of the database instance of this object.
	 */
	public function getTimestampFromDatabase(\FabPlanConnection $db) : ?string
	{
	    $stmt= $db->getConnection()->prepare("
            SELECT `j`.`estampille` FROM `job` AS `j` WHERE `j`.`id_job` = :id;
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if($row = $stmt->fetch())
	    {
	        return $row["estampille"];
	    }
	    else
	    {
	        return null;
	    }
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
	private function emptyInDatabase(\FabPlanConnection $db) : \Job
	{
	    $stmt = $db->getConnection()->prepare("
            SELECT `jt`.`id_job_type` AS `jobTypeId` FROM `job_type` AS `jt` 
            WHERE `jt`.`job_id` = :id;
	    ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    while($row = $stmt->fetch())
	    {
	        \JobType::withID($db, $row["jobTypeId"], \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)->delete($db);
	    }
	    
	    return $this;
	}
	
	/**
	 * Gets the batch to which this job belongs
	 *
	 * @param FabPlanConnection $db The database in which the records must be found.
	 * @param int $databaseConnectionLockingReadType The type of lock to apply to the selected record 
	 *            (see \MYSQLDatabaseLockingReadTypes)
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return JobType This JobType (for method chaining)
	 */
	public function getParentBatch(\FabPlanConnection $db, int $databaseConnectionLockingReadType = 0) : ?\Batch
	{
	    $stmt = $db->getConnection()->prepare(
            "SELECT `b`.`id_batch` AS `batchId` 
            FROM `job` AS `j`
            INNER JOIN `batch_job` AS `bj` ON `bj`.`job_id` = `j`.`id_job`
            INNER JOIN `batch` AS `b` ON `b`.`id_batch` = `bj`.`batch_id`
            WHERE `j`.`id_job` = :id " . 
	        (new \MYSQLDatabaseLockingReadTypes($this->getDatabaseConnectionLockingReadType()))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if($row = $stmt->fetch())
	    {
	        return \Batch::withID($db, $row["batchId"], $databaseConnectionLockingReadType);
	    }
	    
	    return null;
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
	public function setId(?int $id = null) : \Job
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
	public function setName(?string $name = null) : \Job
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
	 * Set the customer's purchase order number of this Job.
	 *
	 * @param int $customerPurchaseOrderNumber The customer's purchase order number of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Job This Job (for method chaining)
	 */
	public function setCustomerPurchaseOrderNumber(?string $customerPurchaseOrderNumber = null) : \Job
	{
	    $this->_customerPurchaseOrderNumber = $customerPurchaseOrderNumber;
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
	 * @return string The timestamp of the last modification date of this Job
	 */
	public function getTimestamp() : ?string
	{
	    return $this->_timestamp;
	}
	
	/**
	 * Get the customer's purchase order number of this Job.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The customer's purchase order number of this Job
	 */
	public function getCustomerPurchaseOrderNumber() : ?string
	{
	    return $this->_customerPurchaseOrderNumber;
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
	
	/**
	 * Gets the database connection locking read type applied to this object.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The database connection locking read type applied to this object.
	 */
	public function getDatabaseConnectionLockingReadType() : int
	{
	    return $this->__database_connection_locking_read_type;
	}
	
	/**
	 * Sets the database connection locking read type applied to this object.
	 * @param int $databaseConnectionLockingReadType The new database connection locking read type applied to this object.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \JobType This JobType.
	 */
	private function setDatabaseConnectionLockingReadType(int $databaseConnectionLockingReadType) : \Job
	{
	    $this->__database_connection_locking_read_type = $databaseConnectionLockingReadType;
	    return $this;
	}
}
?>