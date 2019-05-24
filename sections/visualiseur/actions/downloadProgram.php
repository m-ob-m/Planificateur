<?php
/**
 * \name		fetchProperties.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-09-11
 *
 * \brief 		Fetch the interesting properties of a door (and its associated JobType, Job, etc.)
 * \details     Fetch the interesting properties of a door (and its associated JobType, Job, etc.)
 */

// INCLUDE
include_once __DIR__ . "/../../job/controller/jobController.php";

//Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{
    // Vérification des paramètres
    $jobTypePorteId = $_GET["jobTypePorteId"] ?? null;
    
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $jobTypePorte = \JobTypePorte::withID($db, $jobTypePorteId);
        $jobType = \JobType::withID($db, $jobTypePorte->getJobTypeId());
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
    
    $programName = "{$jobType->getModel()->getId()}_{$jobType->getType()->getImportNo()}_{$jobType->getId()}.mpr";
    
    if($programName === null)
    {
        throw new \Exception("Aucun nom de programme n'a été fourni.");
    }
    
    if(file_exists(CR_FABRIDOR . "SYSTEM_DATA/mpr/{$programName}"))
    {
        $sourceFileHandle = fopen(CR_FABRIDOR . "SYSTEM_DATA/mpr/{$programName}", "rb");
        if(!$sourceFileHandle)
        {
            throw new \Exception("Impossible d'ouvrir le fichier source {$programName}.");
        }
        
        $destinationFileHandle = fopen(__DIR__ . "/../temp/{$programName}", "wb");
        if(!$destinationFileHandle)
        {
            throw new \Exception("Impossible de créer le fichier de destination {$programName}.");
        }
        
        $mpr = fread($sourceFileHandle, filesize(CR_FABRIDOR . "SYSTEM_DATA/mpr/{$programName}"));
        $mpr = mb_convert_encoding($mpr, "ISO-8859-1", "UTF-8");
        $mpr = applyDimensionsToMpr($mpr, strval($jobTypePorte->getLength()), strval($jobTypePorte->getWidth()));
        fwrite($destinationFileHandle, $mpr);
        
        if(!fclose($destinationFileHandle))
        {
            throw new \Exception("Impossible de fermer le fichier de destination {$programName}.");
        }
        
        if(!fclose($sourceFileHandle))
        {
            throw new \Exception("Impossible de fermer le fichier source {$programName}.");
        }
    }
    else
    {
        throw new \Exception("Le fichier \"{$programName}\" n'existe pas.");
    }
        
    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"]["name"] = $programName;
    $responseArray["success"]["data"]["url"] = $programName;
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

/**
 * Applies provided dimensions to the LPX and LPY variables of the mpr.
 *
 * @param string $mpr The mpr code
 * @param string $lpx The value to assign to LPX
 * @param string $lpy The value to assign to LPY
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return string The mpr code with modified dimensions.
 */
function applyDimensionsToMpr(string $mpr, string $lpx, string $lpy) : string
{
    $mpr = preg_replace("/(?<!\r)\n|\r(?!\n)/", "\r\n", $mpr, -1, $count);
    $mpr = preg_replace("/^LPX=\".*\"\r$/m", "LPX=\"{$lpx}\"\r", $mpr, 1);
    $mpr = preg_replace("/^LPY=\".*\"\r$/m", "LPY=\"{$lpy}\"\r", $mpr, 1);
    return $mpr;
}