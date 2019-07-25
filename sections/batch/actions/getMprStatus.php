<?php
    /**
     * \name		getStatus.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-09-11
     *
     * \brief 		Récupère le statut d'une Batch
     * \details     Récupère le statut d'une Batch
     */
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        require_once __DIR__ . '/../controller/batchController.php'; // Contrôleur d'un Batch

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

        $batchId = isset($_GET["batchId"]) ? intval($_GET["batchId"]) : null;
        
        // Get the information
        $batch = null;
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            $batch = \Batch::withID($db, $batchId);
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
        $responseArray["success"]["data"] = $batch->getMprStatus();
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