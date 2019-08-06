<?php
    /**
     * \name		JobTypeParameter
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-05-07
     *
     * \brief 		Modèle de JobTypeParameter
     * \details 	Modèle de JobTypeParameter
     */
    
    require_once __DIR__ . "/../../../parametres/parameter/parameter.php";
    
    class JobTypeParameter extends \Parameter implements \JsonSerializable
    {
        private $_jobTypeId;
        
        /**
         * Main constructor
         *
         * @param int $jobTypeId The id of the JobType object to which this JobTypeParameter belongs
         * @param string $key The key of the JobTypeParameter
         * @param string $value The value of the JobTypeParameter
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return JobTypeParameter This JObTypeParameter
         */
        public function __construct(?int $jobTypeId = null, string $key, ?string $value = null)
        {
            parent::__construct($key);
            $this->setJobTypeId($jobTypeId);
            $this->setValue($value);
        }
        
        /**
         * Constructor that retrieves an instance from the database
         *
         * @param FabPlanConnection $db The database from which the record must be retrieved
         * @param int $jobTypeId The id of the JobType to which  this JobTypeParameter belongs
         * @param string $key The key of the current JobTypeParameter
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return JobtypeParameter This JobTypeParameter
         */
        public static function withID(\FabplanConnection $db, int $jobTypeId, string $key) : ?\JobTypeParameter
        {
            $stmt = $db->getConnection()->prepare(
                "SELECT `jtp`.`param_value` AS `value` FROM `job_type_params` AS `jtp`
                WHERE `jtp`.`job_type_id` = :job_type_id AND `jtp`.`param_key` = :key;"
            );
            $stmt->bindValue(':job_type_id', $jobTypeId, PDO::PARAM_INT);
            $stmt->bindValue(':key', $key, PDO::PARAM_STR);
            $stmt->execute();
            
            $instance = null;
            if($row = $stmt->fetch())
            {
                $instance = new self($jobTypeId, $key, $row["value"]);
            }
            
            return $instance;
        }
        
        /**
         * Save the JobTypeParameter object in the database
         *
         * @param FabPlanConnection $db The database in which the record must be saved
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return JobTypeParameter This JobtypeParameter (for method chaining)
         */
        public function save(\FabPlanConnection $db) : \JobTypeParameter
        {
            if(self::withID($db, $this->getJobTypeId(), $this->getKey()) === null)
            {
                $this->insert($db);
            }
            else
            {
                if($this->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
                {
                    throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
                }
                else 
                {
                    $this->update($db);
                }
            }
            
            return $this;
        }
        
        /**
         * Insert the JobTypeParameter object in the database
         *
         * @param FabPlanConnection $db The database from which the record must be inserted
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return JobTypeParameter This JobTypeParameter (for method chaining)
         */
        private function insert(\FabPlanConnection $db) : \JobTypeParameter
        {
            $stmt = $db->getConnection()->prepare("
                INSERT INTO `job_type_params` (`job_type_id`, `param_key`, `param_value`)
                VALUES (:job_type_id, :key, :value)
            ");
            $stmt->bindValue(':job_type_id', $this->getJobTypeId(), PDO::PARAM_INT);
            $stmt->bindValue(':key', $this->getKey(), PDO::PARAM_STR);
            $stmt->bindValue(':value', $this->getValue(), PDO::PARAM_STR);
            $success = $stmt->execute();
            
            return $this;
        }
        
        /**
         * Update the JobTypeParameter object in the database
         *
         * @param FabPlanConnection $db The database from which the record must be updated
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return JobTypeParameter This JobTypeParameter (for method chaining)
         */
        private function update(\FabPlanConnection $db) : \JobTypeParameter
        {
            $stmt = $db->getConnection()->prepare("
                UPDATE `job_type_params`
                SET `param_value` = :value
                WHERE `jobType_id` = :jobTypeId AND `param_key` = :key;
            ");
            $stmt->bindValue(':jobTypeId', $this->getJobTypeId(), PDO::PARAM_INT);
            $stmt->bindValue(':key', $this->getKey(), PDO::PARAM_STR);
            $stmt->bindValue(':value', $this->getValue(), PDO::PARAM_STR);
            $success = $stmt->execute();
            
            return $this;
        }
        
        /**
         * Delete the JobTypeParameter object from the database
         *
         * @param FabPlanConnection $db The database from which the record must be deleted
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return JobTypeParameter This JobTypeParameter (for method chaining)
         */
        public function delete(\FabPlanConnection $db) : \JobTypeParameter
        {
            $stmt = $db->getConnection()->prepare("
                DELETE FROM `job_type_params`
                WHERE `job_type_id` = :jobTypeId AND `param_key` = :key;
            ");
            $stmt->bindValue(':jobTypeId', $this->getJobTypeId(), PDO::PARAM_INT);
            $stmt->bindValue(':key', $this->getKey(), PDO::PARAM_STR);
            $stmt->execute();
            
            return $this;
        }
        
        /**
         * Set the JobType id of this JobTypeParameter.
         *
         * @param int $jobTypeId The id of the JobType to which this jobTypeParameter is related.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return JobTypeParameter This JobTypeParameter (for method chaining)
         */
        public function setJobTypeId(?int $jobTypeId) : \JobTypeParameter
        {
            $this->_jobTypeId = $jobTypeId;
            return $this;
        }
        
        /**
         * Set the value of the JobTypeParameter
         *
         * @param string $value The new value of the JobTypeParameter
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return JobTypeParameter This JobTypeParameter (for method chaining)
         */
        public function setValue(?string $value) : JobTypeParameter
        {
            $this->_value = $value;
            return $this;
        }
        
        /**
         * Get the JobType id of this JobTypeParameter.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return int The JobType id of this JobTypeParameter
         */
        public function getJobTypeId() : int
        {
            return $this->_jobTypeId;
        }
        
        /**
         * Get the value of the JobTypeParameter
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The JobTypeParameter's value
         */
        public function getValue() : ?string
        {
            return $this->_value;
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
    }
?>