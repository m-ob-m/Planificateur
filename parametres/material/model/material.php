<?php

/**
 * \name		material.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-01-27
*
* \brief 		Modèle de la table matériel
* \details 		Modèle de la table matériel
*/

class Material  implements JsonSerializable
{
	private $_id;
	private $_codeSIA;
	private $_codeCutRite;
	private $_description;
	private $_epaisseur;
	private $_essence;
	private $_grain;
	private $_est_mdf;
	private $_estampille;
	private $__database_connection_locking_read_type = \MYSQLDatabaseLockingReadTypes::NONE;
	
	/**
	 * Material constructor
	 *
	 * @param int $id The id of the Material in the database
	 * @param string $codeSIA The SIA code of the Material
	 * @param string $codeCutRite The CutRite code of the Material
	 * @param string $description The description of the Material
	 * @param string $epaisseur The thickness of the Material
	 * @param string $essence The wood type of the Material
	 * @param string $grain "Y" if Material has a grain direction
	 * @param string $est_mdf "Y" if Material is MDF
	 * @param string $estampille The last modification date of the Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test
	 */ 
	function __construct(?int $id = null, ?string $codeSIA = null, ?string $codeCutRite = null, ?string $description = null, 
	    ?string $epaisseur = null, ?string $essence = null, ?string $grain = null, ?string $est_mdf = null, ?string $estampille = null)
	{
	    $this->setId($id);
	    $this->setCodeSIA($codeSIA);
	    $this->setCodeCutRite($codeCutRite);
		$this->setDescription($description);
		$this->setEpaisseur($epaisseur);
		$this->setEssence($essence);
		$this->setGrain($grain);
		$this->setEstMDF($est_mdf);
		$this->setTimestamp($estampille);
	}
	
	/**
	 * Material constructor using ID of existing record
	 *
	 * @param \FabPlanConnection $db The database in which the record exists
	 * @param int $id The id of the record in the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Material The Material associated to the specified ID in the specified database
	 */ 
	public static function withID(\FabplanConnection $db, ?int $id, int $databaseConnectionLockingReadType = 0) : ?\Material
	{	    
	    // Récupérer le test
	    $stmt = $db->getConnection()->prepare(
            "SELECT `m`.* FROM `material` AS `m` WHERE `m`.`id` = :id " . 
            (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	// Récupération de l'instance de matériel
	    {
	        $instance = new self($row["id"], $row["codeSIA"], $row["codeCutRite"], $row["description"], 
	            $row["epaisseur"], $row["essence"], $row["grain"], $row["est_mdf"], $row["estampille"]);
	    }
	    else
	    {
	        return null;
	    }
	    
	    $instance->setDatabaseConnectionLockingReadType($databaseConnectionLockingReadType);
	    return $instance;
	}

	/**
	 * Material constructor using Cut Rite code of existing record
	 *
	 * @param \FabPlanConnection $db The database in which the record exists
	 * @param string $cutRiteCode The code of the Material in the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Material The Material associated to the specified code in the specified database
	 */ 
	public static function withCutRiteCode(\FabplanConnection $db, string $cutRiteCode, int $databaseConnectionLockingReadType = 0) : ?\Material
	{	    
	    // Récupérer le test
	    $stmt = $db->getConnection()->prepare(
            "SELECT `m`.* FROM `material` AS `m` WHERE `m`.`codeCutRite` = :cutRiteCode " . 
            (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':cutRiteCode', $cutRiteCode, PDO::PARAM_STR);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	// Récupération de l'instance de matériel
	    {
	        $instance = new self($row["id"], $row["codeSIA"], $row["codeCutRite"], $row["description"], 
	            $row["epaisseur"], $row["essence"], $row["grain"], $row["est_mdf"], $row["estampille"]);
	    }
	    else
	    {
	        return null;
	    }
	    
	    $instance->setDatabaseConnectionLockingReadType($databaseConnectionLockingReadType);
	    return $instance;
	}
	
	/**
	 * Save the Material object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be saved
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material (for method chaining)
	 */
	public function save(FabPlanConnection $db) : Material
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
	 * Insert the Material object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be inserted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material (for method chaining)
	 */
	private function insert(FabPlanConnection $db) : Material
	{
	    // Création d'un Material
	    $stmt = $db->getConnection()->prepare("
            INSERT INTO `material`(`codeSIA`, `codeCutRite`, `description`, `epaisseur`, `essence`, `grain`, `est_mdf`)
            VALUES (:siaCode, :cutRiteCode, :description, :thickness, :woodType, :hasGrain, :isMDF);
        ");
	    $stmt->bindValue(":siaCode", $this->getCodeSIA(), PDO::PARAM_STR);
	    $stmt->bindValue(":cutRiteCode", $this->getCodeCutRite(), PDO::PARAM_STR);
	    $stmt->bindValue(":description", $this->getDescription(), PDO::PARAM_STR);
	    $stmt->bindValue(":thickness", $this->getEpaisseur(), PDO::PARAM_STR);
	    $stmt->bindValue(":woodType", $this->getEssence(), PDO::PARAM_STR);
	    $stmt->bindValue(":hasGrain", $this->getGrain(), PDO::PARAM_STR);
	    $stmt->bindValue(":isMDF", $this->getEstMDF(), PDO::PARAM_STR);
	    $stmt->execute();
	    
	    $this->setId(intval($db->getConnection()->lastInsertId()));
	    
	    return $this;
	}
	
	/**
	 * Update the Material object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be updated
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material (for method chaining)
	 */
	private function update(FabPlanConnection $db) : Material
	{
	    // Mise à jour d'un Material
	    $stmt = $db->getConnection()->prepare("
            UPDATE `material` AS `m`
            SET `codeSIA` = :siaCode, `codeCutRite` = :cutRiteCode, `description` = :description, `epaisseur` = :thickness,
                `essence` = :woodType, `grain` = :hasGrain, `est_mdf` = :isMDF
            WHERE `id` = :id;
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->bindValue(":siaCode", $this->getCodeSIA(), PDO::PARAM_STR);
	    $stmt->bindValue(":cutRiteCode", $this->getCodeCutRite(), PDO::PARAM_STR);
	    $stmt->bindValue(":description", $this->getDescription(), PDO::PARAM_STR);
	    $stmt->bindValue(":thickness", $this->getEpaisseur(), PDO::PARAM_STR);
	    $stmt->bindValue(":woodType", $this->getEssence(), PDO::PARAM_STR);
	    $stmt->bindValue(":hasGrain", $this->getGrain(), PDO::PARAM_STR);
	    $stmt->bindValue(":isMDF", $this->getEstMDF(), PDO::PARAM_STR);
	    $stmt->execute();
	    
	    return $this;
	}
	
	/**
	 * Delete the Material object from the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material (for method chaining)
	 */
	public function delete(FabPlanConnection $db) : Material
	{
	    if($this->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
	    {
	        throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
	    }
	    else
	    {
    	    $stmt = $db->getConnection()->prepare("DELETE FROM `material` WHERE `material`.`id` = :id;");
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
            SELECT `m`.`estampille` AS `timestamp` FROM `material` AS `m` WHERE `m`.`id` = :id;
        ");
	    $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if($row = $stmt->fetch())
	    {
	        return $row["timestamp"];
	    }
	    else
	    {
	        return null;
	    }
	}
	
	/**
	 * Get the id of this Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of this Material
	 */ 
	public function getId() :?int
	{
		return $this->_id;
	}
	
	/**
	 * Get the SIA code of this Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The SIA code of this Material
	 */ 
	public function getCodeSIA() :?string
	{
		return $this->_codeSIA;
	}
	
	/**
	* Get the CutRite code of this Material
	*
	* @throws
	* @author Marc-Olivier Bazin-Maurice
	* @return string The CutRite code of this Material
	*/ 
	public function getCodeCutRite() :?string
	{
		return $this->_codeCutRite;
	}
	
	/**
	 * Get the description of this Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The description of this Material
	 */ 
	public function getDescription() :?string
	{
		return $this->_description;
	}
	
	/**
	 * Get the thickness of this Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The thickness of this Material
	 */ 
	public function getEpaisseur() :?string
	{
		return $this->_epaisseur;
	}
	
	/**
	 * Get the wood type of this Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The wood type of this Material
	 */ 
	public function getEssence() :?string
	{
		return $this->_essence;
	}
	
	/**
	 * Get wheter this Material has grain
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string "Y" if this Material has grain, "N" otherwise.
	 */ 
	public function getGrain() :?string
	{
		return $this->_grain;
	}
	
	/**
	 * Get wheter this Material is MDF
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string "Y" if this Material is MDF, "N" otherwise.
	 */ 
	public function getEstMDF() :?string
	{
		return $this->_est_mdf;
	}
	
	/**
	 * Get last modification date of this Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string "Y" if this Material is MDF, "N" otherwise.
	 */ 
	public function getTimestamp() :?string
	{
		return $this->_estampille;
	}
    
	/**
	 * Set the id of this Material
	 * 
	 * @param int $id The new id for this Material 
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material
	 */ 
	public function setId(?int $id) :Material
	{
	    $this->_id = $id;
	    return $this;
	}
	
	/**
	 * Set the SIA code of this Material
	 *
	 * @param string $siaCode The new SIA code for this Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material
	 */ 
	public function setCodeSIA(?string $siaCode) :Material
	{
	    $this->_codeSIA = $siaCode;
	    return $this;
	}
	
   /**
	* Set the CutRite code of this Material
	*
	* @param string $cutRiteCode The new CutRite code for this Material
	*
	* @throws
	* @author Marc-Olivier Bazin-Maurice
	* @return Material This Material
	*/ 
	public function setCodeCutRite(?string $cutRiteCode) :Material
	{
	    $this->_codeCutRite = $cutRiteCode;
	    return $this;
	}
	
	/**
	 * Set the description of this Material
	 *
	 * @param string $description The new description for this Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material
	 */ 
	public function setDescription(?string $description) :Material
	{
	    $this->_description = $description;
	    return $this;
	}
	
	/**
	 * Set the thickness of this Material
	 *
	 * @param string $thickness The new thickness for this Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material
	 */ 
	public function setEpaisseur(?string $thickness) :Material
	{
	    $this->_epaisseur = $thickness;
	    return $this;
	}
	
	/**
	 * Set the wood type of this Material
	 *
	 * @param string $woodType The new wood type for this Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material
	 */ 
	public function setEssence(?string $woodType) :Material
	{
	    $this->_essence = $woodType;
	    return $this;
	}
	
	/**
	 * Set the grain attribute of this Material
	 *
	 * @param string $grain "Y" if Material has grain, "N" otherwise
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material
	 */ 
	public function setGrain(?string $grain) :Material
	{
	    $this->_grain = $grain;
	    return $this;
	}
	
	/**
	 * Set the est_mdf attribute of this Material
	 *
	 * @param string $isMDF "Y" if Material is MDF, "N" otherwise
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material
	 */ 
	public function setEstMDF(?string $isMDF) :Material
	{
	    $this->_est_mdf = $isMDF;
	    return $this;
	}
	
	/**
	 * Set the last modification date of this Material
	 *
	 * @param string $timestamp The last modification date of this Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Material This Material
	 */ 
	private function setTimestamp(?string $timestamp) :Material
	{
	    $this->_estampille = $timestamp;
	    return $this;
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
	private function setDatabaseConnectionLockingReadType(int $databaseConnectionLockingReadType) : \Material
	{
	    $this->__database_connection_locking_read_type = $databaseConnectionLockingReadType;
	    return $this;
	}
}