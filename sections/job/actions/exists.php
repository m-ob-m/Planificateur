<?php
    /**
     * \name		getSummary.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-06-05
     *
     * \brief 		Détermine si une job existe.
     * \details     Détermine si une job existe.
     */
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
        require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
        require_once __DIR__ . '/../model/job.php';		// Modèle d'une Job
    
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

        $db = new \FabPlanConnection();
        
        // Vérification des paramètres
        $jobName = $_GET["name"] ?? null;
        $jobId = $_GET["id"] ?? null;
        
        $job = null;
        try
        {
            $db = new \FabPlanConnection();
            $db->getConnection()->beginTransaction();
            if(!empty($jobId))
            {
                // Get job by id
                $job = \Job::withID($db, $jobId);
            }
            elseif(!empty($jobName))
            {
                // Get job by name
                $job = \Job::withName($db, $jobName);
            }
            else
            {
                throw new \Exception("No job identifier provided.");
            }
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
        $responseArray["success"]["data"] = ($job !== null);
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