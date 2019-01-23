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
$db = new FabPlanConnection();

try
{
    $genericId = intval($_GET["genericId"]) ?? null;
    
    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = \Generic::withID($db, $genericId)->getAssociatedTypes();
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