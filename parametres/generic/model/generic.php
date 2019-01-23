<?php
/**
 * \name		Generic
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2017-03-21
 *
 * \brief 		Modele de générique
 * \details 	Modele de générique
 */
include_once __DIR__ . '/genericparameter.php';
include_once __DIR__ . '/../../type/controller/typeController.php';

class Generic implements JsonSerializable{
    
    private $_id;
    private $_filename;
    private $_description;
    private $_heightParameter;
    private $_genericParameters; // tableau de GenericParameters
    
    /**
     * Generic constructor
     *
     * @param int $id The id of the Generic in the database
     * @param string $nom The name of the test
     * @param string $description The description of this generic
     * @param string $heightParameter The height parameter of this generic
     * @param GenericParameter array $genericParameters an array of GenericParameters objects that belong to this Generic
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Test
     */
    function __construct(?int $id = null, ?string $filename = null, ?string $description = null, ?string $heightParameter = null, 
        ?array $genericParameters = array())
    {
        $this->_id = $id;
        $this->_filename = $filename;
        $this->_description = $description;
        $this->_heightParameter = $heightParameter;
        $this->_genericParameters = $genericParameters;
    }
    
    /**
     * Generic constructor using ID of existing record
     *
     * @param FabPlanConnection $db The database in which the record exists
     * @param int $id The id of the record in the database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic The Generic associated to the specified ID in the specified database
     */
    public static function withID($db, $id) :?Generic
    {
        // Récupérer le Generic
        $stmt = $db->getConnection()->prepare("SELECT `g`.* FROM `fabplan`.`generics` AS `g` WHERE `g`.`id` = :id;");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($row = $stmt->fetch())	// Récupération de l'instance de matériel
        {
            $instance = new self($row["id"], $row["filename"], $row["description"], $row["heightParameter"]);
        }
        else
        {
            return null;
        }
        
        $stmt= $db->getConnection()->prepare("
            SELECT `gp`.* FROM `fabplan`.`generic_parameters` AS `gp` 
            WHERE `gp`.`generic_id` = :genericId 
            ORDER BY `gp`.`id` ASC;
        ");
        $stmt->bindValue(':genericId', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $instance->setGenericParameters(array());
        while($row = $stmt->fetch())
        {
            $parameter = new \GenericParameter(
                $row["id"], 
                $row["generic_id"], 
                $row["parameter_key"], 
                $row["parameter_value"], 
                $row["description"], 
                $row["quick_edit"]
            );
            $instance->addGenericParameter($parameter);
        }
        
        return $instance;
    }
    
    /**
     * Save the Generic object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be saved
     * @param bool $overwriteParameters A boolean that indicates if GenericParameters must be overwritten
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic This Generic (for method chaining)
     */
    function save(FabPlanConnection $db, bool $overwriteParameters) : Generic
    {
        if($this->_id === null)
        {
            $this->insert($db, $overwriteParameters);
        }
        else
        {
            $this->update($db, $overwriteParameters);
        }
        
        return $this;
    }
    
    /**
     * Insert the Generic object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be inserted
     * @param bool $overwriteParameters A boolean that indicates if GenericParameters must be overwritten
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic This Generic (for method chaining)
     */
    private function insert(FabPlanConnection $db, bool $overwriteParameters) : Generic
    {
        try
        {
            $db->getConnection()->beginTransaction();	/*Démarrage de la transaction pour que tout soit créer dans un seul 
            bloc ou pas du tout (ACID) s'il y a eu une erreur durant les transactions (pour l'intégrité des données)*/
            
            // Création d'un test
            $stmt = $db->getConnection()->prepare("
                INSERT INTO `generics` (`filename`, `description`, `heightParameter`) 
                VALUES (:filename, :description, :heightParameter);
            ");
            $stmt->bindValue(':filename', $this->_filename, PDO::PARAM_STR);
            $stmt->bindValue(':description', $this->_description, PDO::PARAM_STR);
            $stmt->bindValue(":heightParameter", $this->_heightParameter, PDO::PARAM_STR);
            $stmt->execute();
            $this->_id = $db->getConnection()->lastInsertId();
            
            if($overwriteParameters)
            {
                $this->deleteGenericParametersFromDatabase($db);
                
                foreach($this->_genericParameters as $genericParameter)
                {
                    $genericParameter->save($db);
                }
            }
            
            $db->getConnection()->commit();	// Envoi des transactions à la BD
            
            return $this;
        }
        catch (Exception $e)
        {
            $db->getConnection()->rollback();
            throw $e;
        }
    }
    
    /**
     * Update the Generic object in the database
     *
     * @param FabPlanConnection $db The database in which the record must be updated
     * @param bool $overwriteParameters A boolean that indicates if GenericParameters must be overwritten
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic This Generic (for method chaining)
     */
    private function update(FabPlanConnection $db, bool $overwriteParameters) : Generic
    {
        try
        {
            $db->getConnection()->beginTransaction();	/* Démarrage de la transaction pour que tout soit créé dans un seul 
            bloc ou pas du tout (ACID) s'il y a eu une erreur durant les transactions (pour l'intégrité des données)*/
            
            // Mise à jour d'un test
            $stmt = $db->getConnection()->prepare("
                UPDATE `fabplan`.`generics` 
                SET `fabplan`.`generics`.`filename` = :filename, 
                    `fabplan`.`generics`.`description` = :description, 
                    `fabplan`.`generics`.`heightParameter` = :heightParameter
                WHERE `fabplan`.`generics`.`id` = :id;
            ");
            $stmt->bindValue(':filename', $this->_filename, PDO::PARAM_STR);
            $stmt->bindValue(':description', $this->_description, PDO::PARAM_STR);
            $stmt->bindValue(":heightParameter", $this->_heightParameter, PDO::PARAM_STR);
            $stmt->bindValue(':id', $this->_id, PDO::PARAM_INT);
            $stmt->execute();
            
            if($overwriteParameters)
            {
                $this->deleteGenericParametersFromDatabase($db);
                
                foreach($this->_genericParameters as $genericParameter)
                {
                    $genericParameter->save($db);
                }
            }
            
            $db->getConnection()->commit();	// Envoi des transactions à la BD
            return $this;
        }
        catch (Exception $e)
        {
            $db->getConnection()->rollback();
            throw $e;
        }
    }
    
    /**
     * Delete the Generic object from the database
     *
     * @param FabPlanConnection $db The database from which the record must be deleted
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic This Generic (for method chaining)
     */
    public function delete(FabPlanConnection $db) : Generic
    {
        try
        {
            $db->getConnection()->beginTransaction();	/* Démarrage de la transaction pour que tout soit créé dans un seul bloc 
            ou pas du tout (ACID) s'il y a eu une erreur durant les transactions (pour l'intégrité des données)*/
            
            if(!empty($this->getAssociatedTypes()))
            {
                throw new \Exception("This Generic still has associated Types.");
            }
            
            foreach($this->_genericParameters as $genericParameter)
            {
                $genericParameter->delete($db);
            }
            
            $stmt = $db->getConnection()->prepare("
                DELETE FROM `fabplan`.`generics` WHERE `fabplan`.`generics`.`id` = :id;
            ");
            $stmt->bindValue(':id', $this->_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $db->getConnection()->commit();	// Envoi des transactions à la BD
            
            return $this;
        }
        catch (Exception $e)
        {
            $db->getConnection()->rollback();
            throw $e;
        }
    }
    
    /**
     * Get the types associated to this Generic
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return array An array of Type associated to this Generic
     */
    function getAssociatedTypes() : array
    {
        $associatedTypes = array();
        foreach((new TypeController())->getTypes() as $type)
        {
            if($type->getGenericId() === $this->getId())
            {
                array_push($associatedTypes, $type);
            }
        }
        return $associatedTypes;
    }
    
    /**
     * Get the id of this Generic
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return int The id of this Generic in the database
     */
    public function getId() : ?int
    {
        return $this->_id;
    }
    
    /**
     * Get the name of the file associated with this Generic
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The name of the generic file.
     */
    public function getFilename() : ?string
    {
        return $this->_filename;
    }
    
    /**
     * Get the description of this Generic
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The description of the Generic
     */
    public function getDescription() : ?string
    {
        return $this->_description;
    }
    
    /**
     * Get the height parameter of this Generic
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The height parameter of the Generic
     */
    public function getHeightParameter() : ?string
    {
        return $this->_heightParameter;
    }
    
    /**
     * Get the parameters of this Generic
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return GenericParameter array The parameters of this Generic
     */
    public function getGenericParameters() : array
    {
        return $this->_genericParameters;
    }
    
    public function getGenericParameterByKey(string $key) : ?\GenericParameter
    {
        foreach($this->_genericParameters as $genericParameter)
        {
            if($genericParameter->getKey() === $key)
            {
                return $genericParameter;
            }
        }
        return null;
    }
    
    /**
     * Set the name of the file associated with this Generic
     *
     * @param string $filename The name of the file
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic This Generic (for method chaining)
     */
    public function setFilename(string $filename) : \Generic
    {
        $this->_filename = $filename;
        return $this;
    }
    
    /**
     * Set the description of this Generic
     *
     * @param string $description The description of the Generic object
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic This Generic (for method chaining)
     */
    public function setDescription(string $description) : Generic
    {
        $this->_description = $description;
        return $this;
    }
    
    /**
     * Set the height parameter of this Generic
     *
     * @param string $heightParameter The height parameter of the Generic object
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic This Generic (for method chaining)
     */
    public function setHeightParameter(string $heightParameter) : Generic
    {
        $this->_heightParameter = $heightParameter;
        return $this;
    }
    
    /**
     * Set the GenericParameter array of this Generic
     *
     * @param GenericParameter array $genericParameters The new generic parameters array of the Generic object
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic This Generic (for method chaining)
     */
    public function setGenericParameters(array $genericParameters) : Generic
    {
        $this->_genericParameters = $genericParameters;
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
     * Add a parameter to this Generic
     *
     * @param GenericParameter $genericParameter The new GenericParameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic This Generic (for method chaining)
     */
    public function addGenericParameter(GenericParameter $genericParameter) : Generic
    {
        array_push($this->_genericParameters, $genericParameter);
        return $this;
    }
    
    /**
     * Remove a parameter to this Generic
     *
     * @param GenericParameter $genericParameter The GenericParameter to remove
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic This Generic (for method chaining)
     */
    public function removeGenericParameter(string $genericParameter) : Generic
    {
        unset($this->_genericParameters[array_search($genericParameter, $this->_genericParameters, true)]);
        return $this;
    }
    
    /**
     * Removes all GenericParameters associated to this Generic without deleting GenericParameters in this object.
     * This allows removal of obsolete variables that are not part of the Generic object anymore, but still
     * subsist in the database.
     *
     * @param FabPlanConnection $db The database containing the Generic and its parameters.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return Generic This Generic (for method chaining)
     */
    private function deleteGenericParametersFromDatabase(FabPlanConnection $db) : Generic
    {
        $stmt = $db->getConnection()->prepare("
            DELETE FROM `generic_parameters`
            WHERE `generic_parameters`.`generic_id` = :genericId;
        ");
        $stmt->bindValue(':genericId', $this->_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this;
    }
    
    /**
     * Get the parameters in the [key => value] format
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string array The parameters of this Generic
     */
    public function getParametersAsKeyValuePairs() : array
    {
        $parametersArray = array();
        foreach($this->getGenericParameters() as $parameter)
        {
            $parametersArray[$parameter->getKey()] = $parameter->getValue();
        }
        return $parametersArray;
    }
}
?>