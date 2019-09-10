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
        require_once __DIR__ . '/../model/batch.php';		// Modèle d'une Job
    
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
        
        $batch = null;
        try
        {
            $db = new \FabPlanConnection();
            $db->getConnection()->beginTransaction();
            $stmt = $db->getConnection()->prepare("
                SELECT `b`.`id_batch` AS `id`, `b`.`nom_batch` AS `name`, `b`.`panneaux` AS `pannels`, SUM(`jtp`.`quantite`) AS `quantity` 
                FROM `batch` AS `b`
                INNER JOIN `fabplan`.`batch_job` AS `bj` ON `b`.`id_batch` = `bj`.`batch_id` 
                INNER JOIN `job` AS `j` ON `bj`.`job_id` = `j`.`id_job`
                INNER JOIN `job_type` AS `jt` ON `j`.`id_job` = `jt`.`job_id`
                INNER JOIN `job_type_porte` AS `jtp` ON `jt`.`id_job_type` = `jtp`.`job_type_id`
                WHERE `b`.`etat_mpr` IN ('A', 'P')
                GROUP BY `b`.`id_batch`, `b`.`nom_batch`, `b`.`panneaux` 
                ORDER BY FIELD(`b`.`etat_mpr`, 'P', 'A'), `quantity` ASC
                LIMIT 1;
            ");
            $stmt->execute();
            
            // Fetch results
            if($row = $stmt->fetch())
            {
                $batch = (object) array("id" => $row["id"], "name" => $row["name"], "pannels" => $row["pannels"]);
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
        $responseArray["success"]["data"] = $batch;
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