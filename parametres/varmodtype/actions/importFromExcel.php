<?php
    /**
     * \name		importFromExcel.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2019-03-25
     *
     * \brief 		Importe les paramètres spécifiques des combinaisons modèle-type-générique à partir d'un fichier au format Excel.
     */

    // Structure de retour vers javascript
    
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/PhpSpreadsheet/autoload.php";
    
    use \PhpOffice\PhpSpreadsheet\Cell\Coordinate as PHPSpreadSheetCoordinate;
    use \PhpOffice\PhpSpreadsheet\Reader\Xlsx as PHPSpreadSheetXlsxReader;

    const KEEP_FILES_FOR_X_DAYS = 1;
    const FIRST_PARAMETER_ROW = 5;
    const FIRST_TYPE_COLUMN = 2;
    
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

        // Clean or create the temporary directory and copy the uploaded file into it.
        $temporaryFolderPath = $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/varmodtype/temp";
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
                        saveExtractedData($db, $extractedData);
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

        $data = (object) array("modelGenerics" => array());
        foreach($workbook->getAllSheets() as $workSheet)
        {
            $column = PHPSpreadSheetCoordinate::stringFromColumnIndex(FIRST_TYPE_COLUMN - 1);
            $parameterKeyArray = array();
            foreach($workSheet->getColumnIterator($column, $column) as $parameterKeyColumn)
            {
                foreach($parameterKeyColumn->getCellIterator(FIRST_PARAMETER_ROW) as $cell)
                {
                    if(preg_match("/\A[A-Za-z_][A-Za-z0-9_]{0,7}\z/", $cell->getValue()))
                    {
                        $parameterKeyArray[$cell->getRow()] = $cell->getValue();
                    }
                    else
                    {
                        break;
                    }
                }
            }

            $types = array();
            foreach($workSheet->getRowIterator(FIRST_PARAMETER_ROW - 2, FIRST_PARAMETER_ROW - 2) as $typeImportNoRow)
            {
                foreach($typeImportNoRow->getCellIterator(PHPSpreadSheetCoordinate::stringFromColumnIndex(FIRST_TYPE_COLUMN)) as $cell)
                {
                    $column = PHPSpreadSheetCoordinate::columnIndexFromString($cell->getColumn());
                    if(preg_match("/\A\d+\z/", $cell->getValue()))
                    {
                        $parameters = array();
                        foreach($parameterKeyArray as $row => $key)
                        {
                            if(!preg_match("/\A\s*\z/", $workSheet->getCellByColumnAndRow($column, $row)->getValue()))
                            {
                                $parameters[] = (object) array(
                                    "key" => $key, 
                                    "value" => $workSheet->getCellByColumnAndRow($column, $row)->getValue()
                                );
                            }
                        }
                        $types[] = (object) array("importNo" => $cell->getValue(), "parameters" => $parameters);
                    }
                    else
                    {
                        break;
                    }
                }
            }

            $data->modelGenerics[] = (object) array(
                "model" => (object) array(
                    "id" => $workSheet->getCellByColumnAndRow(FIRST_TYPE_COLUMN - 1, FIRST_PARAMETER_ROW - 3)->getValue(), 
                    "timestamp" => $workSheet->getCellByColumnAndRow(FIRST_TYPE_COLUMN, FIRST_PARAMETER_ROW - 3)->getValue()
                ),
                "generic" => (object) array(
                    "id" => $workSheet->getCellByColumnAndRow(FIRST_TYPE_COLUMN + 1, FIRST_PARAMETER_ROW - 3)->getValue(), 
                    "timestamp" => $workSheet->getCellByColumnAndRow(FIRST_TYPE_COLUMN + 2, FIRST_PARAMETER_ROW - 3)->getValue()
                ),
                "types" => $types
            );
        }
        
        return $data;
    }

    /**
     * Saves extracted data into the database.
     *
     * @param \FabplanConnection $db The database to save the data in.
     * @param \StdClass $data The data extracted from the parameters file to verify and save into the database.
     *
     * @author Marc-Olivier Bazin-Maurice
     * @return 
     */
    function saveExtractedData(\FabplanConnection $db, \StdClass $data)
    {
        try
        {
            $db->getConnection()->beginTransaction();

            foreach($data->modelGenerics as $modelGeneric)
            {
                $model = \Model::withID($db, $modelGeneric->model->id, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
                if($model === null)
                {
                    throw new \Exception("There is no model with the unique identifier {$modelGeneric->model->id}.");
                }
                
                $generic = \Generic::withID($db, $modelGeneric->generic->id, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
                if($generic === null)
                {
                    throw new \Exception("There is no generic with the unique identifier {$modelGeneric->generic->id}.");
                }
    
                //echo json_encode(\DateTime::createFromFormat("Y-m-d h:i:s", $model->getTimestamp()));
                $databaseModelTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $model->getTimestamp());
                $extractedModelTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $modelGeneric->model->timestamp);
                if($databaseModelTimestamp->diff($extractedModelTimestamp)->format("%s") < 0)
                {
                    throw new \Exception("The model described by the data being imported is outdated.");
                }
    
                $databaseGenericTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $generic->getTimestamp());
                $extractedGenericTimestamp = \DateTime::createFromFormat("Y-m-d H:i:s", $modelGeneric->generic->timestamp);
                if($databaseGenericTimestamp->diff($extractedGenericTimestamp)->format("%s") < 0)
                {
                    throw new \Exception("The generic described by the data being imported is outdated.");
                }
    
                /* @var $extractedType \StdClass */
                foreach($modelGeneric->types as $extractedType)
                {
                    $type = \Type::withImportNo($db, $extractedType->importNo, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
                    if($type === null)
                    {
                        throw new \Exception("There is no type with the import number {$extractedType->importNo}.");
                    }
                    elseif($type->getGeneric()->getId() !== $generic->getId())
                    {
                        throw new \Exception(
                            "The type with import number {$type->getImportNo()}, 
                            belonging to the generic with the unique identifier {$type->getGeneric()->getId()}, 
                            cannot be assigned parameters from the generic with unique identifier {$generic->getId()}."
                        );
                    }
    
                    $modelTypeParameters = array_map(
                        function ($extractedParameter) use ($modelGeneric, $type) {
                            return new \ModelTypeParameter(
                                $extractedParameter->key, 
                                $extractedParameter->value, 
                                $modelGeneric->model->id, 
                                $type->getImportNo()
                            );
                        },
                        $extractedType->parameters
                    );

                    (new \ModelType($model, $type, $modelTypeParameters))->save($db);
                }
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