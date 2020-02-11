<?php
    /**
     * \name		getParameters.php
     * \author    	Marc-olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-03-27
     *
     * \brief 		Retourne la liste de paramètres d'un modèle/type
     * \details 	Retourne la liste de paramètres d'un modèle/type 
     */

    // Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    try
    {
        // INCLUDE
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/test/controller/testController.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/varmodtypegen/controller/modelTypeGenericController.php";
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

        // Vérification des paramètres
        $testId = $_GET["testId"] ?? null;
        
        // Get the information
        $parameters = createTestParametersView($db, $testId);
        
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
     * Generate a view for the Test interface
     *
     * @param \FabplanConnection $db The database in which the Test exists
     * @param int $testId The unique numerical identifier of the Test for which the view must be created
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return array The array containing the fields of the view.
     */ 
    function createTestParametersView(\FabplanConnection $db, int $testId) : array
    {
        try
        {
            $db->getConnection()->beginTransaction();
            $test = \Test::withID($db, $testId);
            $modelTypeGeneric = (new \ModelTypeGeneric($test->getModel(), $test->getType()))->loadParameters($db);  
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
        foreach($test->getParameters() as $testParameter)
        {
            $key = $testParameter->getKey();
            $defaultValue = null;
            
            // Fill the result array
            foreach($modelTypeGeneric->getParameters() as $modelTypeGenericParameter)
            {
                if($modelTypeGenericParameter->getKey() === $key)
                {
                    $specific = $modelTypeGenericParameter->getSpecificValue();
                    $default = $modelTypeGenericParameter->getDefaultValue();
                    $defaultValue = ($specific !== null) ? $specific : $default;
                    break;
                }
            }
            
            array_push($parameters, 
                array(
                    "key" => $key, 
                    "specificValue" => $testParameter->getValue(), 
                    "description" => $testParameter->getDescription(),
                    "defaultValue" => $defaultValue
                )
            );
        }
        
        return $parameters;
    }
?>