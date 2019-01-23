<?php 
    /**
     * \name		getJobTypes.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-08-20
     *
     * \brief 		Get JobTypes for a specified job id
     * \details     Get JobTypes for a specified job id
     */
    
    // INCLUDE
    include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
    include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
    include_once __DIR__ . '/../controller/jobController.php'; // Classe contrôleur de la classe Job
    include_once __DIR__ . '/../../../parametres/model/controller/modelController.php'; // Classe contrôleur de la classe Model
    include_once __DIR__ . '/../../../parametres/type/controller/typeController.php'; // Classe contrôleur de la classe Type
    include_once __DIR__ . '/../../../parametres/generic/controller/genericController.php'; // Classe contrôleur de la classe Generic
    
    //Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    try
    {
        $db = new \FabPlanConnection();
        
        // Vérification des paramètres
        $jobId = $_GET["jobId"] ?? null;
        
        if(intval($jobId) != floatval($jobId) || !is_numeric($jobId) || intval($jobId) <= 0)
        {
            throw new \Exception("L'identifiant unique de Job fourni n'est pas un entier numérique positif.");
        }
        
        $output = array();
        $i = 0;
        foreach ((new \JobController())->getJob($jobId)->getJobTypes() as $jobType)
        {
            $model = (new \ModelController())->getModel($jobType->getModelId());
            $type = (new \TypeController())->getTypeByImportNo($jobType->getTypeNo());
            $generic = (new \GenericController())->getGeneric($type->getGenericId());
            $output[$i] = (object) array(
                "id" => $jobType->getId(),
                "jobId" => $jobType->getJobId(),
                "mprFile" => $jobType->getMprFile(),
                "model" => (object) array("id" => $model->getId(), "description" => $model->getDescription()),
                "type" => (object) array("importNo" => $type->getImportNo(), "description" => $type->getDescription()),
                "genericParameters" => array(),
                "jobTypeParameters" => array(),
                "parts" => array()
            );
            
            $currentParameters = $jobType->getParametersAsKeyValuePairs();
            foreach($generic->getGenericParameters() as $genericParameter)
            {
                $key = $genericParameter->getKey();
                array_push($output[$i]->genericParameters, (object) array(
                    "key" => $key, 
                    "value" => $genericParameter->getValue(),
                    "description" => $genericParameter->getDescription(),
                    "quickEdit" => $genericParameter->getQuickEdit()
                ));
                
                $output[$i]->jobTypeParameters[$key] = $currentParameters[$key] ?? $genericParameter->getValue();
            }
            
            foreach($jobType->getParts() as $part)
            {
                array_push($output[$i]->parts, (object) array(
                    "id" => $part->getId(),
                    "quantity" => $part->getQuantityToProduce(),
                    "length" => $part->getLength(),
                    "width" => $part->getWidth(),
                    "grain" => $part->getGrain()
                ));
            }
            
            $i++;
        }
        
        // Retour au javascript
        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = $output;
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