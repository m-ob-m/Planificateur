<?php
/**
 * \name		save.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-04-18
 *
 * \brief 		Retourne les types associés à un générique
 * \details     Retourne les types associés à un générique
 */

// INCLUDE
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/../../generic/model/generic.php'; // Modèle de Generic

//Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

$associatedTypes = array();
try
{
    $genericId = intval($_GET["genericId"]) ?? null;
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $generic = \Generic::withID($db, $genericId);
        if($generic !== null)
        {
            $associatedTypes = $generic->getAssociatedTypes();
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
    $responseArray["success"]["data"] = $associatedTypes;
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