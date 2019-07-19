<?php 
    /**
     * \name		getJobTypes.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-08-20
     *
     * \brief 		Get JobTypes for a specified job id
     * \details     Get JobTypes for a specified job id
     */
    
    // INCLUDE
    include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
    include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
    include_once __DIR__ . '/../controller/jobController.php'; // Classe contrôleur de la classe Job
    include_once __DIR__ . '/../../../parametres/model/controller/modelController.php'; // Classe contrôleur de la classe Model
    include_once __DIR__ . '/../../../parametres/type/controller/typeController.php'; // Classe contrôleur de la classe Type
    include_once __DIR__ . '/../../../parametres/generic/controller/genericController.php'; // Classe contrôleur de la classe Generic
    
    //Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    try
    {        
        // Vérification des paramètres
        $jobId = $_GET["jobId"] ?? null;
        
        if(intval($jobId) != floatval($jobId) || !is_numeric($jobId) || intval($jobId) <= 0)
        {
            throw new \Exception("L'identifiant unique de Job fourni n'est pas un entier numérique positif.");
        }
        
        $job = null;
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            $job = \Job::withID($db, $jobId);
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
        $responseArray["success"]["data"] = $job;
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