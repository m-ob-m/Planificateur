<?php
/**
 * \name		MAJModeleUnitaire
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-03-16
 *
 * \brief 		Met à jour les programmes unitaires des combinaisons modèle-type spécifiées
 */

include_once __DIR__ . "/../controller/modelTypeGenericController.php"; // Contrôleur de modèle-type
include_once __DIR__ . "/../../../lib/PhpSpreadsheet/autoload.php"; // PHPSpreadsheet
include_once __DIR__ . "/../../../lib/fleFunctions/fileFunctions.php"; // Fonctions sur les fichiers

// Structure de retour vers javascript
$responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

try
{   
    $modelId = $_GET["modelId"] ?? null;
    $model  = (new \ModelController())->getModel($modelId);
    $modelName = $model->getDescription();
    
    if($model === null)
    {
        throw new \Exception("No model unique identifier provided.");
    }
    
    $workbook = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    
    $isFirstSheet = true;
    /* @var $generic \Generic */
    foreach((new \GenericController())->getGenerics() as $generic)
    {
        $worksheet = null;
        if($isFirstSheet === true)
        {
            $worksheet = $workbook->getSheet(1);
        }
        else
        {
            $worksheet = $workbook->addSheet();
        }
        
        /* En-tête d'identification du modèle et du générique actuel. */
        $worksheet->getCellByColumnAndRow(1, 1)
            ->setValueExplicit($model->getId(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $worksheet->getCellByColumnAndRow(2, 1)
            ->setValueExplicit($generic->getId(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $worksheet->getRowDimension(1)->setVisible(false);
        
        /* Liste de paramètres du générique actuel */
        $worksheet->getCellByColumnAndRow(1, 2)
            ->setValueExplicit($modelName, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $worksheet->getCellByColumnAndRow(1, 3)
            ->setValueExplicit("Parameters", \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $worksheet->getRowDimension(2)->setVisible(false);
        $i = 4;
        foreach($generic->getParametersAsKeyValuePairs() as $key => $value)
        {
            $worksheet->getCellByColumnAndRow(1, $i)
                ->setValueExplicit($key, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $i++;
        }
        
        $j = 2;
        /* @var $type \Type */
        foreach((new \TypeController())->getTypes() as $type)
        {
            $worksheet->getCellByColumnAndRow(1, 2)
                ->setValueExplicit($type->getImportNo(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->getCellByColumnAndRow(1, 3)
                ->setValueExplicit($type->getDescription(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $i = 4;
            
            /* @var $modelTypeGenericParameter \ModelTypeGenericParameter */
            foreach((new \ModelTypeGeneric())->getParameters() as $modelTypeGenericParameter)
            {
                $value = $modelTypeGenericParameter->getSpecificValue() ?? "";
                
                $cell = $worksheet->getCellByColumnAndRow($j, $i);
                $cell->setValueExplicit($value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $cell->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $cell->getStyle()->getFill()->getStartColor()->setARGB(($i % 2) === 0 ? "FFFFFFFF" : "FF97BFD9");
                $cell->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $i++;
            }
            
            $j++;
        }
    }
    
    $tempFolder = __DIR__ . "/temp";
    if(!file_exists($tempFolder) || !is_dir($tempFolder))
    {
        mkdir($tempFolder);
    }
    
    $filepath = "{$tempFolder}/output_{$modelName}.xlsx";
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($workbook))->save($filepath);
    
    // Retour au javascript
    $responseArray["status"] = "success";
    $responseArray["success"]["data"] = \FileFunctions\DownloadLinkGenerator::fromFilePath($filepath);
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