<?php
    /**
     * \filename	config.php
     * 
     * \brief 		Fichier de configuration pour le planificateur de production
     * 
     * \date		2017-01-18
     * \version 	1.0
     */
    
    /*
     * Paramètres de connexion à la base de données de FabPlan
     */
    define('DB_HOST', "127.0.0.1");
    define('DB_USER', "root");
    define('DB_PASS', "Cuisine123");
    define('DB_NAME', "fabplan");
    
    /*
     * Paramètre des chemins d'accès aux répertoires de CutRite
     */
    define('CR_FABRIDOR', "C:\\V90\\FABRIDOR\\");
    
    /*
     * Chemin d'accès du répertoire de la Vantage 200
     */
    define('V_200', "\\\\srvcuisine\\Homag\\__vantage_200\\");
    
    /*
     * Chemin d'enregistrement des fichiers tests
     */
    define('_TESTDIRECTORY', "C:\\PROGRAMMES_V200\\__TEST\\");
    
    /*
     * Chemin d'enregistrement des fichiers de programmes unitaires
     */
    define('_UNITARYPROGRAMSDIRECTORY', "C:\\PROGRAMMES_V200\\__Programmes_unitaires\\");
    
    /*
     * Chemin des programmes génériques
     */
    define('_GENERICPROGRAMSDIRECTORY', "Planificateur\\lib\\");
    
    /*
     * Chemin du fichier d'origine de la base de données de panneaux
     */
    define('MMATV9_MDB', "C:\\V90\\FABRIDOR\\SYSTEM_DATA\\LIBs\\mmatv9.mdb");
    
    /*
     * Chemin du fichier d'origine des paramètres globaux des fichiers mpr
     */
    define('WWGLOB_VAR', "C:\\MACHINE1\\a1\\ml4\\wwglob.var");

    /*
     * Paramètres de connexion au répertoires d'impression d'étiquettes
     */
    define('LABEL_PRINT_SERVER_SHARE_INTERNAL_PATH', "Print_Server");
    define('LABEL_PRINT_SERVER_SHARE_DOMAIN', "");
    define('LABEL_PRINT_SERVER_SHARE_USERNAME', "Print_Server");
    define('LABEL_PRINT_SERVER_SHARE_PASSWORD', "");
?>