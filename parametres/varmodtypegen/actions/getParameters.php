<?php
    /**
     * \name		getParameters.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-04-26
     *
     * \brief 		Retourne la liste de paramètres d'un modèle/type/générique
     * \details 	Retourne la liste de paramètres d'un modèle/type/générique
     */
    
    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));

    try
    {  
        // INCLUDE
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/varmodtypegen/controller/modelTypeGenericController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/generic/controller/genericController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/model/controller/modelController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/type/controller/typeController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/numberFunctions/numberFunctions.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";

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
        
        $modelId = is_positive_integer_or_equivalent_string($_GET["modelId"], true, true) ? intval($_GET["modelId"]) : null;
        $typeNo = is_positive_integer_or_equivalent_string($_GET["typeNo"], true, true) ? intval($_GET["typeNo"]) : null;
        
        // Get the information
        $parameters = array();
        if($modelId !== "" && $modelId !== null && $typeNo !== "" && $typeNo !== null)
        {
            $parameters = createModelTypeGenericParametersView($db, $modelId, $typeNo);
        }
        
        // Retour au javascript
        $responseArray["status"] = "success";
        $responseArray["success"]["data"] = $parameters;
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
     * Generate a view for the ModelTypeGeneric interface.
     *
     * @param \FabplanConnection $db The database where the ModelTypeGeneric exists.
     * @param int $modelId The unique numerical identifier of the model to create the view for.
     * @param int $typeNo The import number of the type to create the view for.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return array The array containing the fields of the view.
     */
    function createModelTypeGenericParametersView(\FabplanConnection $db, int $modelId, int $typeNo) : array
    {
        try
        {
            $db->getConnection()->beginTransaction();
            
            $model = \Model::withID($db, $modelId);
            if($model === null)
            {
                throw \Exception("Il n'y a pas de modèle avec l'identifiant unique \"{$modelId}\".");
            }
            
            $type = \Type::withImportNo($db, $typeNo);
            if($type === null)
            {
                throw \Exception("Il n'y a pas de type avec le numéro d'importation \"{$typeNo}\".");
            }
            
            $modelTypeGeneric = (new \ModelTypeGeneric($model, $type))->loadParameters($db);
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
        
        $parameters = array();
        foreach($modelTypeGeneric->getParameters() as $modelTypeGenericParameter)
        {   
            // Fill the result array
            $key = $modelTypeGenericParameter->getKey();
            array_push($parameters, 
                array(
                    "key" => $modelTypeGenericParameter->getKey(),
                    "value" => $modelTypeGenericParameter->getValue(),
                    "description" => $modelTypeGenericParameter->getDescription(),
                    "defaultValue" => $modelTypeGenericParameter->getDefaultValue(),
                    "specificValue" => $modelTypeGenericParameter->getSpecificValue(),
                    "quickEdit" => $modelTypeGeneric->getType()->getGeneric()->getParameterByKey($key)->getQuickEdit()
                )
            );
        }
        
        return $parameters;
    }
?>