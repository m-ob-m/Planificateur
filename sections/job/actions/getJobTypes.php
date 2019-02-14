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
        // Vérification des paramètres
        $jobId = $_GET["jobId"] ?? null;
        
        if(intval($jobId) != floatval($jobId) || !is_numeric($jobId) || intval($jobId) <= 0)
        {
            throw new \Exception("L'identifiant unique de Job fourni n'est pas un entier numérique positif.");
        }
        
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            $jobTypes = getJobTypes($db, $jobId);
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
        
        // Retour au javascript
        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = $jobTypes;
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
     * Gets the list of job types for this job.
     *
     * @param \FabplanConnection $db The database to query.
     * @param int $jobId The numerical unique identifier of the selected job.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \JobType[] An array of job types.
     */
    function getJobTypes(\FabplanConnection $db, int $jobId) : array
    {
        $jobTypes = array();
        $i = 0;
        foreach(\Job::withID($db, $id)->getJobTypes() as $jobType)
        {
            $model = \Model::withID($db, $jobType->getModelId());
            $type = \Type::withImportNo($db, $jobType->getTypeNo());
            $generic = \Generic::withID($db, $type->getGenericId());
            $jobTypes[$i] = (object) array(
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
                array_push($jobTypes[$i]->genericParameters, (object) array(
                    "key" => $key,
                    "value" => $genericParameter->getValue(),
                    "description" => $genericParameter->getDescription(),
                    "quickEdit" => $genericParameter->getQuickEdit()
                ));
                
                $jobTypes[$i]->jobTypeParameters[$key] = $currentParameters[$key] ?? $genericParameter->getValue();
            }
            
            foreach($jobType->getParts() as $part)
            {
                array_push($jobTypes[$i]->parts, (object) array(
                    "id" => $part->getId(),
                    "quantity" => $part->getQuantityToProduce(),
                    "length" => $part->getLength(),
                    "width" => $part->getWidth(),
                    "grain" => $part->getGrain()
                ));
            }
            
            $i++;
        }
        
        return $jobTypes;
    }
?>