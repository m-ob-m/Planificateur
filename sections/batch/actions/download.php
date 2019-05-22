<?php
/**
 * \name		download.php
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-08-15
 *
 * \brief 		Télécharge un Batch vers CutQueue
 * \details     Télécharge un Batch vers CutQueue
 */

/* INCLUDE */
include_once __DIR__ . "/../../../lib/csv/csvCutRite.php";		// Créateur de CSV pour CutRite
include_once __DIR__ . "/../../../lib/mpr/mprCutRite.php";		// Créateur de MPR pour CutRite
include_once __DIR__ . "/../../../lib/config.php";	// Fichier de configuration
include_once __DIR__ . "/../../../lib/connect.php";	// Classe de connection à la base de données
include_once __DIR__ . "/../../../lib/fileFunctions/fileFunctions.php";	// Classe de fonctions liées aux fichiers non natives à PHP

include_once __DIR__ . "/../controller/batchController.php";	// Contrôleur de Batch
include_once __DIR__ . "/../../../parametres/materiel/controller/materielCtrl.php";	// Contrôleur de Materiel

//Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{    
    $input =  json_decode(file_get_contents("php://input"));
    
    // Vérification des paramètres
    $id = $input->batchId ?? null;
    $action = $input->action ?? 1;
    
    // Modèles
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $batch = \Batch::withID($db, $id, MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
        if(!\Materiel::withID($db, $batch->getMaterialId())->getEstMDF())
        {
            throw new \Exception("Seul le nesting de pièces de MDF est supporté.");
        }
        
        $zipPath = createZipArchiveForBatch($batch, $action);
        
        // Téléchargement du fichier ZIP ou message de transfert
        switch($action)
        {
            case "1":
                // MaJ état de la batch pour En attente
                $batch->setMprStatus("A")->save($db);
                
                $responseArray["success"]["data"] = null;
                break;
                
            case "2":
                $responseArray["success"]["data"]["name"] = basename($zipPath);
                $responseArray["success"]["data"]["url"] = \FileFunctions\DownloadLinkGenerator::fromFilePath($zipPath);
                break;
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
    
    $responseArray["status"] = "success";
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
 * Deletes a list of files
 *
 * @param array $filesToDelete The list of files to delete
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return string A download link
 */
function deleteFiles(array $filesToDelete = array())
{
    if(!empty($filesToDelete))
    {
        foreach($filesToDelete as $fileToDelete)
        {
            unlink($fileToDelete);
        }
    }
}

/**
 * Creates the zip archive and its dependencies for a given Batch
 *
 * @param Batch $batch A Batch object
 * @param int $action The selected action (1 = Nest with CutRite, 2 = download a local copy of the individual programs)
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return string The path of the zip archive
 */
function createZipArchiveForBatch(\Batch $batch, int $action) : string
{
    /* Nettoyage des fichiers temporaires. */
    $KEEP_FILES_FOR_X_DAYS = 31;
    $temporaryFolder = __DIR__ . "/../temp";
    (new \FileFunctions\TemporaryFolder($temporaryFolder))->clean($KEEP_FILES_FOR_X_DAYS * 24 * 60 * 60);
    
    $filesToDelete  = array();
    
    // Création du fichier ZIP
    $zipName = \FileFunctions\PathSanitizer::sanitize(
        "{$batch->getName()}.zip",
        array(
            "fileNameMode" => true,
            "allowSlashesInFilename" => false,
            "transliterate" => true,
            "fullyPortable" => true
        )
    );
    $zipPath = "{$temporaryFolder}/{$zipName}";
    
    // Ouvrir une archive
    $zip = new \ZipArchive();
    $openStatus = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if($openStatus !== true)
    {
        $message = "Archive \"{$zipName}\" returned error {$openStatus}. Please close related documents and try again.\n";
        throw new \Exception($message);
    }
    
    // Création du CSV de la batch et ajout dans le zip
    $csvPath = createCsvForBatch($batch);
    $zip->addFile($csvPath, basename($csvPath));
    array_push($filesToDelete, $csvPath);
    if($action === 1)
    {
        copy($csvPath, CR_FABRIDOR . "SYSTEM_DATA/import/" . basename($csvPath));
    }
    
    // Création des fichiers mpr de la batch
    foreach ($batch->getJobs() as $job)
    {
        foreach($job->getJobTypes() as $jobType)
        {
            $mprPath = createMprForJobType($jobType);
            $zip->addFile($mprPath, basename($mprPath));
            array_push($filesToDelete, $mprPath);
            if($action === 1)
            {
                copy($mprPath, \FileFunctions\PathSanitizer::sanitize(CR_FABRIDOR . "SYSTEM_DATA\\import\\" . basename($mprPath)));
            }
        }
    }
    
    // Fermer l'archive et la sauvegarder. La suppression des fichiers à insérer dans l'archive peut ensuite être effectuée.
    $zip->close();
    deleteFiles($filesToDelete);
    return $zipPath;
}

/**
 * Creates the csv file for a given Batch
 *
 * @param Batch A Batch object 
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return string The path of the csv file
 */
function createCsvForBatch(\Batch $batch) : string
{
    $csvName = \FileFunctions\PathSanitizer::sanitize(
        "{$batch->getName()}.txt", 
        array(
            "fileNameMode" => true,
            "allowSlashesInFilename" => false,
            "transliterate" => true,
            "fullyPortable" => true
        )
    );
    $csvPath = __DIR__ . "/../temp/{$csvName}";
    $csv = new \CsvCutrite();
    $csv->makeCsvFromBatch($batch);
    $csv->makeCsvFile($csvPath);
    return $csvPath;
}

/**
 * Creates the mpr file for a given JobType
 *
 * @param JobType A JobType object
 *
 * @throws
 * @author Marc-Olivier Bazin-Maurice
 * @return string The path of the mpr file
 */
function createMprForJobType(\JobType $jobType) : string
{
    $mprName = \FileFunctions\PathSanitizer::sanitize(
        "{$jobType->getModel()->getId()}_{$jobType->getType()->getImportNo()}_{$jobType->getId()}.mpr",
        array(
            "fileNameMode" => true,
            "allowSlashesInFilename" => false, 
            "transliterate" => true, 
            "fullyPortable" => true
        )
    );
    $mprPath = __DIR__ . "/../temp/{$mprName}";
    
    $mpr = new \mprCutrite(realpath(__DIR__ . "/../../../lib/generique.mpr"));
    $mpr->makeMprFromJobType($jobType);
    $mpr->makeMprFile($mprPath);
    return $mprPath;
}
?>