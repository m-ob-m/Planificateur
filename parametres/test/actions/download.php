<?php 
/**
 * \name		download
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2017-11-01
 *
 * \brief 		Génère un fichier unitaire MPR pour un Test particulier.
 * \details 	Génère un fichier unitaire MPR pour un Test particulier.
 */

    include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
    include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
    include_once __DIR__ . '/../../../lib/mpr/mprCutRite.php';  		// Createur de MPR pour CutRite
    include_once __DIR__ . '/../controller/testController.php'; //Controlleur de TestType
    include_once __DIR__ . '/../../generic/controller/genericController.php';	//
   
    //Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    try 
    {
        $input =  json_decode(file_get_contents("php://input"));
        
        $testId = isset($input->testId) ? $input->testId : null;
        if($testId === null)
        {
            throw new Exception("Aucun identifiant de test fourni. La génération du programme unitaire a été annulée.");
        }
        
        GenerateTestProgram((new TestController())->getTest($testId));
        
        // Retour au javascript
        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = $testId;
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
     * Main unitary program generation function that creates a test that is used to subsequently make every unitary program
     *
     * @param Test $test The Test for which the machining program is requested.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return
     */
    function GenerateTestProgram(Test $test) : void
    { 
        $defaultName = $test->getModelId() . "_" . $test->getTypeNo() . "_" .  $test->getId();
        $mprname = ($test->getName() <> "") ? ($test->getName()) : $defaultName;
        $filepath = _TESTDIRECTORY . "{$mprname}.mpr";
        
        // Vérification si programme générique utilisé
        if($test->getModelId() === 1)
        {
            throw new \Exception("Vous ne pouvez pas télécharger des modèles génériques!");
        }
        elseif($test->getModelId() === 2)
        {
            $mpr = new mprCutrite(__DIR__ . "/../../../lib/" . (new GenericController())->getGenerics()[0]->getFilename());
            $mpr->makeMprFromTest($test, array());
            $mpr->makeMprFile($filepath);
        }
        else 
        {
            $generic = (new GenericController())->getGeneric($test->getGenericId());
            $parametersDescription = getParametersDescriptionsTable($generic);
            $mpr = new mprCutrite(__DIR__ . "/../../../lib/" . $generic->getFilename());
            $mpr->extractMprBlocks();
            $mpr->makeMprFromTest($test, $parametersDescription);
            $mpr->makeMprFile($filepath);
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
?>