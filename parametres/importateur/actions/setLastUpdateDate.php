<?php 
    /**
     * \name		create.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2019-04-25
     *
     * \brief 		Gets last update date
     * \details     Gets last update date
     */

    // INCLUDE
    include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
    include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
    include_once __DIR__ . '/../model/importateur.php';

    //Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        $input =  json_decode(file_get_contents("php://input"));
        
        // Vérification des paramètres
        $lastUpdateDate = $input->lastUpdateDate ?? null;
        
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            \Importateur::retrieve($db)->setLastUpdateTimestamp($lastUpdateDate)->save($db);
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
        $responseArray["success"]["data"] = null;
    }
    catch(\Exception $e)
    {
        $responseArray["status"] = "failure";
        $responseArray["failure"]["message"] = $e->getMessage();
    }
    finally
    {
        echo json_encode($responseArray);
    }
?>