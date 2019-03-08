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

    include_once __DIR__ . "/../../../lib/mpr/mprmerge/MprMerge.php";
    include_once __DIR__ . "/../../../lib/fileFunctions/fileFunctions.php";
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    set_time_limit(30 * 60);
    $filesToDelete = array();
    try
    {
        $data =  json_decode(file_get_contents("php://input"));
        
        $outputFolder = "C:\\PROGRAMMES_V200\\__DEV\\merged\\";
        
        if (!file_exists($outputFolder))
        {
            mkdir($outputFolder, 0777, true);
        }
        
        // Vérification des paramètres
        $inputFiles = $data->inputFiles ?? null;
        $outputFileName = \FileFunctions\PathSanitizer::sanitize($data->outputFileName ?? null, array("inputPathDelimiter" => ""));
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
            $timestamp = round(microtime(true) * 1000);
            $inputFile = sys_get_temp_dir() . "\\InputMpr{$timestamp}.mpr";
            file_put_contents($inputFile, mb_convert_encoding($inputFileContents, "ISO-8859-1", "UTF-8"));
            array_push($filesToDelete, $inputFile);
        }
        
        try
        {
            \MprMerge::merge($inputFiles, $outputFolder . $outputFileName);
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