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
    
    // INCLUDE
    include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
    include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
    include_once __DIR__ . '/../controller/testController.php';
    
    //Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    try
    {
        // Vérification des paramètres
        $id = $_GET["id"] ?? null;
        if($id !== null && $id !== "")
        {
            $db = new \FabPlanConnection();
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