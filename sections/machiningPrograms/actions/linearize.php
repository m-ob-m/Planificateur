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

        set_time_limit(40 * 60);

        $data =  json_decode(file_get_contents("php://input"));
        
        $outputFolder = "C:\\PROGRAMMES_V200\\__DEV\\linearized\\";
        
        if (!file_exists($outputFolder))
        {
            mkdir($outputFolder, 0777, true);
        }
        
        // Vérification des paramètres
        $inputFileContents = $data->inputFile ?? null;
        $outputFileName = \FileFunctions\PathSanitizer::sanitize($data->outputFileName ?? null, array("fileNameMode" => true));
        $variables = $data->variables ?? array(); 
        
        if($inputFileContents === null)
        {
            throw new \Exception("No input file was provided.");
        }
        
        if($outputFileName === null)
        {
            throw new \Exception("No output filename was provided.");
        }
        
        $timestamp = round(microtime(true) * 1000);
        $inputFilePath = sys_get_temp_dir() . "\\InputMpr{$timestamp}.mpr";
        file_put_contents($inputFilePath, mb_convert_encoding($inputFileContents, "ISO-8859-1", "UTF-8"));
        try
        {
            \MprMerge::linearize($inputFilePath, $outputFolder . $outputFileName, $variables);
        }
        catch(\Exception $e)
        {
            throw $e;
        }
        finally
        {
            unlink($inputFilePath);
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