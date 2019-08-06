<?php
    /**
     * \name		save.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-07-16
     *
     * \brief 		Sauvegarde un Batch
     * \details     Sauvegarde un Batch
     */
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {
        require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
        require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
        require_once __DIR__ . '/../controller/batchController.php'; // Contrôleur d'un Batch
        require_once __DIR__ . '/../../job/controller/jobController.php'; // Contrôleur d'un Batch
    
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

        $input =  json_decode(file_get_contents("php://input"));
        
        // Vérification des paramètres
        $id = preg_match("/^\d+$/", $input->id ?? null) ? intval($input->id) : null;
        $name = $input->name ?? null;
        $startDate = $input->startDate ?? null;
        $endDate = $input->endDate ?? null;
        $fullDay = $input->fullDay ?? null;
        $materialId = $input->material ?? null;
        $boardSize = $input->boardSize ?? null;
        $status = $input->status ?? null;
        $comments = $input->comments ?? null;
        $jobIds = $input->jobIds ?? null;
        
        // Get the information
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            
            $batch = \Batch::withID($db, $id, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
            $batch = ($batch === null) ? new \Batch() : $batch;
            
            $batch->setMaterialId($materialId)->setBoardSize($boardSize)->setName($name)->setStart($startDate)
                ->setEnd($endDate)->setFullDay($fullDay)->setComments($comments)->setStatus($status)->setMprStatus("N")
                ->setJobs([]);
            foreach($jobIds as $jobId)
            {
                $batch->addJob(\Job::withID($db, $jobId));
            }
            $batch->setCarrousel()->save($db);
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