<?php
    /**
     * \name		linearize.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-12-19
     *
     * \brief 		Simplifie un programme d'usinage mpr
     * \details     Simplifie un programme d'usinage mpr
     */
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    try
    {
        require_once __DIR__ . "/../../../lib/mpr/mprmerge/MprMerge.php";
        require_once __DIR__ . "/../../../lib/fileFunctions/fileFunctions.php";
        
        // Initialize the session
        session_start();
        
        // Check if the user is logged in, if not then redirect him to login page
        if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            header("location: /Planificateur/lib/account/logIn.php");
            exit;
        }
    
        session_write_close();

        set_time_limit(30 * 60);
        $filesToDelete = array();
        
        $data =  json_decode(file_get_contents("php://input"));
        
        $outputFolder = "C:\\PROGRAMMES_V200\\__DEV\\merged\\";
        
        if (!file_exists($outputFolder))
        {
            mkdir($outputFolder, 0777, true);
        }
        
        // Vérification des paramètres
        $inputFiles = $data->inputFiles ?? null;
        $outputFileName = \FileFunctions\PathSanitizer::sanitize($data->outputFileName ?? null, array("fileNameMode" => true,));
        $variables = $data->variables ?? array();
        
        if(!is_array($inputFiles) || empty($inputFiles))
        {
            throw new \Exception("No input file was provided.");
        }
        
        if($outputFileName === null)
        {
            throw new \Exception("No output filename was provided.");
        }
        
        
        foreach($inputFiles as &$inputFile)
        {
            $inputFileContents = $inputFile;
            $inputFile = tempnam(sys_get_temp_dir(), "InputMpr");
            file_put_contents($inputFile, mb_convert_encoding($inputFileContents, "ISO-8859-1", "UTF-8"));
            array_push($filesToDelete, $inputFile);
        }
        
        $mainFile = preg_replace("/(?<!\r)\n|\r(?!\n)/", "\r\n", file_get_contents($inputFiles[0]));
        $mainFileVariablesSection = array();
        preg_match("/\[001\r\n(.*?)\r\n\r\n/s", $mainFile, $mainFileVariablesSection);
        $globalSessionVariablesMatches = array();
        $dimensionsFilter = "/(?<=\A|\r\n)(?<key>LPX|LPY)=\"(?<value>.*?)\"\r\nKM=\"(?<description>.*?)\"(?=\z|\r\n)/";
        preg_match_all($dimensionsFilter, $mainFileVariablesSection[1], $globalSessionVariablesMatches, PREG_SET_ORDER);
        
        $globalSessionVariables = array();
        foreach($globalSessionVariablesMatches as $globalSessionVariablesMatch)
        {
            array_push(
                $globalSessionVariables, 
                new \MprVariable(
                    $globalSessionVariablesMatch["key"], 
                    $globalSessionVariablesMatch["value"], 
                    $globalSessionVariablesMatch["description"]
                )
            );
        }

        try
        {
            \MprMerge::merge($inputFiles, $outputFolder . $outputFileName, $globalSessionVariables);
        }
        catch(\Exception $e)
        {
            throw $e;
        }
        finally
        {
            foreach($filesToDelete as $fileToDelete)
            {
                unlink($fileToDelete);
            }
        }
        
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
        echo json_encode($responseArray);
    }
?>