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
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/test/model/testParameter.php";

class TestParameterController
{
    private $_db;
    
    /**
     * TestParameterController constructor
     * @param \FabplanConnection $db A database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \TestParameterController This TestParameterController
     */ 
    function __construct($db)
    {
        $this->_db = $db;
    }   

    /**
     * Get a TestParameter by Test id and TestParameter key
     *
     * @param \FabplanConnection $db The database in which the TestParameter exists
     * @param int $testId The unique identifier of the Test
     * @param string $parameterKey The key of the Parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \TestParameter The TestParameter (null if none)
     */ 
    function getTestParameter()
    {
        if(func_num_args() == 1)
        {
            return \TestParameter::withID($this->_db, func_get_arg(0), func_get_arg(1));
        }
        elseif(isset($_GET["id"]) && isset($_GET["key"]))
        {
            return \TestParameter::withID($this->_db, $_GET["id"], $_GET["key"]);
        }
        else
        {
            return new \TestParameter($_GET["id"] ?? null, $_GET["key"] ?? null);
        }
    }
}

?>