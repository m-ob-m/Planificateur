<?php
    /**
     * \filename	eventDrop.php
     *
     * \brief 		Change la date d'un évènement lors d'un glisser-déposer sur le calendrier.
     *
     * \date		2017-01-31
     * \version 	1.0
     */

    require_once $SERVER["DOCUMENT_ROOT"] . '/Planificateur/lib/config.php';	// Fichier de configuration
    require_once $SERVER["DOCUMENT_ROOT"] . '/Planificateur/lib/connect.php';	// Classe de connection à la base de données
    require_once $SERVER["DOCUMENT_ROOT"] . '/Planificateur/sections/batch/model/batch.php';	// Modèle d'une batch
    require_once $SERVER["DOCUMENT_ROOT"] . '/Planificateur/controller/planificateur.php';		// Classe controleur de la vue

    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {   
        $input = json_decode(file_get_contents('php://input'));
        
        $db = new \FabPlanConnection();

        $start = null;
        $end = null;
        try
        {
            $db->getConnection()->beginTransaction();
            $batch = \Batch::withID($db, $input->batchId, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);

            //Nouveaux débuts et fins de Batch
            if($input->allDay=="true")
            {	
                $start = \DateTime::createFromFormat("Y-m-d", str_replace("T", " ", $input->debut));
                $end = \DateTime::createFromFormat("Y-m-d", str_replace("T", " ", $input->fin));
                $batch->setStart($start->format("Y-m-d H:i:s"));
                $batch->setEnd($end->format("Y-m-d H:i:s"));
            } 
            else 
            {
                $start = \DateTime::createFromFormat("Y-m-d H:i:s", str_replace("T", " ", $input->debut));
                $end = \DateTime::createFromFormat("Y-m-d H:i:s", str_replace("T", " ", $input->fin));
                $batch->setStart($start->format("Y-m-d H:i:s"));
                $batch->setEnd($end->format("Y-m-d H:i:s"));
            }

            $batch->save($db);
            $db->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $db->getConnection()->rollBack();
            throw $e;
        }
        finally
        {
            $db = null;
        }

        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = \PlanificateurController::couleurEtat($batch->getStatus(), $end);
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
