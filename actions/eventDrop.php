<?php
    /**
     * \filename	eventDrop.php
     *
     * \brief 		Change la date d'un évènement lors d'un glisser-déposer sur le calendrier.
     *
     * \date		2017-01-31
     * \version 	1.0
     */

    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/batch/model/batch.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/controller/planificateur.php";

    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

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

    try
    {   
        $input = json_decode(file_get_contents("php://input"));

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
