<?php
    /**
     * \name		importFromExcel.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2019-03-25
     *
     * \brief 		Importe les paramètres spécifiques des combinaisons modèle-type-générique à partir d'un fichier au format Excel.
     */
    
    include_once __DIR__ . "/../../varmodtypegen/controller/modelTypeGenericController.php"; /* Modèle-type-générique */
    include_once __DIR__ . "/../../generic/controller/genericController.php"; // Contrôleur de générique
    include_once __DIR__ . "/../../model/controller/modelController.php"; // Contrôleur de modèle
    include_once __DIR__ . "/../../../lib/PhpSpreadsheet/autoload.php"; // PHPSpreadsheet
    include_once __DIR__ . "/../../../lib/fileFunctions/fileFunctions.php"; // Fonctions sur les fichiers
    
    use \PhpOffice\PhpSpreadsheet\Cell\Coordinate as PHPSpreadSheetCoordinate;
    use \PhpOffice\PhpSpreadsheet\Reader\Xlsx as PHPSpreadSheetXlsxReader;

    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    const KEEP_FILES_FOR_X_DAYS = 1;
    const FIRST_PARAMETER_ROW = 5;
    const FIRST_TYPE_COLUMN = 2;
    
    try
    {  
        // Clean or create the temporary directory and copy the uploaded file into it.
        $temporaryFolderPath = __DIR__ . "\\temp";
        (new \FileFunctions\TemporaryFolder($temporaryFolderPath))->clean(KEEP_FILES_FOR_X_DAYS * 24 * 60 * 60, true);

        foreach($_FILES["files"]["error"] as $key => $error)
        {
            $sourceFilePath = $_FILES["files"]["tmp_name"][$key];
            $sourceFileName = basename($sourceFilePath);

            if($error === UPLOAD_ERR_OK)
            {
                if(is_uploaded_file($sourceFilePath))
                {
                    $destinationFileName = \FileFunctions\PathSanitizer::sanitize(
                        "UPLOAD_{$sourceFileName}",
                        array("fileNameMode" => true, "allowSlashesInFilename" => false)
                    );
                    $destinationFilePath = "{$temporaryFolderPath}\\{$destinationFileName}";
                    if(move_uploaded_file($sourceFilePath, $destinationFilePath))
                    {
                        $extractedData = extractDataFromExcelFile($destinationFilePath);
                        saveExtractedData($extractedData);
                    }
                    else
                    {
                        throw new \Exception("The file {$sourceFileName} was uploaded properly, but could not be moved.");
                    }
                }
                else
                {
                    throw new \Exception("The file {$sourceFileName} exists, but was not posted to the server by the client.");
                }
            }
            else
            {
                throw new \Exception("The file {$sourceFileName} was not uploaded properly. Error {$error} occured.");
            }
        }

        $responseArray["status"] = "success";
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
     * Extracts data from an Excel parameters file.
     *
     * @param string $filePath The path to the excel file to retrieve the data from.
     *
     * @author Marc-Olivier Bazin-Maurice
     * @return \StdClass An object containing the data contained in the Excel file.
     */
    function extractDataFromExcelFile(string $filePath) : \StdClass
    {
        $reader = new PHPSpreadSheetXlsxReader();
        $workbook = $reader->load("{$filePath}");

        $data = new \stdClass();
        foreach($workbook->getAllSheets() as $workSheet)
        {
            $modelId = $workSheet->getCellByColumnAndRow(FIRST_TYPE_COLUMN - 1, FIRST_PARAMETER_ROW - 3)->getValue();
            $modelTimestamp = $workSheet->getCellByColumnAndRow(FIRST_TYPE_COLUMN, FIRST_PARAMETER_ROW - 3)->getValue();
            $genericId = $workSheet->getCellByColumnAndRow(FIRST_TYPE_COLUMN + 1, FIRST_PARAMETER_ROW - 3)->getValue();
            $genericTimestamp = $workSheet->getCellByColumnAndRow(FIRST_TYPE_COLUMN + 2, FIRST_PARAMETER_ROW - 3)->getValue();
            $data->model = (object) array("id" => $modelId, "timestamp" => $modelTimestamp);
            $data->generic = (object) array("id" => $genericId, "timestamp" => $genericTimestamp);

            $startColumnForIterator = PHPSpreadSheetCoordinate::stringFromColumnIndex(FIRST_TYPE_COLUMN);
            $data->types = array();
            foreach($workSheet->getRowIterator(FIRST_PARAMETER_ROW - 2, FIRST_PARAMETER_ROW - 2) as $typeImportNoRow)
            {
                foreach($typeImportNoRow->getCellIterator($startColumnForIterator) as $cell)
                {
                    $typeImportNo = $cell->getValue();
                    if(preg_match("/\A\d+\z/", $typeImportNo))
                    {
                        $lastColumn = $cell->getColumn();
                        $data->types[$lastColumn] = (object) array("importNo" => $typeImportNo);
                    }
                    else
                    {
                        break;
                    }
                }
            }

            $lastRow = null;
            $column = PHPSpreadSheetCoordinate::stringFromColumnIndex(FIRST_TYPE_COLUMN - 1);
            $parameterKeyArray = array();
            foreach($workSheet->getColumnIterator($column, $column) as $parameterKeyColumn)
            {
                foreach($parameterKeyColumn->getCellIterator(FIRST_PARAMETER_ROW) as $cell)
                {
                    $parameterKey = $cell->getValue();
                    if(preg_match("/\A[A-Za-z_][A-Za-z0-9_]{0,7}\z/", $parameterKey))
                    {
                        $lastRow = $cell->getRow();
                        $parameterKeyArray[$lastRow] = $parameterKey;
                    }
                    else
                    {
                        break;
                    }
                }
            }

            foreach($data->types as $column => &$type)
            {
                $type->parameters = array();
                foreach($workSheet->getColumnIterator($column, $column) as $typeColumn)
                {
                    foreach($typeColumn->getCellIterator(FIRST_PARAMETER_ROW, $lastRow) as $cell)
                    {
                        $key = $parameterKeyArray[$cell->getRow()];
                        $value = $cell->getValue();
                        if(!preg_match("/\A\s*\z/", $value))
                        {
                            array_push($type->parameters, (object) array("key" => $key, "value" => $value));
                        }
                    }
                }
            }
        }
        
        return $data;
    }

    /**
     * Saves extracted data into the database.
     *
     * @param \StdClass $data The data extracted from the parameters file to verify and save into the database.
     *
     * @author Marc-Olivier Bazin-Maurice
     * @return 
     */
    function saveExtractedData(\StdClass $data)
    {
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();

            $modelId = $data->model->id;
            $model = \Model::withID($db, $modelId, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
            if($model === null)
            {
                throw new \Exception("There is no model with the unique identifier {$modelId}.");
            }
            
            $genericId = $data->generic->id;
            $generic = \Generic::withID($db, $genericId, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
            if($generic === null)
            {
                throw new \Exception("There is no generic with the unique identifier {$genericId}.");
            }

            //echo json_encode(\DateTime::createFromFormat("Y-m-d h:i:s", $model->getTimestamp()));
            $databaseModelTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $model->getTimestamp());
            $extractedModelTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $data->model->timestamp);
            if($databaseModelTimestamp->diff($extractedModelTimestamp)->format("%s") < 0)
            {
                throw new \Exception("The model described by the data being imported is outdated.");
            }

            $databaseGenericTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $generic->getTimestamp());
            $extractedGenericTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $data->generic->timestamp);
            if($databaseGenericTimestamp->diff($extractedGenericTimestamp)->format("%s") < 0)
            {
                throw new \Exception("The generic described by the data being imported is outdated.");
            }

            /* @var $extractedType \StdClass */
            foreach($data->types as $extractedType)
            {
                $typeImportNo = $extractedType->importNo;
                $type = \Type::withImportNo($db, $typeImportNo, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
                if($type === null)
                {
                    throw new \Exception("There is no type with the import number {$typeImportNo}.");
                }

                $modelTypeParameters = array();
                foreach($extractedType->parameters as $extractedParameter)
                {
                    $key = $extractedParameter->key;
                    $value = $extractedParameter->value;
                    $modelTypeParameter = new \ModelTypeParameter($key, $value, $modelId, $typeImportNo);
                    array_push($modelTypeParameters, $modelTypeParameter);
                }
                (new \ModelType($model, $type, $modelTypeParameters))->save($db);
            }

            $db->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $db->getConnection()->rollBack();
            throw $e;
        }
        finally
        {
            $db = null;
        }
    }
?>