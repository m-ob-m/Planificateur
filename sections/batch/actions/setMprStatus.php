<?php
/**
 * \name		getStatus.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2019-05-14
 *
 * \brief 		Assigne le statut mpr d'une Batch
 * \details     Assigne le statut mpr d'une Batch
 */

include_once __DIR__ . '/../model/batch.php'; // Modèle d'une Batch

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $input =  json_decode(file_get_contents("php://input"));
    
    $batchId = $input->id ?? null;
    $mprStatus = $input->mprStatus ?? "";
    
    // Get the information
    $batch = null;
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $batch = \Batch::withID($db, $batchId, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
        if($batch !== null)
        {
            $batch->setMprStatus($mprStatus)->save($db);
        }
        else
        {
            throw new \Exception("There is no batch with unique numerical identifier \"{$batchId}\".");
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
    $responseArray["success"]["data"] = null;
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