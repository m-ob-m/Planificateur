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
        require_once __DIR__ . '/../../../lib/mpr/mprCutRite.php';  // Créateur de MPR pour CutRite
        require_once __DIR__ . '/../../test/controller/testController.php'; //Contrôleur de Test
        require_once __DIR__ . '/../../model/controller/modelController.php'; //Contrôleur de Modele
        require_once __DIR__ . '/../../type/controller/typeController.php'; //Contrôleur de Type
        require_once __DIR__ . '/../../generic/controller/genericController.php'; //Contrôleur de Type
        require_once __DIR__ . '/../../varmodtypegen/controller/modelTypeGenericController.php'; //Contrôleur de Type
        require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
        require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
        require_once __DIR__ . "/../../../lib/fileFunctions/fileFunctions.php"; //Fonctions sur les fichiers
        
        // Initialize the session
        session_start();
                                
        // Check if the user is logged in, if not then redirect him to login page
        if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            {
                throw new \Exception("You are not logged in.");
            }
            else
            {
                header("location: /Planificateur/lib/account/logIn.php");
            }
            exit;
        }
    
        // Closing the session to let other scripts use it.
        session_write_close();

        set_time_limit(3600); // Pour éviter les erreurs de dépassement du temps alloué, on augmente le temps alloué.

        $lockName = sys_get_temp_dir() . "/MAJModeleUnitaire.tmp";
        if(!$lock = fopen($lockName, 'c'))
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
        
        GenerateUnitaryPrograms($modelId, $typeNo);
        
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
            
            if($responseArray["status"] === "success")
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
     * @param int $modelId The model ID for which unitary programs must be generated (null means all)
     * @param int $typeId The type ID for which unitary programs must be generated (null means all)
     *
     * @throws 
     * @author Marc-Olivier Bazin-Maurice
     * @return
     */ 
    function GenerateUnitaryPrograms(?int $modelId, ?int $typeNo) : void
    {
        $modelTypeGenericsToUpdate = array();
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            
            // Sélectionner les modèles et types et les mettre à jour.
            $modelsToUpdate = null;
            if($modelId === null)
            {
                $modelsToUpdate = (new \ModelController())->getModels();
            }
            else
            {
                $modelsToUpdate = array(\Model::withID($db, $modelId));
            }
            
            $typesToUpdate = null;
            if($typeNo === null)
            {
                $typesToUpdate = (new \TypeController())->getTypes();
            }
            else
            {
                $typesToUpdate = array(\Type::withID($db, $typeNo));
            }
            
            /* \var $type \Type */
            foreach($typesToUpdate as $type)
            {
                /* \var $model \Model */
                foreach($modelsToUpdate as $model)
                {
                    $modelTypeGeneric = (new \ModelTypeGeneric($model, $type))->loadParameters($db);
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
        $mpr = new \mprCutrite(__DIR__ . "/../../../lib/" . $generic->getFilename());
        $mpr->extractMprBlocks();
        try 
        {
            $mpr->makeMprFromTest($test, $generic->getParametersAsKeyDescriptionPairs());
            $mpr->makeMprFile(getUnitaryProgramLocation($model, $type) . $test->getName());
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