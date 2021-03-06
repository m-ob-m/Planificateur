<?php 
    /**
     * \name		save.php
     * \author    	Marc-olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-03-21
     *
     * \brief 		Sauvegarde d'un générique
     * \details 	Sauvegarde d'un générique
     */
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        // INCLUDE
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/generic/controller/genericController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";

        // Initialize the session
        session_start();
        
        // Check if the user is logged in, if not then redirect him to login page
        if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            if(!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest")
            {
                throw new \Exception("You are not logged in.");
            }
            else
            {
                header("location: /Planificateur/lib/account/logIn.php");
            }
            exit;
        }

        // Getting a connection to the database.
	    $db = new \FabPlanConnection();

        // Closing the session to let other scripts use it.
        session_write_close();

        $input =  json_decode(file_get_contents("php://input"));
        
        // Vérification des paramètres
        $id = (isset($input->id) ? $input->id : null);
        $filename = (isset($input->filename) ? $input->filename : null);
        $description = (isset($input->description) ? $input->description : null);
        $heightParameter = (isset($input->heightParameter) ? $input->heightParameter : null);
        $copyParametersFrom = (isset($input->copyParametersFrom) ? $input->copyParametersFrom : null);
        
        try
        {
            $db->getConnection()->beginTransaction();
            if($id === null)
            {
                $generic = new \Generic();
            }
            else
            {
                $generic = \Generic::withID($db, $id, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
                if($generic === null)
                {
                    throw new \Exception("Il n'y a aucun générique possédant l'identifiant numérique unique \"{$id}\".");
                }
            }
            
            $generic->setFilename($filename)->setDescription($description)->setHeightParameter($heightParameter);
            $generic = saveGeneric($generic, $copyParametersFrom, $db);
            $db->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $db->getConnection()->rollback();
            throw $e;
        }
        finally
        {
            $db = null;
        }
        
        // Retour au javascript
        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = $generic->getID();
    }
    catch(Exception $e)
    {
        $responseArray["status"] = "failure";
        $responseArray["failure"]["message"] = $e->getMessage();
    }
    finally
    {
        echo json_encode($responseArray);
    }
    
    /**
     * Saves a Generic into the database.
     *
     * @param \Generic $generic A Generic that is to receive parameters from the Generic designated by $referenceId
     * @param int $referenceId The id of an existing Generic from which parameters will be fetched to intialize the new 
     *                         Generic. If it is set to null, the Generic will be created without any parameter.
     * @param \FabplanConnection $db The database to save the Generic into.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Generic This \Generic
     */
    function saveGeneric(\Generic $generic, ?int $referenceId, ?\FabPlanConnection $db) : ?\Generic
    {
        $create = ($generic->getId() === null) ? true : false;
        $generic->save($db, false);
        if($referenceId !== null && $create === true)
        {
            // Only on insert
            $generic = \Generic::withID($db, $generic->getId(), \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
            $referenceGeneric = \Generic::withID($db, $referenceId, \MYSQLDatabaseLockingReadTypes::FOR_SHARE);
            $generic = copyGenericParameters($generic, $referenceGeneric->getParameters())->save($db, true);
        }
            
        return $generic;
    }
    
    /**
     * Copies a list of GenericParameters in a Generic, setting the GenericParameters' GenericId to the right value.
     *
     * @param \Generic $generic The Generic in which parameters should be inserted
     * @param \GenericParameter[] $parameters The list of GenericParameters to add in the Generic object
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Generic The modified Generic object
     */ 
    function copyGenericParameters(\Generic $generic, array $parameters) : \Generic
    {
        if(!empty($parameters))
        {
            /* @var $parameter \GenericParameter */
            foreach($parameters as $parameter)
            {
                $generic->addParameter($parameter->setGenericId($generic->getId()));
            }
        }
        return $generic;
    }
?>