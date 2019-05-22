<?php 
    /**
     * \name		create.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2019-04-25
     *
     * \brief 		Create a job from CutQueue's import module
     * \details     Create a job from CutQueue's import module
     */

    // INCLUDE
    include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
    include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
    include_once __DIR__ . '/../controller/jobController.php';

    //Structure de retour vers javascript
    $responseArray = array(
        "status" => null, 
        "success" => array("data" => null), 
        "warning" => array("message" => null), 
        "failure" => array("message" => null)
    );

    try
    {
        $inputJob =  json_decode(file_get_contents("php://input"));
        
        $id = null;
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            $job = createJob($db, $inputJob);
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
        $responseArray["success"]["data"] = $job->getId();
    }
    catch(\CannotOverwriteJobException $e)
    {
        $responseArray["status"] = "warning";
        $responseArray["failure"]["message"] = $e->getMessage();
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

    function createJob(\FabplanConnection $db, \stdClass $inputJob)
    {
        if(!is_object($inputJob))
        {
            throw new \Exception("No job was provided.");
        }
        elseif(!isset($inputJob->name))
        {
            throw new \Exception("The provided job has no specified name.");
        }

        $job = null;
        $jobName = $inputJob->name;
        $databaseJob = \Job::withName($db, $jobName, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
        if($databaseJob === null)
        {
            $job = new \Job(null, $jobName, $inputJob->deliveryDate ?? null, "E");
        }
        else
        {
            throw new \CannotOverwriteJobException($jobName);
        }

        if(isset($inputJob->jobTypes))
        {
            $jobTypes = array();
            foreach($inputJob->jobTypes as $jobTypeIndex => $inputJobType)
            {
                if(!isset($inputJobType->model))
                {
                    throw new \Exception("JobType {$jobTypeIndex} of job {$jobName} has no specified model.");
                }
                elseif(!isset($inputJobType->type))
                {
                    throw new \Exception("JobType {$jobTypeIndex} of job {$jobName} has no specified type.");
                }
                elseif(!isset($inputJobType->parts))
                {
                    throw new \Exception("JobType {$jobTypeIndex} of job {$jobName} has no parts section.");
                }
                elseif(!isset($inputJobType->externalProfile))
                {
                    throw new \Exception("JobType {$jobTypeIndex} of job {$jobName} has no specified external profile.");
                }
                elseif(!isset($inputJobType->material))
                {
                    throw new \Exception("JobType {$jobTypeIndex} of job {$jobName} has no specified material.");
                }

                $model = \Model::withDescription($db, $inputJobType->model) ?? \Model::withID($db, 1);
                $type = \Type::withImportNo($db, $inputJobType->type);
                $jobType = (new \JobType())->setModel($model)->setType($type);

                /* Set parameters. */
                $parameters = $jobType->loadParameters($db)->getParametersAsKeyValuePairs();
                if(!in_array($type->getImportNo(), array(10, 11, 12, 13), true))
                {
                    $externalProfile = $inputJobType->externalProfile;
                    if($externalProfile === "" || $externalProfile === null)
                    {
                        $parameters["T_Ext"] = "0";
                    }
                    elseif(preg_match("/\A[A-Z]\*\z/", $externalProfile))
                    {
                        /* External profiles consisting of an alphabetic character followed by an asterisk are replaced by  */
                        $parameters["T_Ext"] = preg_replace("/\A([A-Z])\*\z/", "_PROF_\1\1", $externalProfile);
                    }
                    elseif(preg_match("/\A[A-Z]\z/", $externalProfile))
                    {
                        $parameters["T_Ext"] = preg_replace("/\A([A-Z])\z/", "_PROF_\1", $externalProfile);
                    }
                    else
                    {
                        $parameters["T_Ext"] = $externalProfile;
                    }
                }
                if(in_array($type->getImportNo(), array(1, 2), true) && preg_match("/\ATHERMO\z/i", $inputJobType->material))
                {
                    $parameters["thermo"] = 1;
                }
                
                $jobTypeParameters = array();
                foreach($parameters as $key => $value)
                {
                    array_push($jobTypeParameters, new \JobTypeParameter(null, $key, $value));
                }
                
                $jobTypePortes = array();
                foreach($inputJobType->parts as $partIndex => $part)
                {
                    if(!isset($part->height))
                    {
                        throw new \Exception(
                            "Part {$partIndex} of jobType {$jobTypeIndex} of job {$jobName} has no specified height."
                        );
                    }
                    elseif(!isset($part->width))
                    {
                        throw new \Exception(
                            "Part {$partIndex} of jobType {$jobTypeIndex} of job {$jobName} has no specified width."
                        );
                    }
                    elseif(!isset($part->quantity))
                    {
                        throw new \Exception(
                            "Part {$partIndex} of jobType {$jobTypeIndex} of job {$jobName} has no specified quantity."
                        );
                    }
                    elseif(!isset($part->quantity))
                    {
                        throw new \Exception(
                            "Part {$partIndex} of jobType {$jobTypeIndex} of job {$jobName} has no specified grain direction."
                        );
                    }
                    
                    $jobTypePorte = new \JobTypePorte(null, null, $part->quantity, 0, $part->height, $part->width, $part->grain);
                    array_push($jobTypePortes, $jobTypePorte);
                }
                $jobType->setParameters($jobTypeParameters)->setParts($jobTypePortes);
                
                array_push($jobTypes, $jobType);
            }
            $job->setJobTypes($jobTypes)->save($db);
        }
        else
        {
            throw new \Exception("JobType {$jobTypeIndex} of job {$jobName} has no contents.");
        }

        return $job;
    }

    class CannotOverwriteJobException extends \Exception
    {
        private $jobName;
        
        /**
         * \CannotOverwriteJobException constructor
         * @param string $jobName The name of the job that triggered the exception
         * @param int $code The code of the \Exception
         * @param \Exception $previous A children exception if applicable
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \CannotOverwriteJobException
         */
        public function __construct(string $jobName, int $code = 0, \Exception $previous = null) 
        {
            $message = "Job \"{$jobName}\" cannot be overwritten.";
            parent::__construct($message, $code, $previous);
            $this->jobName = $jobName;
        }
        
        /**
         * Returns a string representing the exception
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string A string representing the exception
         */
        public function __toString() : string
        {
            return __CLASS__ . ": [{$this->getCode()}]: {$this->getMessage()}\n";
        }
        
        /**
         * Returns the name of the job that triggered the exception
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The name of the job that triggered the exception
         */
        public function getJobName() : string
        {
            return $this->jobName;
        }
    }


?>