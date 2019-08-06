<?php
    /**
     * \name		getParameters.php
     * \author    	Marc-Olivier Bazin-Maurice
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
        require_once __DIR__ . '/../../generic/controller/genericController.php'; //Contrôleur de générique
        require_once __DIR__ . '/../../type/controller/typeController.php'; //Contrôleur de type
        require_once __DIR__ . '/../controller/modelTypeController.php'; // Contrôleur de Modèle-Type
        require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
        require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
        require_once __DIR__ . '/../../../lib/numberFunctions\numberFunctions.php';	/* Classe de fonctions sur les nombres et 
                                                            les chaînes de caractères représentant des nombres */

        // Initialize the session
        session_start();
                        
        // Check if the user is logged in, if not then redirect him to login page
        if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            {
                throw new \Exception("You are not logged in.");
            }
            else
            {
                header("location: /Planificateur/lib/account/logIn.php");
            }
            exit;
        }

        // Closing the session to let other scripts use it.
        session_write_close();
        
        // Vérification des paramètres
        $modelId = $_GET["modelId"] ?? null;
        $typeNo = $_GET["typeNo"] ?? null;
        
        if(is_positive_integer_or_equivalent_string($modelId, true, true))
        {
            $modelId = (int)$modelId;
        }
        else
        {
            throw new \Exception("L'identifiant unique de modèle fourni \"{$modelId}\" n'est pas valide.");
        }
        
        if(is_positive_integer_or_equivalent_string($typeNo, true, true))
        {
            $typeNo = (int)$typeNo;
        }
        else
        {
            throw new \Exception("Le numéro d'importation de type fourni \"{$typeNo}\" n'est pas valide.");
        }
        
        // Get the information
        $parameters = createModelTypeParametersView($modelId, $typeNo);
        
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
     * Generate a view for the ModelType interface
     *
     * @param int $modelId The unique numerical identifier of the model to create the view for.
     * @param int $typeNo The import number of the type to create the view for.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return array The array containing the fields of the view.
     */ 
    function createModelTypeParametersView(int $modelId, int $typeNo) : array
    {
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            $modelType = (new \ModelTypeController())->getModelType($modelId, $typeNo);
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
        /* @var $genericParameter \GenericParameter */
        foreach($modelType->getType()->getGeneric()->getParameters() as $genericParameter)
        {
            $key = $genericParameter->getKey();
            $value = null;
            foreach($modelType->getParameters() as $modelTypeParameter)
            {
                if($modelTypeParameter->getKey() === $key)
                {
                    $value = $modelTypeParameter->getValue();
                    break;
                }
            }
            
            // Fill the result array
            array_push($parameters, array(
                    "key" => $key, 
                    "specificValue" => $value, 
                    "description" => $genericParameter->getDescription(),
                    "defaultValue" => $genericParameter->getValue()
                )
            );
        }
        
        return $parameters;
    }
?>