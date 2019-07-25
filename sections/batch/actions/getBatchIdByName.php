<?php
    /**
     * \name		getJobs.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-07-12
     *
     * \brief 		Récupère les Job contenus dans un Batch
     * \details     Récupère les Job contenus dans un Batch
     */

    require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
    require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
    require_once __DIR__ . '/../controller/batchController.php'; // Contrôleur d'un Batch
    require_once __DIR__ . '/../../job/controller/jobController.php'; // Contrôleur d'un Batch

    /* 
     * No session required to open this page! Be careful concerning what you put here. 
     * Advanced user account control might become available in a later release.
     */
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        $db = new FabPlanConnection();
        
        // Vérification des paramètres
        if(isset($_GET["name"]) && trim($_GET["name"]) !== "")
        {
            $name = trim($_GET["name"]);
        }
        else
        {
            throw new \Exception("An invalid batch name was provided.");
        }

        $batch = \Batch::withName($db, $name);
        if($batch === null)
        {
            throw new \Exception("There is no batch with the name \"{$name}\".");
        }

        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = $batch->getId();
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