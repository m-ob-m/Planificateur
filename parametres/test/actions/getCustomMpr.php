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
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/test/controller/testController.php";

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
        
        // Vérification des paramètres
        $id = $_GET["id"] ?? null;
        if($id !== null && $id !== "")
        {
            try
            {
                $db->getConnection()->beginTransaction();
                $test = \Test::withID($db, $id);
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
        }
        else 
        {
            throw new \Exception("There is no test with the unique numerical identifier \"{$id}\".");
        }
        
        // Retour au javascript
        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = ($test->getModel()->getId() === 2) ? $test->getFichierMpr() : null;
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