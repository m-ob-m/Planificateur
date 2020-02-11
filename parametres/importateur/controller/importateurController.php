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
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/importateur/model/importateur.php";

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
        function __construct(\FabplanConnection $db)
        {
            $this->_db = $db;
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
            return \Importateur::retrieve($this->_db);
        }
    }
?>