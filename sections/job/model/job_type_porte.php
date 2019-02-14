<?php
/**
 * \name		JobTypePorte
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-05-07
 *
 * \brief 		Modèle de jobTypePorte
 * \details 	Modèle de jobTypePorte
 */

class JobTypePorte implements JsonSerializable
{
    private $_id;
    private $_jobTypeId;
    private $_quantityToProduce;
    private $_producedQuantity;
    private $_length;
    private $_width;
    private $_grain;
    private $_done;
    private $_timestamp;
    private $__database_connection_locking_read_type = \MYSQLDatabaseLockingReadTypes::NONE;
    
    /**
     * Main constructor
     *
     * @param int $jobTypePorteId The id of this JobTypePorte object in the database
     * @param int $jobTypeId The id of the JobType object to which this JobTypePorte belongs
     * @param int $quantityToProduce The quantity of this JobTypePorte to produce
     * @param int $producedQuantity The produced quantity of this JobTypePorte
     * @param float $length The length (or height) of this JobTypePorte
     * @param float $width The width of this JobTypePorte
     * @param string $grain A string worth "0" if this JobTypePorte has no grain direction 
     *      (I assume it is worth "H" or "V" if there is a grain direction)
     * @param string $done A boolean string worth "O" if all required parts for this JobTypePorte were produced and "N" otherwise
     * @param string $timestamp The timestamp of the last modification date of this JobtypePorte
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte
     */
    function __construct(?int $id = null, ?int $jobTypeId = null, ?int $quantityToProduce = null, ?int $producedQuantity = null, 
        ?float $length = null, ?float $width = null, ?string $grain = null, ?string $done = null, ?string $timestamp = null)
    {
        $this->setId($id);
        $this->setJobTypeId($jobTypeId);
        $this->setQuantityToProduce($quantityToProduce);
        $this->setProducedQuantity($producedQuantity);
        $this->setLength($length);
        $this->setWidth($width);
        $this->setGrain($grain);
        $this->setDone($done);
        $this->setTimestamp($timestamp);
    }
    
    /**
     * Constructor that retrieves an instance from the database
     *
     * @param FabPlanConnection $db The database from which the record must be retrieved
     * @param int $jobTypePorteId The id of the JobTypePorte
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte
     */
    static function withID(\FabplanConnection $db, int $id, int $databaseConnectionLockingReadType = 0) : ?\JobTypePorte
    {
        $stmt = $db->getConnection()->prepare(
            "SELECT `jtp`.`job_type_id` AS `jobTypeId`, `jtp`.`longueur` AS `length`, `jtp`.`largeur` AS `width`, 
                `jtp`.`quantite` AS `quantityToProduce`, `jtp`.`qte_produite` AS `producedQuantity`, `jtp`.`grain` AS `grain`, 
                `jtp`.`terminer` AS `done`, `jtp`.`estampille` AS `timestamp`
            FROM `fabplan`.`job_type_porte` AS `jtp`
            WHERE `jtp`.`id_job_type_porte` = :id " . 
            (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $instance = null;
        if($row = $stmt->fetch())
        {
            $instance = new self($id, $row["jobTypeId"], $row["quantityToProduce"], $row["producedQuantity"], 
                (float)$row["length"], (float)$row["width"], $row["grain"], $row["done"], $row["timestamp"]);
        }
        else
        {
            return null;    
        }
        
        $this->setDatabaseConnectionLockingReadType($databaseConnectionLockingReadType);
        return $instance;
    }
    
    /**
     * Save the JobTypePorte object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be saved
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    public function save(\FabPlanConnection $db) : \JobTypePorte
    {        
        if(self::withID($db, $this->getId()))
        {
            $this->insert($db);
        }
        else
        {
            $dbTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $this->getTimestampFromDatabase($db), "America/Montreal");
            $localTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $this->getTimestamp(), "America/Montreal");
            if($this->getDatabaseConnectionReadingLockType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
            {
                throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
            }
            elseif($databaseTimestamp > $localTimestamp)
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
     * Insert the JobTypePorte object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be inserted
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    private function insert(FabPlanConnection $db) : JobTypePorte
    {
        $stmt = $db->getConnection()->prepare("
            INSERT INTO `fabplan`.`job_type_porte` (`id_job_type_porte`, `job_type_id`, `quantite`, `qte_produite`, `longueur`, 
                `largeur`, `grain`, `terminer`)
            VALUES (:jobTypePorteId, :jobTypeId, :quantityToProduce, :producedQuantity, :length, :width, :grain, :done);
        ");
        $stmt->bindValue(':jobTypePorteId', $this->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':jobTypeId', $this->getJobTypeId(), PDO::PARAM_INT);
        $stmt->bindValue(':quantityToProduce', $this->getQuantityToProduce(), PDO::PARAM_INT);
        $stmt->bindValue(':producedQuantity', $this->getProducedQuantity(), PDO::PARAM_INT);
        $stmt->bindValue(':length', (string)$this->getLength(), PDO::PARAM_STR);
        $stmt->bindValue(':width', (string)$this->getWidth(), PDO::PARAM_STR);
        $stmt->bindValue(':grain', $this->getGrain(), PDO::PARAM_STR);
        $stmt->bindValue(':done', $this->getDone(), PDO::PARAM_STR);
        $success = $stmt->execute();
        $this->setId($db->getConnection()->lastInsertId());
        
        return $this;
    }
    
    /**
     * Update the JobTypePorte object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be updated
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    private function update(FabPlanConnection $db) : JobTypePorte
    {
        $stmt = $db->getConnection()->prepare("
            UPDATE `fabplan`.`job_type_porte`
            SET `job_type_id` = :jobTypeId, `quantite` = :quantityToProduce, `qte_produite` = :produceQuantity, 
                `longueur` = :length, `largeur` = :width, `grain`= :grain, `terminer` = :done
            WHERE `id_job_type_porte` = :id;
        ");
        $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
        $stmt->bindValue(':jobTypeId', $this->getJobTypeId(), PDO::PARAM_INT);
        $stmt->bindValue(':quantityToProduce', $this->getQuantityToProduce(), PDO::PARAM_INT);
        $stmt->bindValue(':producedQuantity', $this->getProducedQuantity(), PDO::PARAM_INT);
        $stmt->bindValue(':length', (string)$this->getLength(), PDO::PARAM_STR);
        $stmt->bindValue(':width', (string)$this->getWidth(), PDO::PARAM_STR);
        $stmt->bindValue(':grain', $this->getGrain(), PDO::PARAM_STR);
        $stmt->bindValue(':done', $this->getDone(), PDO::PARAM_STR);
        $success = $stmt->execute();
        
        return $this;
    }
    
    /**
     * Delete the JobTypePorte object from the database
     *
     * @param FabPlanConnection $db The database from which the record must be deleted
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    public function delete(FabPlanConnection $db) : JobTypePorte
    {
        $stmt = $db->getConnection()->prepare("
                DELETE FROM `fabplan`.`job_type_porte`
                WHERE `id_job_type_porte` = :id;
            ");
        $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
        $stmt->execute();
        
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
            SELECT `jtp`.`estampille` FROM `fabplan`.`job_type_porte` AS `jtp` WHERE `jtp`.`id_job_type_porte` = :id;
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
     * Set the id of this JobTypePorte.
     *
     * @param int $id The id of this jobTypePorte.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    public function setId(?int $id) : JobTypePorte
    {
        $this->_id = $id;
        return $this;
    }
    
    /**
     * Set the JobType id of this JobTypePorte.
     *
     * @param int $jobTypeId The id of the JobType to which this jobTypePorte is related.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    public function setJobTypeId(?int $jobTypeId) : JobTypePorte
    {
        $this->_jobTypeId = $jobTypeId;
        return $this;
    }
    
    /**
     * Set the Quantity to produce of this JobTypePorte.
     *
     * @param int $quantityToProduce The amount of times this JobTypePorte must be produced.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    public function setQuantityToProduce(?int $quantityToProduce) : JobTypePorte
    {
        $this->_quantityToProduce = $quantityToProduce;
        return $this;
    }
    
    /**
     * Set the produced quantity of this JobTypePorte
     *
     * @param int $producedQuantity The quantity of this JobTypePorte that has been produced yet
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    public function setProducedQuantity(?int $producedQuantity) : JobTypePorte
    {
        $this->_producedQuantity = $producedQuantity;
        return $this;
    }
    
    /**
     * Set the length of this JobTypePorte
     *
     * @param int $length The length of this JobTypePorte
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    public function setLength(?float $length) : JobTypePorte
    {
        $this->_length = $length;
        return $this;
    }
    
    /**
     * Set the width of this JobTypePorte
     *
     * @param int $width The width of this JobTypePorte
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    public function setWidth(?float $width) : JobTypePorte
    {
        $this->_width = $width;
        return $this;
    }
    
    /**
     * Set the grain of this JobTypePorte
     *
     * @param int $grain The grain of this JobTypePorte
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    public function setGrain(?string $grain) : JobTypePorte
    {
        $this->_grain = $grain;
        return $this;
    }
    
    /**
     * Set the done property of this JobTypePorte
     *
     * @param int $done The new value of the done property of this JobTypePorte
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    public function setDone(?string $done) : JobTypePorte
    {
        $this->_done = $done;
        return $this;
    }
    
    /**
     * Set the timestamp of the last modification date of this JobTypePorte
     *
     * @param string $timestamp The timestamp of the new modification date of this JobTypePorte
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return JobTypePorte This JobTypePorte (for method chaining)
     */
    public function setTimestamp(?string $timestamp) : JobTypePorte
    {
        $this->_timestamp = $timestamp;
        return $this;
    }
    
    /**
     * Get the id of this JobTypePorte.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The id of this JobTypePorte
     */
    public function getId() : int
    {
        return $this->_id;
    }
    
    /**
     * Get the id of the JobType to which this JobTypePorte belongs.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The id of the JobType to which this JobTypePorte belongs
     */
    public function getJobTypeId() : int
    {
        return $this->_jobTypeId;
    }
    
    /**
     * Get the quantity to produce of this JobTypePorte.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The quantity to produce of this JobTypePorte
     */
    public function getQuantityToProduce() : int
    {
        return $this->_quantityToProduce;
    }
    
    /**
     * Get the produced quantity of this JobTypePorte.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The produced quantity of this JobTypePorte
     */
    public function getProducedQuantity() : int
    {
        return $this->_producedQuantity;
    }
    
    /**
     * Get the length of this JobTypePorte.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return float The length of this JobTypePorte
     */
    public function getLength() : float
    {
        return $this->_length;
    }
    
    /**
     * Get the width of this JobTypePorte.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return float The width of this JobTypePorte
     */
    public function getWidth() : float
    {
        return $this->_width;
    }
    
    /**
     * Get the grain direction of this JobTypePorte.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The grain direction of this JobTypePorte
     */
    public function getGrain() : string
    {
        return $this->_grain;
    }
    
    /**
     * Get the done property of this JobTypePorte.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The done property of this JobTypePorte
     */
    public function getDone() : string
    {
        return $this->_done;
    }
    
    /**
     * Get the timestamp of the last modification date of this JobTypePorte.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The timestamp of the last modification date of this JobTypePorte
     */
    public function getTimestamp() : string
    {
        return $this->_timestamp;
    }
    
    /**
     * Get a JSON compatible representation of this object.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return array This object in a JSON compatible format
     */
    public function jsonSerialize()
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
    private function getDatabaseConnectionLockingReadType() : int
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
    private function setDatabaseConnectionLockingReadType(int $databaseConnectionLockingReadType) : \JobType
    {
        $this->__database_connection_locking_read_type = $databaseConnectionLockingReadType;
        return $this;
    }
}
?>