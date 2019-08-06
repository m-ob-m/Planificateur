<?php 
    /**
     * \name		save.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-04-13
     *
     * \brief 		Sauvegarde un test
     * \details     Sauvegarde un test
     */

    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        // INCLUDE
        require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
        require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
        require_once __DIR__ . '/../controller/testController.php';
        require_once __DIR__ . "/../../../lib/fileFunctions/fileFunctions.php";	// Classe de fonctions liées aux fichiers
        
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
        
        $input =  json_decode(file_get_contents("php://input"));
        
        // Vérification des paramètres
        $id = (isset($input->testId) ? $input->testId : null);
        $name = (isset($input->testName) ? $input->testName : null);
        $modelId = (isset($input->modelId) ? $input->modelId : null);
        $typeNo = (isset($input->typeNo) ? $input->typeNo : null);
        $mpr = (isset($input->mpr) ? $input->mpr : null);
        $newParameters = (isset($input->parameters) ? $input->parameters : array());
        
        $name = pathinfo(
            \FileFunctions\PathSanitizer::sanitize(
                "{$name}.mpr", 
                array(
                    "fileNameMode" => true,
                    "allowSlashesInFilename" => false,
                    "transliterate" => true,
                    "fullyPortable" => true
                )
            ), 
            PATHINFO_FILENAME
        );
        
        if($name === "" || $name === null)
        {
            throw new \Exception("Le nom de Test fourni \"{$name}\" est invalide.");
        }
        
        $test = null;
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            if($id === null)
            {
                $test = new \Test();
            }
            else
            {
                $test = \Test::withID($db, $id, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
                if($test === null)
                {
                    throw new \Exception("Il n'y a aucun test possédant l'identifiant numérique unique \"{$id}\".");
                }
            }
            
            $model = \Model::withID($db, $modelId);
            if($model === null)
            {
                throw new \Exception("Il n'y a aucun modèle possédant l'identifiant unique \"{$modelId}\".");
            }
            
            $type = \Type::withImportNo($db, $typeNo);
            if($type === null)
            {
                throw new \Exception("Il n'y a aucun type possédant le numéro d'importation \"{$typeNo}\".");
            }
            
            $test->setName($name)->setFichierMpr($mpr)->setModel($model)->setType($type);
            saveModifiedParameters($newParameters, $test->save($db), $db);
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
        $responseArray["success"]["data"] = $test->getId();
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
     * Saves the modified parameters to the database.
     *
     * @param array $newParameters The parameters to save.
     * @param \Test $test A test from the database (must have an id)
     * @param \FabPlanConnection $db A connection to the database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return
     */ 
    function saveModifiedParameters(array $newParameters, \Test $test, \FabPlanConnection $db)
    {
        $testId = $test->getId();
        foreach($newParameters as $parameter)
        {
            $testParameter = new \TestParameter($testId, $parameter->key, $parameter->value, $parameter->description);
            if($parameter->value === null)
            {
                $testParameter->delete($db);
            }
            else
            {
                $testParameter->save($db);
            }
        }
    }
?>