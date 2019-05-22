<?php

include_once __DIR__ . "/carrousel.php";
include_once __DIR__ . "/../../job/model/job.php";
include_once __DIR__ . "/../../../lib/mpr/mprExpressionEvaluator.php";

/**
* \name		Batch
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-01-26
*
* \brief 		Modèle de la table Batch
* \details 		Modèle de la table Batch
*/

class Batch implements JsonSerializable
{
	private $_id;
	private $_materialId;
	private $_boardSize;
	private $_name;
	private $_start;
	private $_end;
	private $_fullDay;
	private $_comments;
	private $_status;
	private $_mprStatus;
	private $_carrousel;
	private $_timestamp;
	private $_jobs;	// Array de Job
	private $__database_connection_locking_read_type = \MYSQLDatabaseLockingReadTypes::NONE;
	
	/**
	 * Batch constructor
	 *
	 * @param int $id The id of the Batch in the database
	 * @param int $materialId The id of the material associated to this Batch
	 * @param string $boardSize The size of the boards in this batch
	 * @param string $name The name of this Batch
	 * @param string $start The timestamp of the planned start of machining time of the batch
	 * @param string $end The timestamp of the planned end of machining of the batch
	 * @param string $fullDay "Y" if the batch is planned to take longer than a day to machine, "N" otherwise
	 * @param string $comments Some comments concerning this Batch
	 * @param string $status The status of this Batch 
	 *     ("E" => Planned, "X" => In execution, "P" => Urgent, "N" => Not delivered, T => Completed)
	 * @param string $mprStatus The status of the machining files of this Batch 
	 *     ("A" => Waiting, "N" => Not downloaded to CutQueue, "P" => Processing, "G" => Ready)
	 * @param Carrousel $carrousel A valid carrousel initializer (see Carrousel.php)
	 * @param string $timestamp A timestamp of the last modification date of this Batch
	 * @param Job array $jobs An array of jobs that belong to this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch
	 */ 
	public function __construct(?int $id = null, ?int $materialId = null, ?string $boardSize = null, ?string $name = null, 
	    ?string $start = null, ?string $end = null, ?string $fullDay = null, ?string $comments = null, ?string $status = null, 
	    ?string $mprStatus = null, $carrousel = null, ?string $timestamp = null, array $jobs = array())
	{
		$this->setId($id);
		$this->setMaterialId($materialId);
		$this->setBoardSize($boardSize);
		$this->setName($name);
		$this->setStart($start);
		$this->setEnd($end);
		$this->setFullDay($fullDay);
		$this->setComments($comments);
		$this->setStatus($status);
		$this->setMprStatus($mprStatus);
		$this->setTimestamp($timestamp);
		$this->setJobs($jobs);
		$this->setCarrousel($carrousel);
	}


	/**
	 * Batch constructor using ID of existing record
	 *
	 * @param FabPlanConnection $db The database in which the record exists
	 * @param int $id The id of the record in the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch The Batch associated to the specified ID in the specified database
	 */
	public static function withID(\FabPlanConnection $db, ?int $id, int $databaseConnectionLockingReadType = 0) : ?\Batch
	{
	    // Récupérer le test
	    $stmt = $db->getConnection()->prepare(
            "SELECT `b`.`id_batch` AS `id`, `b`.`materiel_id` AS `materialId`, `b`.`panneaux` AS `boardSize`, 
                `b`.`nom_batch` AS `name`, `b`.`date_debut` AS `startDate`, `b`.`date_fin` AS `endDate`, 
                `b`.`jour_complet` AS `fullDay`, `b`.`commentaire` AS `comments`, `b`.`etat` AS `status`, 
                `b`.`etat_mpr` AS `mprStatus`, `b`.`carrousel` AS `carrousel`, `b`.`estampille` AS `timestamp`
            FROM `fabplan`.`batch` AS `b` 
            WHERE `b`.`id_batch` = :id " . 
	        (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	// Récupération de l'instance de Batch
	    {
	        $instance = new self(
	            $row["id"], $row["materialId"], $row["boardSize"], $row["name"], $row["startDate"], $row["endDate"], 
	            $row["fullDay"], $row["comments"], $row["status"], $row["mprStatus"], $row["carrousel"], $row["timestamp"]
	        );
	    }
	    else
	    {
	        return null;
	    }
	    
	    //Récupérer les Jobs
	    $stmt = $db->getConnection()->prepare(
            "SELECT `bj`.`job_id` AS `jobId` FROM `fabplan`.`batch_job` AS `bj` 
            WHERE `bj`.`batch_id` = :batchId " . 
            (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':batchId', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    while($row = $stmt->fetch())	// Récupération de l'instance Job
	    {
	        $instance->addJob(Job::withID($db, $row["jobId"]));
	    }
	    
	    $instance->setDatabaseConnectionLockingReadType($databaseConnectionLockingReadType);
	    return $instance;
	}
	
	/**
	 * Get the id of this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of this Batch
	 */ 
	public function getId() : ?int
	{
	    return $this->_id;
	}
	
	/**
	 * Get the id of the Material associated to this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of thie Material associated to this Batch
	 */ 
	public function getMaterialId() : ?int
	{
	    return $this->_materialId;
	}
	
	/**
	 * Get the size of the boards (as a string) to be used for this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The board size for this Batch
	 */ 
	public function getBoardSize() : ?string
	{
	    return $this->_boardSize;
	}
	
	/**
	 * Get the name of this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The name of this Batch
	 */ 
	public function getName() : ?string
	{
	    return $this->_name;
	}
	
	/**
	 * Get the start date of this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The start date of this Batch
	 */ 
	public function getStart() : ?string
	{
	    return $this->_start;
	}
	
	/**
	 * Get the end date of this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The end date of this Batch
	 */
	public function getEnd() : ?string
	{
	    return $this->_end;
	}
	
	/**
	 * Get a boolean that indicates if this Batch is going to take a day or more.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string A boolean with value "Y" or "N" that indicates if this Batch is going to take a day or more
	 */
	public function getFullDay() : ?string
	{
	    return $this->_fullDay;
	}
	
	/**
	 * Get comments for this Batch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The comments of this Batch
	 */
	public function getComments() : ?string
	{
	    return $this->_comments;
	}
	
	/**
	 * Get status of this Batch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The status of this Batch
	 */
	public function getStatus() : ?string
	{
	    return $this->_status;
	}
	
	/**
	 * Get status of the machining programs of this Batch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The status of the machining programs of this Batch
	 */
	public function getMprStatus() : ?string
	{
	    return $this->_mprStatus;
	}
	
	/**
	 * Get the suggested configuration of the carrousel for this Batch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The Carrousel for this Batch
	 */
	public function getCarrousel() : ?Carrousel
	{
	    return $this->_carrousel;
	}
	
	/**
	 * Get the timestamp of the last modification date of this Batch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The timestamp of the last modification date of this Batch
	 */
	public function getTimestamp() : ?string
	{
	    return $this->_timestamp;
	}
	
	/**
	 * Get the array of Job belonging to this Batch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array The array of Job belonging to this Batch
	 */
	public function getJobs() : ?array
	{
	    return $this->_jobs;
	}
	
	/**
	 * Set the id of this Batch.
	 * @param int $id The id of this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setId(?int $id) : Batch
	{
	    $this->_id = $id;
	    return $this;
	}
	
	/**
	* Set the id of the material associated to this Batch.
	* @param int $id The id of the Material for this Batch
	*
	* @throws
	* @author Marc-Olivier Bazin-Maurice
	* @return Batch This Batch
	*/
	public function setMaterialId(?int $materialId) : Batch
	{
	    $this->_materialId = $materialId;
	    return $this;
	}
	
	/**
	 * Set the board size for this Batch.
	 * @param string $boardSize The board size of this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setBoardSize(?string $boardSize) : Batch
	{
	    $this->_boardSize = $boardSize;
	    return $this;
	}
	
	/**
	 * Set the name of this Batch.
	 * @param string $name The name of this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setName(?string $name) : Batch
	{
	    $this->_name = $name;
	    return $this;
	}
	
	/**
	 * Set the start date of this Batch.
	 * @param string $start The start date of this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setStart(?string $start) : Batch
	{
	    $this->_start = $start;
	    return $this;
	}
	
	/**
	 * Set the end date of this Batch.
	 * @param string $end The end date of this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setEnd(?string $end) : Batch
	{
	    $this->_end = $end;
	    return $this;
	}
	
	/**
	 * Set the boolean string that indicates if this Batch is going to take a day or more.
	 * @param string $fullDay A boolean string worth either "Y" or "N" that indicates if the machining of the batch is going to take 
	 *     longer than a day
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setFullDay(?string $fullDay) : Batch
	{
	    $this->_fullDay = $fullDay;
	    return $this;
	}
	
	/**
	 * Set the comments of this Batch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setComments(?string $comments) : Batch
	{
	    $this->_comments = $comments;
	    return $this;
	}
	
	/**
	 * Set the status of this Batch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setStatus(?string $status) : Batch
	{
	    $this->_status = $status;
	    return $this;
	}
	
	/**
	 * Set the status of the machining programs for this Batch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setMprStatus(?string $mprStatus) : Batch
	{
	    $this->_mprStatus = $mprStatus;
	    return $this;
	}
	
	/**
	 * Set the suggested configuration of the carrousel for this Batch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setCarrousel($carrousel = null) : Batch
	{
	    if($carrousel instanceof Carrousel)
	    {
	        $this->_carrousel = $carrousel;
	    }
	    elseif(is_array($carrousel))
	    {
	        $this->_carrousel = Carrousel::fromArray($carrousel);
	    }
	    elseif(is_string($carrousel))
	    {
	        $this->_carrousel = Carrousel::fromCsv($carrousel);
	    }
	    else 
	    {
	        $this->updateCarrousel();
	    }
	    
	    return $this;
	}
	
	/**
	 * Set the array of Job belonging to this Batch.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setTimestamp(?string $timestamp) : Batch
	{
	    $this->_timestamp = $timestamp;
	    return $this;
	}
	
	/**
	 * Set the array of Job belonging to this Batch.
	 * @param array[Job] $jobs The array of Job to assign to this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function setJobs(array $jobs) : Batch
	{
	    $this->_jobs = $jobs;
	    return $this;
	}
	
	/**
	 * Adds a job to this Batch
	 * @param \Job $job The Job to add to this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Batch This Batch
	 */
	public function addJob(\Job $job) : \Batch
	{
	    foreach($this->getJobs() as $includedJob)
	    {
	        if($job->getId() === $includedJob->getId())
	        {
	            return $this;
	        }
	    }
	    
	    array_push($this->_jobs, $job);
	    return $this;
	}
	
	/**
	 * Removes a job from this Batch
	 * @param int $id The id of the job to remove from this Batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch
	 */
	public function removeJob(int $id) : Batch
	{
	    foreach($this->_jobs as $index => $job)
	    {
            if($job->getId === $id)
            {
                unset($this->jobs[$index]);
            }
	    }
	    return $this;
	}
	
	/**
	 * Save the Batch object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be saved
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch (for method chaining)
	 */
	public function save(FabPlanConnection $db) : Batch
	{
	    if($this->getId() === null)
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
	            $this->update($db);
	        }
	    }
	    
	    // Récupération de l'estampille à jour
	    $this->setTimestamp($this->getTimestampFromDatabase($db));
	    return $this;
	}
	
	/**
	 * Insert the Batch object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be inserted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch (for method chaining)
	 */
	private function insert(FabPlanConnection $db) : Batch
	{
	    // Création d'un type de test
	    $stmt = $db->getConnection()->prepare("
            INSERT INTO `fabplan`.`batch` (`materiel_id`, `panneaux`, `nom_batch`, `date_debut`, `date_fin`, `jour_complet`, 
                `commentaire`, `etat`, `etat_mpr`, `carrousel`) 
            VALUES (:materialId, :boardSize, :name, :start, :end, :fullDay, :comments, :status, :mprStatus, :carrousel);
        ");
	    $stmt->bindValue(':materialId', $this->getMaterialId(), PDO::PARAM_INT);
	    $stmt->bindValue(":boardSize", $this->getBoardSize(), PDO::PARAM_STR);
	    $stmt->bindValue(':name', $this->getName(), PDO::PARAM_STR);
	    $stmt->bindValue(':start', $this->getStart(), PDO::PARAM_STR);
	    $stmt->bindValue(':end', $this->getEnd(), PDO::PARAM_STR);
	    $stmt->bindValue(':fullDay', $this->getFullDay(), PDO::PARAM_STR);
	    $stmt->bindValue(':comments', $this->getComments(), PDO::PARAM_STR);
	    $stmt->bindValue(':status', $this->getStatus(), PDO::PARAM_STR);
	    $stmt->bindValue(':mprStatus', $this->getMprStatus(), PDO::PARAM_STR);
	    $stmt->bindValue(':carrousel', $this->getCarrousel()->toCsv(), PDO::PARAM_STR);
	    $stmt->execute();
	    $this->setId(intval($db->getConnection()->lastInsertId()));
	    
	    //Mettre à jour les liens entre les jobs et la batch
	    $this->unlinkAllJobs($db);
	    foreach($this->getJobs() as $job)
	    {
	        $this->linkJob($job, $db);
	    }
	    
	    return $this;
	}
	
	/**
	 * Update the Batch object in the database
	 *
	 * @param \FabPlanConnection $db The database in which the record must be updated
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Batch This Batch (for method chaining)
	 */
	private function update(\FabPlanConnection $db) : \Batch
	{
	    // Mise à jour d'un Batch
	    $stmt = $db->getConnection()->prepare("
            UPDATE `fabplan`.`batch` AS `b`
            SET `materiel_id` = :materialId, `panneaux` = :boardSize, `nom_batch` = :name, `date_debut` = :start, 
                `date_fin` = :end, `jour_complet` = :fullDay, `commentaire` = :comments, `etat` = :status, 
                `etat_mpr` = :mprStatus, `carrousel` = :carrousel
            WHERE `id_batch` = :id;
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->bindValue(':materialId', $this->getMaterialId(), PDO::PARAM_INT);
	    $stmt->bindValue(":boardSize", $this->getBoardSize(), PDO::PARAM_STR);
	    $stmt->bindValue(':name', $this->getName(), PDO::PARAM_STR);
	    $stmt->bindValue(':start', $this->getStart(), PDO::PARAM_STR);
	    $stmt->bindValue(':end', $this->getEnd(), PDO::PARAM_STR);
	    $stmt->bindValue(':fullDay', $this->getFullDay(), PDO::PARAM_STR);
	    $stmt->bindValue(':comments', $this->getComments(), PDO::PARAM_STR);
	    $stmt->bindValue(':status', $this->getStatus(), PDO::PARAM_STR);
	    $stmt->bindValue(':mprStatus', $this->getMprStatus(), PDO::PARAM_STR);
	    $stmt->bindValue(':carrousel', $this->getCarrousel()->toCsv(), PDO::PARAM_STR);
	    $stmt->execute();
	    
	    //Mettre à jour les liens entre les jobs et la batch
	    $this->unlinkAllJobs($db);
	    foreach($this->getJobs() as $job)
	    {
	        $this->linkJob($job, $db);
	    }
	    
	    return $this;
	}
	
	/**
	 * Delete the Batch object from the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch (for method chaining)
	 */
	public function delete(FabPlanConnection $db) : Batch
	{
	    if($this->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
	    {
	        throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
	    }
	    else
	    {
    	    foreach($this->getJobs() as $job)
    	    {
    	        $this->unlinkJob($job, $db);
    	    }
    	    
    	    $stmt = $db->getConnection()->prepare("DELETE FROM `fabplan`.`batch` WHERE `id_batch` = :id;");
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
            SELECT `b`.`estampille` FROM `fabplan`.`batch` AS `b` WHERE `b`.`id_batch` = :id;
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
	 * Unlinks all jobs from this Batch
	 *
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch (for method chaining)
	 */
	private function unlinkAllJobs(FabPlanConnection $db) : Batch
	{
	    $stmt = $db->getConnection()->prepare("DELETE FROM `fabplan`.`batch_job` WHERE `batch_id` = :batchId;");
	    $stmt->bindValue(':batchId', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    return $this;
	}
	
	/**
	 * Unlinks a job from this Batch
	 *
	 * @param Job $job The job to unlink
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch (for method chaining)
	 */
	private function unlinkJob(Job $job, FabPlanConnection $db) : Batch
	{
	    $stmt = $db->getConnection()->prepare("DELETE FROM `fabplan`.`batch_job` WHERE `batch_id` = :batchId AND `job_id`= :jobId;");
	    $stmt->bindValue(':batchId', $this->getId(), PDO::PARAM_INT);
	    $stmt->bindValue(':jobId', $job->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    return $this;
	}
	
	/**
	 * Links a job to this Batch
	 *
	 * @param Job $job The job to link
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch (for method chaining)
	 */
	private function linkJob(\Job $job, \FabPlanConnection $db) : \Batch
	{
	    $stmt = $db->getConnection()->prepare("INSERT INTO `fabplan`.`batch_job`(`batch_id`, `job_id`) VALUES(:batchId, :jobId);");
	    $stmt->bindValue(':batchId', $this->getId(), PDO::PARAM_INT);
	    $stmt->bindValue(':jobId', $job->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    return $this;
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
	 * Fills
	 *
	 * @param Job $job The job to link
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Batch This Batch (for method chaining)
	 */
	public function updateCarrousel() : Batch
	{
	    
	    $this->_carrousel = new Carrousel();
	    
	    if(!empty($this->getJobs()))
	    {
    	    foreach ($this->getJobs() as $job)
    	    {
    	        if(!empty($job->getJobTypes()))
    	        {
        	        foreach($job->getJobTypes() as $jobType)
        	        {
        	            $parameters = $jobType->getParametersAsKeyValuePairs();
        	            $parameters = array_merge($parameters, $this->_carrousel->getSymbolicToolNamesArray());
        	            $NpasInt = \MprExpression\Evaluator::evaluate($parameters["NpasInt"] ?? 0, null, $parameters);
        	            for($i = 1; $i <= $NpasInt; $i++)
        	            {
        	                $tool = \MprExpression\Evaluator::evaluate($parameters["T_INT" . $i] ?? 0, null, $parameters);
        	                if(!$this->_carrousel->toolExists($tool))
        	                {
        	                    $this->_carrousel->addTool($tool);
        	                }
        	            }
        	            
        	            $NpasBat = \MprExpression\Evaluator::evaluate($parameters["NpasBat"] ?? 0, null, $parameters);
        	            for($i = 1; $i <= $NpasBat; $i++)
        	            {
        	                $tool = \MprExpression\Evaluator::evaluate($parameters["T_BAT" . $i] ?? 0, null, $parameters);
        	                if(!$this->_carrousel->toolExists($tool))
        	                {
        	                    $this->_carrousel->addTool($tool);
        	                }
        	            }
        	            
        	            $NpasInt2 = \MprExpression\Evaluator::evaluate($parameters["NpasInt2"] ?? 0, null, $parameters);
        	            for($i = 1; $i <= $NpasInt2; $i++)
        	            {
        	                $tool = \MprExpression\Evaluator::evaluate($parameters["T_INT2" . $i] ?? 0, null, $parameters);
        	                if(!$this->_carrousel->toolExists($tool))
        	                {
        	                    $this->_carrousel->addTool($tool);
        	                }
        	            }
        	            
        	            $NpasTh = \MprExpression\Evaluator::evaluate($parameters["NpasTh"] ?? 0, null, $parameters);
        	            for($i = 1; $i <= $NpasTh; $i++)
        	            {
        	                $tool = \MprExpression\Evaluator::evaluate($parameters["T_Th" . $i] ?? 0, null, $parameters);
        	                if(!$this->_carrousel->toolExists($tool))
        	                {
        	                    $this->_carrousel->addTool($tool);
        	                }
        	            }
        	            
        	            $Act_PK_C = \MprExpression\Evaluator::evaluate($parameters["Act_PK_C"] ?? 0, null, $parameters);
        	            $tool = \MprExpression\Evaluator::evaluate("_T_PCKT", null, $parameters);
        	            if(!$this->_carrousel->toolExists($tool))
        	            {
        	                $this->_carrousel->addTool($tool);
        	            }
        	            
        	            $A_Clean = \MprExpression\Evaluator::evaluate($parameters["A_Clean"] ?? 0, null, $parameters);
        	            $tool = \MprExpression\Evaluator::evaluate($parameters["T_CLEAN"] ?? 0, null, $parameters);
        	            if(!$this->_carrousel->toolExists($tool))
        	            {
        	                $this->_carrousel->addTool($tool);
        	            }
        	            
        	            $shape = \MprExpression\Evaluator::evaluate($parameters["shape"] ?? 0, null, $parameters);
        	            if($shape <> 0)
        	            {
        	                if(!$this->_carrousel->toolExists("167"))
        	                {
        	                   $this->_carrousel->addTool("167");
        	                }
        	                if(!$this->_carrousel->toolExists("168"))
        	                {
        	                    $this->_carrousel->addTool("168");
        	                }
        	            }
        	            
        	            $tool = \MprExpression\Evaluator::evaluate("_T_CUT", null, $parameters);
        	            if(!$this->_carrousel->toolExists($tool))
        	            {
        	               $this->_carrousel->addTool($tool);
        	            }
        	        }
    	        }
    	    }
	    }
	    
	    return $this;
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
	private function setDatabaseConnectionLockingReadType(int $databaseConnectionLockingReadType) : \Batch
	{
	    $this->__database_connection_locking_read_type = $databaseConnectionLockingReadType;
	    return $this;
	}
}
?>