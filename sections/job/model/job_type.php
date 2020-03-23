<?php

/**
 * \name		JobType
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-05-07
 *
 * \brief 		Modele de la table job_type
 * \details 	Modele de la table job_type
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/varmodtypegen/model/modelTypeGeneric.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/model/model/model.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/type/model/type.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/job/model/jobTypeParameter.php";

class JobType extends \ModelTypeGeneric implements \JsonSerializable
{
    private $_id;
    private $_jobId;
    private $_mprFileName;
    private $_mprFileContents;
    private $_timestamp;
    private $_parts;
    private $__database_connection_locking_read_type = \MYSQLDatabaseLockingReadTypes::NONE;
    
    /**
     * JobType constructor
     *
     * @param int $id The id of the JobType in the database
     * @param int $jobId The id of the Job to which this JobType belongs
     * @param \Model $model The Model associated with this JobType (the one that was modified)
     * @param \Type $type The Type associated with this JobType (the one that was modified)
     * @param string $mprFileContents The contents of the .mpr file associated to this JobType if not using a generic file
     * @param int $genericId The id of the Generic associated to this JobType
     * @param string $timestamp A timestamp of the last modification applied to this JobType (leave null)
     * @param \JobTypeParameter[] $parameters An array containing the JobTypeParameters objects associated with this JobType.
     * @param \JobTypePorte[] $parts The array of JobTypePorte of this jobType.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType
     */
    public function __construct(?int $id = null, ?int $jobId = null, ?\Model $model = null, ?\Type $type = null,
        ?string $mprFilename = null, ?string $mprFileContents = null, ?string $timestamp = null, array $parameters = array(), 
        array $parts = array())
    {
        parent::__construct($model, $type, $parameters);
        $this->setId($id);
        $this->setJobId($jobId);
        $this->setMprFileName($mprFilename);
        $this->setMprFileContents($mprFileContents);
        $this->setTimestamp($timestamp);
        $this->setParts($parts);
    }
    
    /**
     * JobType constructor using ID of existing record
     *
     * @param \FabPlanConnection $db The database in which the record exists
     * @param int $id The id of the record in the database
     * @param int $databaseConnectionLockingReadType
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType The JobType associated to the specified ID in the specified database
     */
    static function withID(\FabPlanConnection $db, int $id, int $databaseConnectionLockingReadType = 0) : ?\JobType
    {
        // Récupérer le test
        $stmt = $db->getConnection()->prepare(
            "SELECT `jt`.`job_id` AS `jobId`, `jt`.`door_model_id` AS `modelId`, `jt`.`type_no` AS `typeNo`, 
                `jt`.`nom_fichier_mpr` AS `mprFileName`, `jt`.`contenu_fichier_mpr` AS `mprFileContents`, `jt`.`estampille` AS `timestamp`
            FROM `job_type` AS `jt` WHERE `jt`.`id_job_type` = :id " . 
            (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
        $stmt->bindValue(":id", $id, \PDO::PARAM_INT);
        $stmt->execute();
        
		$model = null;
        if ($row = $stmt->fetch())
        {
            $model = \Model::withID($db, $row["modelId"]);
            $type = \Type::withImportNo($db, $row["typeNo"]);
            
            $instance = new self(
                $id, $row["jobId"], $model, $type, $row["mprFileName"], $row["mprFileContents"], $row["timestamp"]
            );
        }
        else
        {
            return null;
        }
        
		if($model->getId() !== 2)
		{
			//Récupérer les paramètres
			$stmt = $db->getConnection()->prepare(
				"SELECT `gp`.`key` AS `key`, 
					IF (`jtp`.`value` IS NULL, `gp`.`value`, `jtp`.`value`) AS `value`,
					`gp`.`description` AS `description`
				FROM  `job_type` AS `jt`
				INNER JOIN `door_types` AS `dt` ON `dt`.`importNo` = `jt`.`type_no`
				INNER JOIN `generics` AS `g` ON `g`.`id` = `dt`.`generic_id`
				INNER JOIN LATERAL (
					SELECT `gp`.`id` AS `id`, 
						`gp`.`parameter_key` AS `key`, 
						`gp`.`parameter_value` AS `value`, 
						`gp`.`description` AS `description`
					FROM `generic_parameters` AS `gp` 
					WHERE `gp`.`generic_id` = `g`.`id`
				) AS `gp`
				LEFT JOIN LATERAL (
					SELECT `jtp`.`param_key` AS `key`, `jtp`.`param_value` AS `value` 
					FROM `job_type_params` AS `jtp` 
					WHERE `jt`.`id_job_type` = `jtp`.`job_type_id`
				) AS `jtp` ON `jtp`.`key` = `gp`.`key`
				WHERE `jt`.`id_job_type` = :id
				ORDER BY `gp`.`id` ASC " . 
				(new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
			);
			$stmt->bindValue(":id", $id, PDO::PARAM_INT);
			$stmt->execute();
			
			while($row = $stmt->fetch())	// Récupération des paramètres
			{
				$parameter = (new \JobTypeParameter($id, $row["key"], $row["value"]));
				array_push($instance->_parameters, $parameter);
			}
		}
        
        //Récupérer les pièces
        $stmt = $db->getConnection()->prepare(
            "SELECT `jtp`.* FROM `job_type_porte` AS `jtp` WHERE `jtp`.`job_type_id` = :id " . 
            (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        //Récupération des pièces à produire
        while($row = $stmt->fetch())
        {
            $part = new \JobTypePorte($row["id_job_type_porte"], $id, $row["quantite"], $row["qte_produite"], $row["longueur"], 
                $row["largeur"], $row["grain"], $row["terminer"], $row["estampille"]);
            array_push($instance->_parts, $part);
        }
        
        $instance->setDatabaseConnectionLockingReadType($databaseConnectionLockingReadType);
        return $instance;
    }
    
    /**
     * Save the JobType object in the database
     *
     * @param \FabPlanConnection $db The database in which the record must be saved
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType This JobType (for method chaining)
     */
    public function save(\FabPlanConnection $db) : \JobType
    {
        if($this->getId() === null || self::withID($db, $this->getId()) === null)
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
     * Empties the JobType object in the database
     *
     * @param \FabPlanConnection $db The database in which the record is located
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType This JobType (for method chaining)
     */
    private function emptyInDatabase(FabPlanConnection $db) : \JobType
    {
        if($this->getId() !== null)
        {
            $stmt = $db->getConnection()->prepare("
                    DELETE FROM `job_type_params`
                    WHERE `job_type_id` = :id;
                ");
            $stmt->bindValue(":id", $this->getId(), \PDO::PARAM_INT);
            $stmt->execute();
            
            $stmt = $db->getConnection()->prepare("
                    DELETE FROM `job_type_porte`
                    WHERE `job_type_id` = :id;
                ");
            $stmt->bindValue(":id", $this->getId(), \PDO::PARAM_INT);
            $stmt->execute();
        }
        
        return $this;
    }
    
    /**
     * Insert the JobType object in the database
     *
     * @param \FabPlanConnection $db The database in which the record must be inserted
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType This JobType (for method chaining)
     */
    private function insert(\FabPlanConnection $db) : \JobType
    {
        $stmt = $db->getConnection()->prepare("
            INSERT INTO `job_type` (`id_job_type`, `job_id`, `door_model_id`, `type_no`, `nom_fichier_mpr`, `contenu_fichier_mpr`)
            VALUES (:jobTypeId, :jobId, :modelId, :typeNo, :mprFileName, :mprFileContents);
        ");
        $stmt->bindValue(":jobTypeId", $this->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(":jobId", $this->getJobId(), \PDO::PARAM_INT);
        $stmt->bindValue(":modelId", $this->getModel()->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(":typeNo", $this->getType()->getImportNo(), \PDO::PARAM_INT);
        $stmt->bindValue(":mprFileName", $this->getMprFileName(), \PDO::PARAM_STR);
        $stmt->bindValue(":mprFileContents", $this->getMprFileContents(), \PDO::PARAM_STR);
        $success = $stmt->execute();
        $this->setId(intval($db->getConnection()->lastInsertId()));
        
        /* @var \JobTypeParameter $parameter */
        foreach($this->_parameters as $parameter)
        {
            $parameter->setJobTypeId($this->getId())->save($db);
        }
        
        foreach($this->_parts as $part)
        {
            $part->setJobTypeId($this->getId())->save($db);
        }
        
        return $this;
    }
    
    /**
     * Update the JobType object in the database
     *
     * @param \FabPlanConnection $db The database in which the record must be updated
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType This JobType (for method chaining)
     */
    private function update(\FabPlanConnection $db) : \JobType
    {
        $stmt = $db->getConnection()->prepare("
            UPDATE `job_type`
            SET `job_id` = :jobId, `door_model_id` = :modelId, `type_no` = :typeNo, `nom_fichier_mpr` = :mprFileName, 
                `contenu_fichier_mpr` = :mprFileContents
            WHERE `id_job_type` = :id;
        ");
        $stmt->bindValue(":id", $this->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(":jobId", $this->getJobId(), \PDO::PARAM_INT);
        $stmt->bindValue(":modelId", $this->getModel()->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(":typeNo", $this->getType()->getImportNo(), \PDO::PARAM_INT);
        $stmt->bindValue(":mprFileName", $this->getMprFileName(), \PDO::PARAM_STR);
        $stmt->bindValue(":mprFileContents", $this->getMprFileContents(), \PDO::PARAM_STR);
        $success = $stmt->execute();
        
        foreach($this->_parameters as $parameter)
        {
            $parameter->save($db);
        }
        
        foreach($this->_parts as $part)
        {
            $part->save($db);
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
    public function delete(\FabPlanConnection $db) : \JobType
    {
        if($this->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
        {
            throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
        }
        else
        {
            $this->emptyInDatabase($db);
            
            $stmt = $db->getConnection()->prepare("DELETE FROM `job_type` WHERE `id_job_type` = :id;");
            $stmt->bindValue(":id", $this->getId(), PDO::PARAM_INT);
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
            SELECT `jt`.`estampille` FROM `job_type` AS `jt` WHERE `jt`.`id_job_type` = :id;
        ");
        $stmt->bindValue(":id", $this->getId(), PDO::PARAM_INT);
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
    
    public function loadParameters(\FabplanConnection $db) : \ModelTypeGeneric
    {
        if($this->getModel()->getId() <> 2)
        {
            parent::loadParameters($db);
        }
        else
        {
            $key = null;
            $value = null;
            $description = null;
            $this->setParameters(array());
            
            $matches1 = array();
            preg_match("/\[001\r\n(.*?)\r\n\r\n/s", $this->getMprFileContents(), $matches1);
            $matches2 = array();
            preg_match_all("/^(.*=\".*\")\r$/m", $matches1[1], $matches2);
            
            foreach($matches2[1] as $parameterString)
            {
                if(!preg_match("/^KM=\".*\"$/", $parameterString))
                {
                    $matches3 = array();
                    preg_match("/^(.*)=\"(.*)\"$/", $parameterString, $matches3);
                    $key = $matches3[1];
                    $value = $matches3[2];
                }
                else 
                {
                    $matches3 = array();
                    preg_match("/^KM=\"(.*)\"$/", $parameterString, $matches3);
                    $description = $matches3[1];
                    $modelId = $this->getModel()->getId();
                    $typeNo = $this->getType()->getImportNo();
                    $parameter = new \ModelTypeGenericParameter($key, $value, $modelId, $typeNo, $description, $value);
                    array_push($this->_parameters, $parameter);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Set the id of this JobType.
     *
     * @param int $id The id of this jobType.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType This JobType (for method chaining)
     */
    public function setId(?int $id = null) : \JobType
    {
        $this->_id = $id;
        return $this;
    }
    
    /**
     * Set the id of the Job this JobType belongs to.
     *
     * @param int $id The id of the Job.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType This JobType (for method chaining)
     */
    public function setJobId(?int $id = null) : \JobType
    {
        $this->_jobId = $id;
        return $this;
    }
    
    /**
     * Set the mpr file name of this JobType.
     *
     * @param string $mprFile The mpr file name of this jobType.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType This JobType (for method chaining)
     */
    public function setMprFileName(?string $mprFileName = null) : \JobType
    {
        $this->_mprFileName = $mprFileName;
        return $this;
    }

    /**
     * Set the mpr file contents of this JobType.
     *
     * @param string $mprFileContents The mpr file contents of this jobType.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType This JobType (for method chaining)
     */
    public function setMprFileContents(?string $mprFileContents = null) : \JobType
    {
        $this->_mprFileContents = $mprFileContents;
        return $this;
    }
    
    /**
     * Set the last modification date timestamp of this JobType.
     *
     * @param string $timestamp The last modification date timestamp of this JobType.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType This JobType (for method chaining)
     */
    private function setTimestamp(?string $timestamp = null) : \JobType
    {
        $this->_timestamp = $timestamp;
        return $this;
    }
    
    /**
     * Set the array of parts of this JobType.
     *
     * @param \JobTypePorte[] $parts The array of JobTypePorte of this jobType.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType This JobType (for method chaining)
     */
    public function setParts(array $parts = array()) : \JobType
    {
        $this->_parts = $parts;
        return $this;
    }
    
    /**
     * Get the id of this JobType.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The id of this JobType
     */
    public function getId() : ?int
    {
        return $this->_id;
    }
    
    /**
     * Get the Job id of this JobType.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The id of the Job to which this JobType belongs
     */
    public function getJobId() : int
    {
        return $this->_jobId;
    }
    
    /**
     * Get the name of the mpr file of this JobType (when not using a generic program).
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The name of the mpr file of this JobType
     */
    public function getMprFileName() : ?string
    {
        return $this->_mprFileName;
    }

    /**
     * Get the contents of the mpr file of this JobType (when not using a generic program).
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The contents of the mpr file of this JobType
     */
    public function getMprFileContents() : ?string
    {
        return $this->_mprFileContents;
    }
    
    /**
     * Get the timestamp of the last modification date of this JobType.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The timestamp of the last modification date of this JobType
     */
    public function getTimestamp() : ?string
    {
        return $this->_timestamp;
    }
    
    /**
     * Get the array of parts of this JobType.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobTypePorte[] The array of JobTypePorte of this JobType
     */
    public function getParts() : array
    {
        return $this->_parts;
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
    private function setDatabaseConnectionLockingReadType(int $databaseConnectionLockingReadType) : \JobType
    {
        $this->__database_connection_locking_read_type = $databaseConnectionLockingReadType;
        return $this;
    }
}