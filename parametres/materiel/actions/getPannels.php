<?php
/**
 * \name		getPannels.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-10-10
 *
 * \brief 		Récupère la liste de panneaux pour un matériel donné
 * \details     Récupère la liste de panneaux pour un matériel donné
 */

// INCLUDE
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/../controller/materielCtrl.php';

//Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    $db = new FabPlanConnection();
    
    $id = $_GET["materialId"] ?? null;
    $accessDbPath = __DIR__ . "/../temp/mmatv9.mdb";
    $material = \Materiel::withID($db, $id);
    $boards = array();
    
    if($material !== null)
    {
        if(!file_exists(dirname($accessDbPath)))
        {
            mkdir(dirname($accessDbPath));
        }
        
        if(file_exists(MMATV9_MDB))
        {
            if(!file_exists($accessDbPath))
            {
                copy(MMATV9_MDB, $accessDbPath);
            }
            elseif(time() - filemtime($accessDbPath) >= 60 * 60 || filemtime($accessDbPath) !== filemtime(MMATV9_MDB))
            {
                unlink($accessDbPath);
                copy(MMATV9_MDB, $accessDbPath);
            }
        }
        else
        {
            throw new \Exception("Cut Rite's board library file \"mmatv9.mdb\" doesn't exist.");
        }
        
        $accessDb =  new \PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ={$accessDbPath};", "", "", array());
        $stmt = $accessDb->prepare("
            SELECT [b].[BoardsCode] AS [BoardCode]
            FROM [Boards] AS [b]
            WHERE [b].[BoardsMaterialCode] = :materialCode;
        ");
        $stmt->bindValue(":materialCode", $material->getCodeCutRite(), PDO::PARAM_STR);
        $stmt->execute();
        
        while($result = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            array_push($boards, preg_replace("/^{$material->getCodeCutRite()}_/", "", $result["BoardCode"], 1));
        }
        natsort($boards);
        
        $accessDb = null;
    }
    else 
    {
        throw new \Exception("Il n'y a aucun matériel associé à l'identifiant numérique unique \"{$id}\".");
    }
    
    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = array_values($boards);
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