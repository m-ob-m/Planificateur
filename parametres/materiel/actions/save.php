<?php
    /**
     * \name		save.php
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-04-18
     *
     * \brief 		Sauvegarde un Materiel
     * \details     Sauvegarde un Materiel
     */
    
    // INCLUDE
    include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
    include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
    include_once __DIR__ . '/../controller/materielCtrl.php';
    
    //Structure de retour vers javascript
    $responseArray = array("status" => null, "success" => array("data" => null), "failure" => array("message" => null));
    
    try
    {
        $input =  json_decode(file_get_contents("php://input"));
        
        // Vérification des paramètres
        $id = (isset($input->id) ? $input->id : null);
        $siaCode = (isset($input->siaCode) ? $input->siaCode : null);
        $cutRiteCode = (isset($input->cutRiteCode) ? $input->cutRiteCode : null);
        $description = (isset($input->description) ? $input->description : null);
        $thickness = (isset($input->thickness) ? $input->thickness : null);
        $woodType = (isset($input->woodType) ? $input->woodType : null);
        $grain = (isset($input->grain) ? $input->grain : null);
        $isMDF = (isset($input->isMDF) ? $input->isMDF : null);
        
        $material = null;
        $db = new \FabPlanConnection();
        try
        {
            $db->getConnection()->beginTransaction();
            if($id === null)
            {
                $material = new \Materiel();
            }
            else
            {
                $material = \Materiel::withID($db, $id, \MYSQLDatabaseLockingReadTypes::FOR_UPDATE);
                if($material === null)
                {
                    throw new \Exception("Il n'y a aucun matériel possédant l'identifiant numérique unique \"{$id}\".");
                }
            }
            
            $material
                ->setDescription($description)
                ->setCodeSIA($siaCode)
                ->setCodeCutRite($cutRiteCode)
                ->setEpaisseur($thickness)
                ->setEssence($woodType)
                ->setGrain($grain)
                ->setEstMDF($isMDF)
                ->save($db);
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
        $responseArray["success"]["data"] = $material->getId();
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