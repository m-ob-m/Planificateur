<?php

/**
 * \name		TestParameterController
 * \author    	Marc-Olivier Bazin-Maurice
 * \version		1.0
 * \date       	2018-04-03
 *
 * \brief 		Controlleur d'un testParameter
 * \details 	Controlleur d'un testParameter
 */

/*
 * Includes
 */
require_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
require_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection à la base de données
require_once __DIR__ . '/../model/testParameter.php';	// Classe d'un test

class TestParameterController
{
    private $_db;
    
    function __construct()
    {
        $this->_db = new FabPlanConnection();
    }   

    function getTestParameter()
    {
        if(func_num_args() == 1)
        {
            return TestParameter::withID($this->_db, func_get_arg(0), func_get_arg(1));
        }
        elseif(isset($_GET["id"]) && isset($_GET["key"]))
        {
            return TestParameter::withID($this->_db, $_GET["id"], $_GET["key"]);
        }
        else
        {
            return new TestParameter($_GET["id"] ?? null, $_GET["key"] ?? null);
        }
    }
    
    function getConnection()
    {
        return $this->_db;
    }
}

?>