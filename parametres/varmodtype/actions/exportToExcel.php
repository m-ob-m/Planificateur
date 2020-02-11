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
    
     require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/PhpSpreadsheet/autoload.php";
        
    use \PhpOffice\PhpSpreadsheet\Spreadsheet as PHPSpreadSheetWorkBook;
    use \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet as PHPSpreadSheetWorkSheet;
    use \PhpOffice\PhpSpreadsheet\Cell\Coordinate as PHPSpreadSheetCoordinate;
    use \PhpOffice\PhpSpreadsheet\Style\Conditional as PHPSpreadSheetConditional;
    use \PhpOffice\PhpSpreadsheet\Style\Alignment as PHPSpreadSheetAlignment;
    use \PhpOffice\PhpSpreadsheet\Style\Fill as PHPSpreadSheetFill;
    use \PhpOffice\PhpSpreadsheet\Style\Border as PHPSpreadSheetBorder;
    use \PhpOffice\PhpSpreadsheet\Writer\Xlsx as PHPSpreadsheetXlsxWriter;
        
    const KEEP_FILES_FOR_X_DAYS = 1;
    const FIRST_PARAMETER_ROW = 5;
    const FIRST_TYPE_COLUMN = 2;
    const TYPE_HEADER_COLOR = "FF00B050";
    const EVEN_ROW_COLOR = "FF97BFD9";
    const ODD_ROW_COLOR = "FFFFFFFF";
    const BORDER_COLOR = "FF000000";

    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    try
    {  
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/varmodtypegen/controller/modelTypeGenericController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/generic/controller/genericController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/model/controller/modelController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/fileFunctions/fileFunctions.php";

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

        $modelId = $_GET["modelId"] ?? null;
        if($modelId === null)
        {
            throw new \Exception("No model unique identifier provided.");
        }
        
        $genericId = $_GET["genericId"] ?? null;
        
        $modelTypeGenericCombinations = array();
        $model = null;
        $generic = null;
        try
        {
            $db->getConnection()->beginTransaction();
            
            /* Identify the model to export. */
            $model = \Model::withID($db, $modelId, \MYSQLDatabaseLockingReadTypes::FOR_SHARE);
            if($model === null)
            {
                throw new \Exception("There is no Model with a unique numerical identifier of \"{$modelId}\".");
            }
            
            /* Identify the generic to export. */
            if($genericId !== null)
            {
                $generic = \Generic::withID($db, $genericId, \MYSQLDatabaseLockingReadTypes::FOR_SHARE);
                if($generic === null)
                {
                    throw new \Exception("There is no Generic with a unique numerical identifier of \"{$genericId}\".");
                }
            }
            
            $modelTypeGenericCombinations = retrieveModelTypeGenericCombinationsToExport($db, $model, $generic);
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
        
        if(empty($modelTypeGenericCombinations))
        {
            if($genericId === null)
            {
                throw new \Exception("Aucun paramètre n'a été retourné car il n'existe aucun générique parce qu'aucun 
                    des génériques existants n'a de type associé.");
            }
            else
            {
                throw new \Exception("Aucun paramètre n'a été retourné car le générique \"{$genericDescription}\" n'a aucun 
                    type associé.");
            }
        }
        
        /* Procéder à l'exportation des paramètres des modèles-types */
        $workbookTitle = $genericId !== null ? 
            "Paramètres du modèle {$model->getDescription()} - {$generic->getDescription()}" : 
            "Paramètres du modèle {$model->getDescription()}";
        $workbook = new PhpSpreadsheetWorkBook();
        $workbook->getProperties()->setCreator("Fabplan")->setLastModifiedBy("Fabplan")->setTitle($workbookTitle)
        ->setSubject($workbookTitle)->setDescription($workbookTitle);
        exportModelTypeGenericsToExcel($workbook, $modelTypeGenericCombinations);
        
        /* Clean the temporary folder. */
        $TemporaryFolderPath = $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/varmodtype/temp";
        (new \FileFunctions\TemporaryFolder($TemporaryFolderPath))->clean(KEEP_FILES_FOR_X_DAYS * 24 * 60 * 60, true);
        
        $filename = $generic !== null ? 
            "{$model->getDescription()}_" . pathinfo($generic->getFilename(), PATHINFO_FILENAME) . ".xlsx" : 
            "{$model->getDescription()}.xlsx";
        $filepath = "{$TemporaryFolderPath}/{$filename}";
        try
        {
            (new PHPSpreadsheetXlsxWriter($workbook))->save($filepath);
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
     * Returns a list of \ModelTypeGeneric objects to export.
     *
     * @param \FabPlanConnection $db The database connection to retrieve the information from.
     * @param \Model $model The model to export.
     * @param ?\Generic $generic The generic to export the model-type parameters for. If not provided, then the parameters 
     *                           are exported for every model-type combination with the model $modelId.
     *
     * @throws \Exception if $modelId is not a valid existing unique \Model identifier.
     * @author Marc-Olivier Bazin-Maurice
     * @return array[\ModelTypeGeneric] An array of \ModelTypeGeneric objects to export.
     */
    function retrieveModelTypeGenericCombinationsToExport(\FabPlanConnection $db, \Model $model, ?\Generic $generic) : array
    {
        /* Identify the types to export according to the selected generic. */
        $typesToExport = null;
        if($generic === null)
        {
            $typesToExport = (new \TypeController($db))->getTypes();
        }
        else
        {
            $typesToExport = $generic->getAssociatedTypes($db);
        }
        
        /* Build the list of model-type combinations to export */
        $modelTypeGenericCombinations = array();
        /* @var \Type $type */
        foreach($typesToExport as $type)
        {
            $modelTypeGeneric = (new \ModelTypeGeneric($model, $type))->loadParameters($db);
            array_push($modelTypeGenericCombinations, $modelTypeGeneric);
        }
        
        return $modelTypeGenericCombinations;
    }
    
    /**
     * Creates an Excel workbook and then inserts the specific parameters of different model-type-generic combinations. 
     * The workbook can be edited and then re-sent to update the parameters.
     *
     * @param PHPSpreadSheetWorkBook $workbook The Excel workbook
     * @param array[\ModelTypeGeneric] $modelTypeGenericCombinations The \ModelTypeGeneric objects of which parameters are 
     *                                                               to be stored into the Excel workbook.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return
     */
    function exportModelTypeGenericsToExcel(PHPSpreadSheetWorkBook $workbook, array $modelTypeGenericCombinations)
    {
        $workSheetsIndex = array();
        $firstIteration = true;
        /* @var $modelTypeGenericCombination \ModelTypeGeneric */
        foreach($modelTypeGenericCombinations as $modelTypeGenericCombination)
        {
            $model = $modelTypeGenericCombination->getModel();
            $generic = $modelTypeGenericCombination->getType()->getGeneric();
            $genericIdAsString = strval($generic->getId());
            if(isset($workSheetsIndex[$genericIdAsString]))
            {
                /* If the worksheet for the current generic already exists, then go to that worksheet. Then, identify
                 * the next available column. */
                $worksheet = $workbook->getSheetByName($workSheetsIndex[$genericIdAsString]->workSheetName);
                $workbook->setActiveSheetIndexByName($workSheetsIndex[$genericIdAsString]->workSheetName);
                $workSheetsIndex[$genericIdAsString]->nextEmptyColumn++;
            }
            else
            {
                /* If the worksheet for the current generic doesn't exist, then create and prepare it. Then, identify
                 * the first available column. */
                $worksheet = null;
                if($firstIteration)
                {
                    $worksheet = $workbook->getSheet(0);
                    $firstIteration = false;
                }
                else 
                {
                    $worksheet = $workbook->addSheet(new PHPSpreadSheetWorkSheet());
                }
                $workSheetName = $generic->getDescription();
                $worksheet->setTitle($workSheetName);
                $workbook->setActiveSheetIndexByName($workSheetName);
                
                createTemplateWorkSheetForModelGeneric($worksheet, $model, $generic);
                
                $workSheetsIndex[$genericIdAsString] = (object) array(
                    "nextEmptyColumn" => FIRST_TYPE_COLUMN,
                    "parametersCount" => count($generic->getParameters()),
                    "workSheetName" => $workSheetName
                );
            }
            
            /* Fill a column of the worksheet with the parameters of the selected model-type-generic combination. */
            $column = $workSheetsIndex[$genericIdAsString]->nextEmptyColumn;
            ModelTypeGenericToColumn($worksheet, $modelTypeGenericCombination, $column);
        }

        applyStyles($workbook, $workSheetsIndex);

        $workbook->setActiveSheetIndex(0);
    }
    
    function applyStyles(PHPSpreadSheetWorkBook &$workbook, array $workSheetsIndex) : void
    { 
        foreach($workSheetsIndex as $worksheetIndex)
        {
            $worksheet = $workbook->getSheetByName($worksheetIndex->workSheetName);
            $workbook->setActiveSheetIndexByName($worksheetIndex->workSheetName);

            /* Set style for the type headers. */
            $style = array(
                "borders" => array(
                    "allBorders" => array(
                        "borderStyle" => PHPSpreadSheetBorder::BORDER_THIN
                    )
                ),
                "alignment" => array(
                    "textRotation" => 90,
                    "horizontal" => PHPSpreadSheetAlignment::HORIZONTAL_CENTER,
                    "vertical" => PHPSpreadSheetAlignment::VERTICAL_CENTER
                ),
                "fill" => array(
                    "fillType" => PHPSpreadSheetFill::FILL_SOLID,
                    "color" => array("argb" => TYPE_HEADER_COLOR)
                )
            );
            $firstColumn = PHPSpreadSheetCoordinate::stringFromColumnIndex(FIRST_TYPE_COLUMN - 1);
            $firstRow = (string)(FIRST_PARAMETER_ROW - 1);
            $lastColumn = PHPSpreadSheetCoordinate::stringFromColumnIndex($worksheetIndex->nextEmptyColumn);
            $lastRow = (string)(FIRST_PARAMETER_ROW - 1);
            $worksheet->getStyle("{$firstColumn}{$firstRow}:{$lastColumn}{$lastRow}")
                ->applyFromArray($style);
            
            /* Set style to parameter cells. */
            $style = array(
                "borders" => array(
                    "allBorders" => array(
                        "borderStyle" => PHPSpreadSheetBorder::BORDER_THIN
                    )
                ),
                "alignment" => array(
                    "horizontal" => PHPSpreadSheetAlignment::HORIZONTAL_CENTER,
                    "vertical" => PHPSpreadSheetAlignment::VERTICAL_CENTER
                )
            );
            $conditionnalFormatting = (new PHPSpreadSheetConditional())
                ->setConditionType(PHPSpreadSheetConditional::CONDITION_EXPRESSION)
                ->addCondition("NOT(MOD(ROW(), 2))");
            $conditionnalFormatting->getStyle()->getFill()->setFillType(PHPSpreadSheetFill::FILL_SOLID);
            $conditionnalFormatting->getStyle()->getFill()->getStartColor()->setARGB(EVEN_ROW_COLOR);
            $conditionnalFormatting->getStyle()->getFill()->getEndColor()->setARGB(EVEN_ROW_COLOR);
            $firstRow = (string)(FIRST_PARAMETER_ROW);
            $lastRow = (string)(FIRST_PARAMETER_ROW + $worksheetIndex->parametersCount - 1);
            $worksheet->getStyle("{$firstColumn}{$firstRow}:{$lastColumn}{$lastRow}")
                ->applyFromArray($style)
                ->setConditionalStyles(array($conditionnalFormatting));
        }
    }

    /**
     * Creates an Excel spreadsheet template to insert the specific parameters of a model-generic combination.
     *
     * @param PHPSpreadSheetWorkSheet $workbook The worksheet in which the template must be inserted.
     * @param \Model $model The model for which the worksheet must be filled.
     * @param \Generic $generic The generic for which the worksheet must be filled.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return
     */
    function createTemplateWorkSheetForModelGeneric(PHPSpreadSheetWorkSheet $worksheet, \Model $model, \Generic $generic)
    {
        /* Set column dimension. */
        $worksheet->getColumnDimensionByColumn(FIRST_PARAMETER_ROW - 1)->setWidth(10);
        
        $genericParameters = $generic->getParameters();
        $values = array(
            array("ID modèle", "Timestamp modèle", "ID générique", "Timestamp générique"),
            array(strval($model->getId()), $model->getTimestamp(), strval($generic->getId()), $generic->getTimestamp()),
            array(null),
            array("Paramètres")
        );
        for( $i = 0; $i < count($genericParameters); $i++)
        {
            /* Get the value to insert into the cell. */
            array_push($values, array($genericParameters[$i]->getKey()));
        }
        $worksheet->fromArray(
            $values,
            null,
            $worksheet->getCellByColumnAndRow(FIRST_TYPE_COLUMN - 1, FIRST_PARAMETER_ROW - 4)->getCoordinate(),
            true
        );
        
        /* Hiding some rows. */
        $worksheet->getRowDimension(FIRST_PARAMETER_ROW - 2)->setVisible(false);
        $worksheet->getRowDimension(FIRST_PARAMETER_ROW - 3)->setVisible(false);
        $worksheet->getRowDimension(FIRST_PARAMETER_ROW - 4)->setVisible(false);
    }
    
    /**
     * Fills a column of a worksheet with the parameters of a ModelTypeGeneric combination
     *
     * @param PHPSpreadSheetWorkSheet $worksheet The worksheet in which the data should be inserted.
     * @param \ModelTypeGeneric $modelTypeGeneric The ModelTypeGeneric objects of which parameters are to be stored into 
     *                                            the column.
     * @param int $column The column number of the column to fill.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return 
     */
    function ModelTypeGenericToColumn(PHPSpreadSheetWorkSheet $worksheet, \ModelTypeGeneric $modelTypeGeneric, int $column)
    {
        
        /* Set column dimension. */
        $worksheet->getColumnDimensionByColumn($column)->setWidth(10);
        
        /* Puts the parameters in the specified column. */
        $parameters = $modelTypeGeneric->getSpecificParametersAsKeyValuePairs();
        $genericParameters = $modelTypeGeneric->getType()->getGeneric()->getParameters();
        $values = array(
            array(strval($modelTypeGeneric->getType()->getImportNo())),
            array($modelTypeGeneric->getType()->getDescription())
        );
        for( $i = 0; $i < count($genericParameters); $i++)
        {
            /* Get the value to insert into the cell. */
            $key = $genericParameters[$i]->getKey();
            array_push($values, array(isset($parameters[$key]) ? $parameters[$key] : null));
        }
        
        /* Insert values in worksheet. */
        $worksheet->fromArray(
            $values,
            null,
            $worksheet->getCellByColumnAndRow($column, FIRST_PARAMETER_ROW - 2)->getCoordinate(),
            true
        );
    }
?>