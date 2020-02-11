<?php
    /**
     * \name		save.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-04-18
     *
     * \brief 		Retourne les types associés à un générique
     * \details     Retourne les types associés à un générique
     */

     // Structure de retour vers javascript
     $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        // INCLUDE
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/generic/model/generic.php";

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

        $associatedTypes = array();

        $genericId = intval($_GET["genericId"]) ?? null;
        try
        {
            $db->getConnection()->beginTransaction();
            $generic = \Generic::withID($db, $genericId);
            if($generic !== null)
            {
                $associatedTypes = $generic->getAssociatedTypes($db);
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
        $responseArray["success"]["data"] = $associatedTypes;
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