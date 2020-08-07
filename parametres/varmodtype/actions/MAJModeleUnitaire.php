<?php
    /**
     * \name		MAJModeleUnitaire
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-03-16
     *
     * \brief 		Met à jour les programmes unitaires des combinaisons modèle-type spécifiées
     */
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    $lock = null;
    try
    {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/mpr/mprCutRite.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/test/controller/testController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/model/controller/modelController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/type/controller/typeController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/generic/controller/genericController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/varmodtypegen/controller/modelTypeGenericController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/fileFunctions/fileFunctions.php";
        
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

        set_time_limit(3600); // Pour éviter les erreurs de dépassement du temps alloué, on augmente le temps alloué.

        $lockName = sys_get_temp_dir() . "/MAJModeleUnitaire.tmp";
        if(!$lock = fopen($lockName, "c"))
        {
            throw new \Exception("Le verrou d'application ne peut pas être créé.");
        }
        
        $alreadyRunning = false;
        flock($lock, LOCK_EX | LOCK_NB, $alreadyRunning);
        if($alreadyRunning)
        {
            throw new \Exception("L'opération est déja en cours.");
        }
        
        $input =  json_decode(file_get_contents("php://input"));
        
        // Vérification des paramètres
        $modelId = isset($input->modelId) ? $input->modelId : null;
        $typeNo = isset($input->typeNo) ? $input->typeNo : null;
        
        GenerateUnitaryPrograms($db, $modelId, $typeNo);
        
        // Retour au javascript
        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = null;
    }
    catch(Exception $e)
    {
        $responseArray["status"] = "failure";
        $responseArray["failure"]["message"] = $e->getMessage();
    }
    finally
    {
        try{
            if($lock !== false)
            {
                fclose($lock);
            }
            
            if(file_exists($lockName))
            {
                unlink($lockName);
            }
        }
        catch(\Exception $e)
        {
            $responseArray["status"] = "failure";
            $responseArray["failure"]["message"] .= "
                L'opération a réussi, mais le verrour d'application n'a pas pu être relâché. 
                Cette routine risque d'échouer au prochain lancement.
            ";
        }
        finally
        {
            echo json_encode($responseArray);
        }
    }
    
    /**
     * Main unitary program generation function that creates a test that is used to subsequently make every unitary program
     * 
     * @param \FabplanConnection $db The database to fetch the parameters from
     * @param int $modelId The model ID for which unitary programs must be generated (null means all)
     * @param int $typeId The type ID for which unitary programs must be generated (null means all)
     *
     * @throws 
     * @author Marc-Olivier Bazin-Maurice
     * @return
     */ 
    function GenerateUnitaryPrograms(\FabplanConnection $db, ?int $modelId, ?int $typeNo) : void
    {
        try
        {
            $db->getConnection()->beginTransaction();
            
            // Sélectionner les modèles et types et les mettre à jour.
            $modelsToUpdate = null;
            if($modelId === null)
            {
                $modelsToUpdate = (new \ModelController($db))->getModels();
            }
            else
            {
                $modelsToUpdate = array(\Model::withID($db, $modelId));
            }
            
            $typesToUpdate = null;
            if($typeNo === null)
            {
                $typesToUpdate = (new \TypeController($db))->getTypes();
            }
            else
            {
                $typesToUpdate = array(\Type::withID($db, $typeNo));
            }
            
            /* \var $type \Type */
            foreach($typesToUpdate as $typeToUpdate)
            {
                /* \var $model \Model */
                foreach($modelsToUpdate as $modelToUpdate)
                {
                    $modelTypeGeneric = (new \ModelTypeGeneric($modelToUpdate, $typeToUpdate))->loadParameters($db);
                    $model = \Model::withID($db, $modelTypeGeneric->getModel()->getId());
                    $type = \Type::withImportNo($db, $modelTypeGeneric->getType()->getImportNo());
                    $name = getUnitaryProgramName($model, $type);
                    $test = \Test::fromModelTypeGeneric($modelTypeGeneric)->setName($name);
                    generateSingleUnitaryProgram($test, $model, $type);
                }
            }
            
            $db->getConnection()->commit();
            $db = null;
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
    }
    
    /**
     * Generate a unitary program for a model-type combination
     *
     * @param \Test $test A Test object.
     * @param \Model $model A model object for which a unitary program must be generated.
     * @param \Type $type A type object for which a unitary program must be generated.
     *
     * @throws 
     * @author Marc-Olivier Bazin-Maurice
     * @return 
     */ 
    function generateSingleUnitaryProgram(\Test $test, \Model $model, \Type $type) : void
    {
        // Les modèles 1 à 9 n'ont pas de programme par défaut.
        if($model->getId() > 0 && $model->getId() < 10)
        {
            return;
        }
        
        $generic = $type->getGeneric();
        
        // Créer le fichier mpr.
        $mpr = new \mprCutrite($_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/" . $generic->getFilename());
        try 
        {
            $mpr->makeMprFromTest($test, $generic->getParametersAsKeyDescriptionPairs());
			
            $i = 0;
            while($i < 5)
            {
                try
                {
                    // Créer le mpr dans le système de fichiers.
					$oldErrorReporting = error_reporting(E_ALL & ~E_WARNING);
                    $mpr->makeMprFile(getUnitaryProgramLocation($model, $type) . $test->getName());
					error_reporting($oldErrorReporting);
                    break;
                }
                catch (\Exception $e)
                {
                    if($i < 5)
                    {
                        // Réessayer
                        usleep(500000);
                        $i++;
                    }
                    else
                    {
                        // Après 5 tentatives ratées, le programme échoue.
                        throw $e;
                    }
                }
            }
        }
        catch(\MprExpression\UndefinedVariableException $e)
        {
            if($e->getVariableName() !== "erreur")
            {
                $message = "Generating unitary program for \"{$test->getName()}\": " . $e->getMessage();
                throw new \Exception($message, $e->getCode(), $e);
            }
            else 
            {
                // Failing is the intended behavior but doesn't require further treatment.
            }
        }
    }
    
    /**
     * Return the unitary program standard filename
     *
     * @param \Model $model The model of the unitary program
     * @param \Type $type The type of the unitary program
     * 
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The standard filename for this unitary program
     */ 
    function getUnitaryProgramName(\Model $model, \Type $type) : string
    {
        return \FileFunctions\PathSanitizer::sanitize(
            "{$type->getDescription()}_{$model->getDescription()}.mpr", 
            array(
                "fileNameMode" => true,
                "allowSlashesInFilename" => false,
                "transliterate" => true,
                "fullyPortable" => true,
                "simplify" => false,
            )
        );
    }
    
    /**
     * Create if necessary and return the standard location of the unitary program file for this unitary program
     * 
     * @param \Model $model The model of the unitary program
     * @param \Type $type The type of the unitary program
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The full path to the location specific to this unitary program
     */ 
    function getUnitaryProgramLocation(\Model $model, \Type $type) : string
    {
        $relativeLocation = $type->getImportNo() . " - " . $type->getDescription() . "/" . $model->getDescription() . "/";
        $fullLocation = \FileFunctions\PathSanitizer::sanitize(_UNITARYPROGRAMSDIRECTORY . $relativeLocation);
        
        // Créer l'emplacement du fichier si requis
        if (!is_dir($fullLocation))
        {
            mkdir($fullLocation, 0777, true);
        }
        
        return $fullLocation;
    }
?>