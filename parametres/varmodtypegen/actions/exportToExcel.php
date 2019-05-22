<?php
    /**
     * \name		exportToExcel.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2019-02-08
     *
     * \brief 		Exporte les paramètres spécifiques des combinaisons modèle-type-générique au format Excel pour édition et 
     *              réimportation.
     */
    
    include_once __DIR__ . "/../controller/modelTypeGenericController.php"; // Contrôleur de modèle-type-générique
    include_once __DIR__ . "/../../generic/controller/genericController.php"; // Contrôleur de générique
    include_once __DIR__ . "/../../model/controller/modelController.php"; // Contrôleur de modèle
    include_once __DIR__ . "/../../../lib/PhpSpreadsheet/autoload.php"; // PHPSpreadsheet
    include_once __DIR__ . "/../../../lib/fileFunctions/fileFunctions.php"; // Fonctions sur les fichiers
    
    use \PhpOffice\PhpSpreadsheet as PHPSpreadSheetWorkBook;
    use \PhpOffice\PhpSpreadsheet\Cell\DataType as PHPSpreadSheetDataType;
    use \PhpOffice\PhpSpreadsheet\Style\Border as PHPSpreadSheetBorder;
    use \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as PHPSpreadSheetWorkSheet;
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    const KEEP_FILES_FOR_X_DAYS = 1;
    
    try
    {   
        $modelId = $_GET["modelId"] ?? null;
        if($modelId === null)
        {
            throw new \Exception("No model unique identifier provided.");
        }
        $genericId = $_GET["genericId"] ?? null;
    
        $workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $workSheetsIndex = array();
        $modelTypeGenericsToExport = retrieveModelTypeGenericsToExport($modelId, $genericId);
        /* @var $modelTypeGenericToExport \ModelTypeGeneric */
        foreach($modelTypeGenericsToExport as $modelTypeGenericToExport)
        {
            $worksheet = null;
            $generic = $modelTypeGenericToExport["generic"];
            $model = $modelTypeGenericToExport["model"];
            $genericIdAsString = strval($generic->getId());
            if(array_key_exists($genericIdAsString, $workSheetsIndex))
            {
                $worksheet = $workbook->getSheet($workSheetsIndex[$genericIdAsString]->index);
            }
            else
            {
                $worksheet = CreateWorksheetForModelGeneric($workbook, $model, $generic);
                $workSheetsIndex[$genericIdAsString] = (object)array(
                    "index" => $workbook->getActiveSheetIndex(),
                    "nextEmptyColumn" => 2
                );
            }
            ModelTypeGenericCombinationToSpreadsheetColumn($worksheet, $modelTypeGenericToExport);
            $workSheetsIndex[$genericIdAsString]->nextEmptyColumn++;
        }
        $workbook->setActiveSheetIndex(0);
        
        /* Clean the temporary folder. */
        $TemporaryFolderPath = __DIR__ . "/temp";
        (new \FileFunctions\TemporaryFolder($TemporaryFolderPath))->clean(KEEP_FILES_FOR_X_DAYS * 24 * 60 * 60, true);
        
        $filename = "output_{$model->getDescription()}.xlsx";
        $filepath = "{$TemporaryFolderPath}/{$filename}";
        try
        {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($workbook))->save($filepath);
        }
        catch(\Exception $e)
        {
            throw new \Exception("La sauvegarde du fichier généré {$filename} a échouée.");
        }
        
        // Retour au javascript
        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = \FileFunctions\DownloadLinkGenerator::fromFilePath($filepath);
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
    
    /**
     * Creates an Excel spreadsheet template to insert the specific parameters of a model-generic combination.
     *
     * @param PHPSpreadSheetWorkBook\Spreadsheet $workbook The workbook i which the worksheet must be created.
     * @param \Model $model The model for which the worksheet must be filled.
     * @param \Generic $generic The generic for which the worksheet must be filled.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return PHPSpreadSheetWorkSheet The new worksheet
     */
    function CreateWorksheetForModelGeneric(PHPSpreadSheetWorkBook\Spreadsheet $workbook, \Model $model, \Generic $generic)
        : PHPSpreadSheetWorkSheet
    {
        /* Création d'une nouvelle feuille de travail pour le générique actuel.*/
        $worksheet = $workbook->addSheet(new PHPSpreadSheetWorkSheet());
        $worksheet->setTitle($generic->getDescription());
        
        /* En-tête d'identification du modèle et du générique actuel. */
        $worksheet->getCellByColumnAndRow(1, 1)->setValueExplicit($model->getId(), PHPSpreadSheetDataType::TYPE_STRING);
        $worksheet->getCellByColumnAndRow(2, 1)->setValueExplicit($generic->getId(), PHPSpreadSheetDataType::TYPE_STRING);
        $worksheet->getRowDimension(1)->setVisible(false);
        
        /* Liste de paramètres du générique actuel */
        $worksheet->getCellByColumnAndRow(1, 2)->setValueExplicit($model->getDescription(), PHPSpreadSheetDataType::TYPE_STRING);
        $worksheet->getCellByColumnAndRow(1, 3)->setValueExplicit("Parameters", PHPSpreadSheetDataType::TYPE_STRING);
        $worksheet->getRowDimension(2)->setVisible(false);
        
        /* Afficher les clés des paramètres du générique dans la première colonne du classeur. */
        $i = 4;
        foreach($generic->getParametersAsKeyValuePairs() as $key => $value)
        {
            $cell = $worksheet->getCellByColumnAndRow(1, $i);
            $cell->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $cell->getStyle()->getFill()->getStartColor()->setARGB(($i % 2) === 1 ? "FFFFFFFF" : "FF97BFD9");
            $cell->getStyle()->getBorders()->getOutline()->setBorderStyle(PHPSpreadSheetBorder::BORDER_THIN);
            $cell->setValueExplicit($key, PHPSpreadSheetDataType::TYPE_STRING);
            $i++;
        }
        $worksheet->getColumnDimensionByColumn(1)->setAutoSize(true);
        
        return $worksheet;
    }
    
    /**
     * Fills a column of an Excel spreadsheet with the specific parameters of a model-type-generic combination.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet &$worksheet The worksheet to fill.
     * @param \ModelTypeGeneric $modelTypeGeneric The model-type-generic combination for which the specified column must be 
     *                                            filled.
     * @param int $nextAvailableColumn The number associated to the next available column (1 is the first column of the 
     *                                 worksheet).
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return 
     */
    function ModelTypeGenericCombinationToSpreadsheetColumn(PHPSpreadSheetWorkSheet &$worksheet, 
        \ModelTypeGeneric $modelTypeGenericToExport, int $currentColumn) : void
    {
        $model = $modelTypeGenericToExport["model"];
        $type = $modelTypeGenericToExport["type"];
        $generic = $modelTypeGenericToExport["generic"];
        $modelTypeGeneric = $modelTypeGenericToExport["modelTypeGeneric"];
        $modelTypeGenericSpecificParameters = $modelTypeGeneric->getSpecificParametersAsKeyValuePairs();
        
        /* Afficher le type en cours du générique en cours dans l'en-tête des colonnes du tableau de paramètres. */
        $cell = $worksheet->getCellByColumnAndRow($currentColumn, 2);
        $cell->setValueExplicit($type->getImportNo(), PHPSpreadSheetDataType::TYPE_STRING);
        $cell = $worksheet->getCellByColumnAndRow($currentColumn, 3);
        $cell->setValueExplicit($type->getDescription(), PHPSpreadSheetDataType::TYPE_STRING);
        $cell->getStyle()->getAlignment()->setTextRotation(90);
        $cell->getStyle()->getBorders()->getOutline()->setBorderStyle(PHPSpreadSheetBorder::BORDER_THIN);
        
        /* Afficher les parmètres propres au modèle-type-générique en cours dans le tableau des paramètres. */
        $i = 4;
        foreach($generic->getParametersAsKeyValuePairs() as $key => $genericValue)
        {
            $cell = $worksheet->getCellByColumnAndRow($currentColumn, $i);
            $cell->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $cell->getStyle()->getFill()->getStartColor()->setARGB(($i % 2) === 1 ? "FFFFFFFF" : "FF97BFD9");
            $cell->getStyle()->getBorders()->getOutline()->setBorderStyle(PHPSpreadSheetBorder::BORDER_THIN);
            if(array_key_exists($key, $modelTypeGenericSpecificParameters))
            {
                $cell->setValueExplicit($modelTypeGenericSpecificParameters[$key], PHPSpreadSheetDataType::TYPE_STRING);
            }
            $i++;
        }
        
        $worksheet->getColumnDimensionByColumn($currentColumn)->setAutoSize(true);
    }
    
    /**
     * Retrieves the model-type-generic combinations to export.
     *
     * @param int $modelId The id of the model to export.
     * @param int|null $genericId The id of the generic to export.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \ModelTypeGeneric[] An array of model-type-generic combinations to export.
     */
    function retrieveModelTypeGenericsToExport(int $modelId, ?int $genericId) : array
    {
        $modelTypeGenericsToExport = array();
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            
            $model = \Model::withID($db, $modelId);
            if($model === null)
            {
                throw new \Exception("There is no Model with a unique numerical identifier of \"{$modelId}\".");
            }
            
            $typesToExport = null;
            if($genericId === null)
            {
                $typesToExport = (new \TypeController())->getTypes();
            }
            else
            {
                $genericToExport = \Generic::withID($db, $genericId);
                if($genericToExport === null)
                {
                    throw new \Exception("There is no Generic with a unique numerical identifier of \"{$genericId}\".");
                }
                $typesToExport = \Generic::withID($db, $genericId)->getAssociatedTypes();
            }
            
            /* \var $type \Type */
            foreach($typesToExport as $type)
            {
                $modelTypeGeneric = (new \ModelTypeGeneric($model->getId(), $type->getImportNo()))->loadParameters($db);
                $generic = \Generic::withID($db, $type->getGenericId());
                array_push(
                    $modelTypeGenericsToExport,
                    array("modelTypeGeneric" => $modelTypeGeneric, "model" => $model, "type" => $type, "generic" => $generic)
                );
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
        
        return $modelTypeGenericsToExport;
    }
?>