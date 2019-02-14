<?php
/**
 * \name		retrieveBetweenTwoDates.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-10-15
 *
 * \brief 		Retourne la liste des tests créés ou modifiés pour la dernière fois entre deux dates.
 * \details 	Retourne la liste des tests créés ou modifiés pour la dernière fois entre deux dates.
 */

// INCLUDE
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $db = new \FabPlanConnection();
    
    // Vérification des paramètres
    $startDate = $_GET["startDate"] ?? null;
    $endDate = $_GET["endDate"] ?? null;
    
    if($startDate == null || $endDate === null)
    {
        throw new \Exception("No start date and/or end date provided.");
    }
    
    $stmt = $db->getConnection()->prepare("
        SELECT `t`.`id` AS `id`, `t`.`name` AS `name`, `dm`.`description_model` AS `modelName`, `dt`.`description` AS `typeName`, 
            `g`.`Filename` AS `genericFilename`, `t`.`estampille` AS `timestamp` 
        FROM `fabplan`.`test` AS `t` 
        INNER JOIN `fabplan`.`door_model` AS `dm` ON `dm`.`id_door_model` = `t`.`door_model_id`
        INNER JOIN `fabplan`.`door_types` AS `dt` ON `dt`.`importNo` = `t`.`type_no`
        INNER JOIN `fabplan`.`generics` AS `g` ON `g`.`id` = `t`.`generic_id`
        WHERE `t`.`estampille` >= :startDate AND `t`.`estampille` <= :endDate
        ORDER BY `t`.`estampille` DESC
        FOR SHARE;
    ");
    $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
    $stmt->execute();
    
    // Get the information
    $tests = array();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
        array_push(
            $tests,
            (object) array(
                "id" => $row["id"],
                "name" => $row["name"],
                "model" => $row["modelName"],
                "type" => $row["typeName"],
                "generic" => $row["genericFilename"],
                "timestamp" => $row["timestamp"]
            )
        );
    }
    
    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = $tests;
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