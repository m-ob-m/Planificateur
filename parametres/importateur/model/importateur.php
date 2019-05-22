<?php
    /**
     * \name		Importateur
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2019-04-26
     *
     * \brief 		Modèle de la table importateur
     * \details 	Modèle de la table importateur
     */

    class Importateur implements JsonSerializable
    {	
        private $_last_update_timestamp;
        private $_timestamp;
        
        /**
         * Job constructor
         *
         * @param int $_last_update_timestamp The timestamp f the last time the Importateur imported a job into the database.
         * @param string $timestamp A timestamp of the last time the Importateur was updated.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \Importateur
         */
        private function __construct(?int $lastUpdateTimestamp = null, ?string $timestamp = null)
        {
            $this->setLastUpdateTimestamp($lastUpdateTimestamp);
            $this->setTimestamp($timestamp);
        }
        
        /**
         * Retrieve the Importateur.
         *
         * @param \FabPlanConnection $db The database in which the Importateur exists
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \Importateur The Importateur
         */
        static function retrieve(FabPlanConnection $db) : ?\Importateur
        {
            // Récupérer le test
            $stmt = $db->getConnection()->prepare(
                "SELECT `i`.`derniere_date` AS `lastUpdateTimestamp`, `i`.`estampille` AS `timestamp`
                FROM `fabplan`.`importateur` AS `i`;"
            );
            $stmt->execute();
            
            if ($row = $stmt->fetch())
            {
                $instance = new self($row["lastUpdateTimestamp"], $row["timestamp"]);
            }
            else
            {
                $instance = null;
            }

            return $instance;
        }
        
        /**
         * Saves the Importateur's last update timestamp in the database
         *
         * @param \FabPlanConnection $db The database in which the record must be updated.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \Importateur This Importateur (for method chaining)
         */
        public function save(FabPlanConnection $db) : \Importateur
        {
            $stmt = $db->getConnection()->prepare("
                UPDATE `fabplan`.`importateur`
                SET `derniere_date` = :lastUpdateTimestamp;
            ");
            $stmt->bindValue(':lastUpdateTimestamp', $this->getLastUpdateTimestamp(), PDO::PARAM_INT);
            $success = $stmt->execute();
            
            // Récupération de l'estampille à jour
            $this->setTimestamp($this->getTimestampFromDatabase($db));
            
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
                SELECT `i`.`estampille` FROM `fabplan`.`importateur` AS `i`;
            ");
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
         * Set the last update timestamp of the Importateur.
         *
         * @param int $lastUpdateTimestamp The last update UNIX timestamp of the Importateur.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \Importateur This Importateur (for method chaining)
         */
        public function setLastUpdateTimestamp(?int $lastUpdateTimestamp = null) : \Importateur
        {
            $this->_last_update_timestamp = $lastUpdateTimestamp;
            
            return $this;
        }
        
        /**
         * Set the last modification timestamp of the Importateur.
         *
         * @param string $timestamp The last modification date timestamp of this Job.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \Importateur This Importateur (for method chaining)
         */
        private function setTimestamp(?string $timestamp = null) : \Importateur
        {
            $this->_timestamp = $timestamp;
            return $this;
        }
        
        /**
         * Get the last update timestamp of the Importateur.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \Importateur The last update timestamp of the Importateur
         */
        public function getLastUpdateTimestamp() : ?int
        {
            return $this->_last_update_timestamp;
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