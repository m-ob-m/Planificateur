<?php

/**
 * \name		materiel.php
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-01-27
*
* \brief 		Modèle de la table matériel
* \details 		Modèle de la table matériel
*/

class Materiel  implements JsonSerializable
{
	private $_id_materiel;
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
	 * Materiel constructor
	 *
	 * @param int $id_materiel The id of the Materiel in the database
	 * @param string $codeSIA The SIA code of the Materiel
	 * @param string $codeCutRite The CutRite code of the Materiel
	 * @param string $description The description of the Materiel
	 * @param string $epaisseur The thickness of the Materiel
	 * @param string $essence The wood type of the Materiel
	 * @param string $grain "Y" if Materiel has a grain direction
	 * @param string $est_mdf "Y" if Materiel is MDF
	 * @param string $estampille The last modification date of the Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Test
	 */ 
	function __construct(?int $id_materiel = null, ?string $codeSIA = null, ?string $codeCutRite = null, ?string $description = null, 
	    ?string $epaisseur = null, ?string $essence = null, ?string $grain = null, ?string $est_mdf = null, ?string $estampille = null)
	{
	    $this->setId($id_materiel);
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
	 * Materiel constructor using ID of existing record
	 *
	 * @param FabPlanConnection $db The database in which the record exists
	 * @param int $id The id of the record in the database
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel The Materiel associated to the specified ID in the specified database
	 */ 
	public static function withID(\FabplanConnection $db, ?int $id, int $databaseConnectionLockingReadType = 0) : ?\Materiel
	{	    
	    // Récupérer le test
	    $stmt = $db->getConnection()->prepare(
            "SELECT `m`.* FROM `fabplan`.`materiel` AS `m` WHERE `m`.`id_materiel` = :id " . 
            (new \MYSQLDatabaseLockingReadTypes($databaseConnectionLockingReadType))->toLockingReadString() . ";"
        );
	    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
	    $stmt->execute();
	    
	    if ($row = $stmt->fetch())	// Récupération de l'instance de matériel
	    {
	        $instance = new self($row["id_materiel"], $row["codeSIA"], $row["codeCutRite"], $row["description"], 
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
	 * Save the Materiel object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be saved
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel (for method chaining)
	 */
	public function save(FabPlanConnection $db) : Materiel
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
	 * Insert the Materiel object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be inserted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel (for method chaining)
	 */
	private function insert(FabPlanConnection $db) : Materiel
	{
	    // Création d'un Materiel
	    $stmt = $db->getConnection()->prepare("
            INSERT INTO `fabplan`.`materiel`(`codeSIA`, `codeCutRite`, `description`, `epaisseur`, `essence`, `grain`, `est_mdf`)
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
	    
	    $this->setId($db->getConnection()->lastInsertId());
	    
	    return $this;
	}
	
	/**
	 * Update the Materiel object in the database
	 *
	 * @param FabPlanConnection $db The database in which the record must be updated
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel (for method chaining)
	 */
	private function update(FabPlanConnection $db) : Materiel
	{
	    // Mise à jour d'un Materiel
	    $stmt = $db->getConnection()->prepare("
            UPDATE `fabplan`.`materiel` AS `m`
            SET `codeSIA` = :siaCode, `codeCutRite` = :cutRiteCode, `description` = :description, `epaisseur` = :thickness,
                `essence` = :woodType, `grain` = :hasGrain, `est_mdf` = :isMDF
            WHERE `id_materiel` = :id;
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
	 * Delete the Materiel object from the database
	 *
	 * @param FabPlanConnection $db The database from which the record must be deleted
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel (for method chaining)
	 */
	public function delete(FabPlanConnection $db) : Materiel
	{
	    if($this->getDatabaseConnectionLockingReadType() !== \MYSQLDatabaseLockingReadTypes::FOR_UPDATE)
	    {
	        throw new \Exception("The provided " . get_class($this) . " is not locked for update.");
	    }
	    else
	    {
    	    $stmt = $db->getConnection()->prepare("DELETE FROM `materiel` WHERE `materiel`.`id_materiel` = :id;");
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
            SELECT `m`.`estampille` AS `timestamp` FROM `fabplan`.`materiel` AS `m` WHERE `m`.`id_materiel` = :id;
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
	 * Get the id of this Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of this Materiel
	 */ 
	public function getId() :?int
	{
		return $this->_id_materiel;
	}
	
	/**
	 * Get the SIA code of this Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The SIA code of this Materiel
	 */ 
	public function getCodeSIA() :?string
	{
		return $this->_codeSIA;
	}
	
	/**
	* Get the CutRite code of this Materiel
	*
	* @throws
	* @author Marc-Olivier Bazin-Maurice
	* @return string The CutRite code of this Materiel
	*/ 
	public function getCodeCutRite() :?string
	{
		return $this->_codeCutRite;
	}
	
	/**
	 * Get the description of this Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The description of this Materiel
	 */ 
	public function getDescription() :?string
	{
		return $this->_description;
	}
	
	/**
	 * Get the thickness of this Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The thickness of this Materiel
	 */ 
	public function getEpaisseur() :?string
	{
		return $this->_epaisseur;
	}
	
	/**
	 * Get the wood type of this Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The wood type of this Materiel
	 */ 
	public function getEssence() :?string
	{
		return $this->_essence;
	}
	
	/**
	 * Get wheter this Materiel has grain
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string "Y" if this Materiel has grain, "N" otherwise.
	 */ 
	public function getGrain() :?string
	{
		return $this->_grain;
	}
	
	/**
	 * Get wheter this Materiel is MDF
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string "Y" if this Materiel is MDF, "N" otherwise.
	 */ 
	public function getEstMDF() :?string
	{
		return $this->_est_mdf;
	}
	
	/**
	 * Get last modification date of this Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string "Y" if this Materiel is MDF, "N" otherwise.
	 */ 
	public function getTimestamp() :?string
	{
		return $this->_estampille;
	}
    
	/**
	 * Set the id of this Materiel
	 * 
	 * @param int $id The new id for this Materiel 
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel
	 */ 
	public function setId(?int $id) :Materiel
	{
	    $this->_id_materiel = $id;
	    return $this;
	}
	
	/**
	 * Set the SIA code of this Materiel
	 *
	 * @param string $siaCode The new SIA code for this Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel
	 */ 
	public function setCodeSIA(?string $siaCode) :Materiel
	{
	    $this->_codeSIA = $siaCode;
	    return $this;
	}
	
   /**
	* Set the CutRite code of this Materiel
	*
	* @param string $cutRiteCode The new CutRite code for this Materiel
	*
	* @throws
	* @author Marc-Olivier Bazin-Maurice
	* @return Materiel This Materiel
	*/ 
	public function setCodeCutRite(?string $cutRiteCode) :Materiel
	{
	    $this->_codeCutRite = $cutRiteCode;
	    return $this;
	}
	
	/**
	 * Set the description of this Materiel
	 *
	 * @param string $description The new description for this Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel
	 */ 
	public function setDescription(?string $description) :Materiel
	{
	    $this->_description = $description;
	    return $this;
	}
	
	/**
	 * Set the thickness of this Materiel
	 *
	 * @param string $thickness The new thickness for this Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel
	 */ 
	public function setEpaisseur(?string $thickness) :Materiel
	{
	    $this->_epaisseur = $thickness;
	    return $this;
	}
	
	/**
	 * Set the wood type of this Materiel
	 *
	 * @param string $woodType The new wood type for this Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel
	 */ 
	public function setEssence(?string $woodType) :Materiel
	{
	    $this->_essence = $woodType;
	    return $this;
	}
	
	/**
	 * Set the grain attribute of this Materiel
	 *
	 * @param string $grain "Y" if Materiel has grain, "N" otherwise
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel
	 */ 
	public function setGrain(?string $grain) :Materiel
	{
	    $this->_grain = $grain;
	    return $this;
	}
	
	/**
	 * Set the est_mdf attribute of this Materiel
	 *
	 * @param string $isMDF "Y" if Materiel is MDF, "N" otherwise
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel
	 */ 
	public function setEstMDF(?string $isMDF) :Materiel
	{
	    $this->_est_mdf = $isMDF;
	    return $this;
	}
	
	/**
	 * Set the last modification date of this Materiel
	 *
	 * @param string $timestamp The last modification date of this Materiel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Materiel This Materiel
	 */ 
	private function setTimestamp(?string $timestamp) :Materiel
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
	private function setDatabaseConnectionLockingReadType(int $databaseConnectionLockingReadType) : \Materiel
	{
	    $this->__database_connection_locking_read_type = $databaseConnectionLockingReadType;
	    return $this;
	}
}