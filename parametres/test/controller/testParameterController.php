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
include_once __DIR__ . '/../../../lib/config.php';	// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	// Classe de connection � la base de donn�es

include_once __DIR__ . '/../model/testParameter.php';	// Classe d'un test

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
            return TestParameter::withID($this->_db, func_get_arg(0));
        }
        elseif(isset($_GET["id"]))
        {
            return TestParameter::withID($this->_db, $_GET["id"]);
        }
        else
        {
            return new TestParameter();
        }
    }
    
    function getConnection()
    {
        return $this->_db;
    }
}

?>