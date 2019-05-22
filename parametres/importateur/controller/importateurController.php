<?php

/**
 * \name		ImportateurController
* \author    	Marc-Olivier Bazin-Maurice
* \version		1.0
* \date       	2019-04-26
*
* \brief 		Contrôleur de l'importateur
* \details 		Contrôleur de l'importateur
*/

/*
 * Includes
*/
include_once __DIR__ . '/../../../lib/config.php';		// Fichier de configuration
include_once __DIR__ . '/../../../lib/connect.php';	    // Classe de connection à la base de données
include_once __DIR__ . '/../model/importateur.php';			// Classe d'une job

class ImportateurController
{
    private $_db;
    
    /**
     * ImportateurController constructor
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \ImportateurController This ImportateurController
     */
    function __construct()
    {
        $this->connect();
    }
    
    /**
     * Get the Importateur
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Importateur The Importateur
     */
    function getImportateur() : ?\Importateur
    {
        return \Importateur::retrieve($this->getDBConnection());
    }
    
    /**
     * Get the connection to the database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \FabplanConnection The connection to the database
     */
    function getDBConnection() : \FabPlanConnection
    {
        return $this->_db;
    }
    
    /**
     * Set the connection to the database
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \ImportateurController This ImportateurController
     */
    function connect() : \ImportateurController
    {
        $this->_db = new FabPlanConnection();
        return $this;
    }
}
?>