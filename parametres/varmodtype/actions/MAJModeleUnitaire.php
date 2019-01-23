<?php
    /**
     * \name		MAJModeleUnitaire
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-03-16
     *
     * \brief 		Met à jour les programmes unitaires des combinaisons modèle-type spécifiées
     */
    
    include_once __DIR__ . '/../../../lib/mpr/mprCutRite.php';  // Créateur de MPR pour CutRite
    include_once __DIR__ . '/../../test/controller/testController.php'; //Contrôleur de Test
    include_once __DIR__ . '/../../model/controller/modelController.php'; //Contrôleur de Modele
    include_once __DIR__ . '/../../type/controller/typeController.php'; //Contrôleur de Type
    include_once __DIR__ . '/../../generic/controller/genericController.php'; //Contrôleur de Type
    include_once __DIR__ . '/../../varmodtypegen/controller/modelTypeGenericController.php'; //Contrôleur de Type
    include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
    include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
    include_once __DIR__ . "/../../../lib/fileFunctions/fileFunctions.php"; //Fonctions sur les fichiers
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    set_time_limit(3600); // Pour éviter les erreurs de dépassement du temps alloué, on augmente le temps alloué.
    
    $lock = null;
    try
    {
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
            /* Boohoo! Quelque chose nous échappe. Allez, retournons quand-même notre réponse à l'utilisateur. */
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
        // Pour enlever les notices du eval()
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        
        //Sélectionner les modèles et types et les mettre à jour.
        $modelsToUpdate = null;
        if($modelId === null)
        {
            $modelsToUpdate = (new ModelController())->getModels();
        }
        else
        {
            $modelsToUpdate = array((new ModelController())->getModel($modelId));
        }
        
        $typesToUpdate = null;
        if($typeNo === null)
        {
            $typesToUpdate = (new TypeController())->getTypes();
        }
        else
        {
            $typesToUpdate = array((new TypeController())->getTypeByImportNo($typeNo));
        }
        
        loopGenerateUnitaryPrograms($modelsToUpdate, $typesToUpdate);
    }
    
    
    /**
     * Loop through model-type combinations and generate their unitary program
     *
     * @param Modele array $modelsToUpdate An array containing all the Model objects for which unitary programs must be generated.
     * @param Type array $typesToUpdate An array containing all the Type objects for which unitary programs must be generated.
     *
     * @throws 
     * @author Marc-Olivier Bazin-Maurice
     * @return 
     */ 
    function loopGenerateUnitaryPrograms(array $modelsToUpdate, array $typesToUpdate) : void
    {
        if(empty($modelsToUpdate) || empty($typesToUpdate))
        {
            throw new Exception("La demande de mise à jour des couples modèle-type n'affecte aucun couple.");
        }
        
        foreach ($typesToUpdate as $type)
        {
            $generic = (new GenericController())->getGeneric($type->getGenericId());
            foreach($modelsToUpdate as $model)
            {
                $name = getUnitaryProgramName($model, $type);
                $modelTypeGeneric = (new ModelTypeGenericController())->getModelTypeGeneric($model->getId(), $type->getImportNo());
                $test = Test::fromModelTypeGeneric($modelTypeGeneric)->setName($name);
                generateSingleUnitaryProgram($test, $model, $type, $generic);
            }
        }
    }
    
    /**
     * Generate a unitary program for a model-type combination
     *
     * @param Test $test A Test object.
     * @param Model $model A model object for which a unitary program must be generated.
     * @param Type $type A type object for which a unitary program must be generated.
     * @param Generic $generic The generic object to use to produce this Test.
     *
     * @throws 
     * @author Marc-Olivier Bazin-Maurice
     * @return 
     */ 
    function generateSingleUnitaryProgram(Test $test, Model $model, Type $type, Generic $generic) : void
    {
        // Les modèles 1 à 9 n'ont pas de programme par défaut.
        if($model->getId() > 0 && $model->getId() < 10)
        {
            return;
        }
        
        // Créer le fichier mpr.
        $mpr = new mprCutrite($_SERVER['DOCUMENT_ROOT'] . "\\" . _GENERICPROGRAMSDIRECTORY . $generic->getFilename());
        $mpr->extractMprBlocks();
        try 
        {
            $mpr->makeMprFromTest($test, getParametersDescriptionsTable($generic));
            $mpr->makeMprFile(getUnitaryProgramLocation($model, $type) . $test->getName());
        }
        catch(\MprExpression\UndefinedVariableException $e)
        {
            if($e->getVariableName() !== "erreur")
            {
                $message = "Generating unitary program for \"{$test->getName()}\": " . $e->getMessage();
                throw new \Exception($message, $e->getCode(), $e);
            }
        }
    } 
    
    /**
     * Return the parameters description table for the specified Generic
     *
     * @param Generic $generic The Generic of this unitary program
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return array The parameters description table
     */ 
    function getParametersDescriptionsTable(Generic $generic) : array
    {
        $descriptionTable = array();
        foreach($generic->getGenericParameters() as $parameter)
        {
            $descriptionTable[$parameter->getKey()] = $parameter->getDescription();
        }
        return $descriptionTable;
    }
    
    /**
     * Return the unitary program standard filename
     *
     * @param Model $model The model of the unitary program
     * @param Type $type The type of the unitary program
     * 
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The standard filename for this unitary program
     */ 
    function getUnitaryProgramName(Model $model, Type $type) : string
    {
        return \FileFunctions\PathSanitizer::sanitize(
            "{$type->getDescription()}_{$model->getDescription()}.mpr", 
            array(
                "allowSlashesInFilename" => false,
                "transliterate" => true,
                "fullyPortable" => true,
                "simplify" => false,
                "inputPathDelimiter" => "" /* This is a filename. */
            )
        );
    }
    
    /**
     * Create if necessary and return the standard location of the unitary program file for this unitary program
     * 
     * @param Model $model The model of the unitary program
     * @param Type $type The type of the unitary program
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The full path to the location specific to this unitary program
     */ 
    function getUnitaryProgramLocation(Model $model, Type $type) : string
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