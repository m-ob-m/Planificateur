<?php
    /**
     * \name		save.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-04-18
     *
     * \brief 		Sauvegarde un Material
     * \details     Sauvegarde un Material
     */

    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        // INCLUDE
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/material/controller/materialCtrl.php";
        
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
        
        $input =  json_decode(file_get_contents("php://input"));
        
        // Vérification des paramètres
        $id = (isset($input->id) ? $input->id : null);
        $siaCode = (isset($input->siaCode) ? $input->siaCode : null);
        $cutRiteCode = (isset($input->cutRiteCode) ? $input->cutRiteCode : null);
        $description = (isset($input->description) ? $input->description : null);
        $thickness = (isset($input->thickness) ? $input->thickness : null);
        $woodType = (isset($input->woodType) ? $input->woodType : null);
        $grain = (isset($input->grain) ? $input->grain : null);
        $isMDF = (isset($input->isMDF) ? $input->isMDF : null);
        
        $material = null;
        try
        {
            $db->getConnection()->beginTransaction();
            if($id === null)
            {
                $material = new \Material();
            }
            else
            {
                $material = \Material::withID($db, $id, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
                if($material === null)
                {
                    throw new \Exception("Il n'y a aucun matériel possédant l'identifiant numérique unique \"{$id}\".");
                }
            }
            
            $material
                ->setDescription($description)
                ->setCodeSIA($siaCode)
                ->setCodeCutRite($cutRiteCode)
                ->setEpaisseur($thickness)
                ->setEssence($woodType)
                ->setGrain($grain)
                ->setEstMDF($isMDF)
                ->save($db);
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
        $responseArray["success"]["data"] = $material->getId();
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