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
    define("DATABASE_CONNECTION_PASSWORDS", array(
		"dbadmin@localhost" => "",
		"Programmer@localhost" => "1a5MkNtcL9xX9Av6",
		"BatchEntryTechnician@localhost" => "xwn0DS3LFVcu4Qhu",
        "LabelPC@localhost" => "agsdjKFk9itkTTUj",
        "CutQueue@localhost" => "dwIKRcHnczAUVd80",
        "Backup@localhost" => "1vl9QFrxwoEMnE9R", 
        "Authenticator@localhost" => "OuNufLqebFoBx4Fb",
        "dbadmin@127.0.0.1" => "",
		"Programmer@127.0.0.1" => "1a5MkNtcL9xX9Av6",
		"BatchEntryTechnician@127.0.0.1" => "xwn0DS3LFVcu4Qhu",
        "LabelPC@127.0.0.1" => "agsdjKFk9itkTTUj",
        "CutQueue@127.0.0.1" => "dwIKRcHnczAUVd80",
        "Backup@127.0.0.1" => "1vl9QFrxwoEMnE9R", 
        "Authenticator@127.0.0.1" => "OuNufLqebFoBx4Fb"
	));
    define("DATABASE_HOST_NAME", "127.0.0.1");
    define("DATABASE_NAME", "fabplan");
    define("DATABASE_AUTHENTICATION_USER_NAME", "Authenticator");
    define("DATABASE_CONNECTION_CHARACTER_SET", "utf8");
    
    /*
     * Paramètre des chemins d'accès aux répertoires de CutRite
     */
    define("CR_FABRIDOR", "C:\\V90\\FABRIDOR\\");
    
    /*
     * Chemin d'accès du répertoire de la Vantage 200
     */
    define("V_200", "\\\\srvcuisine\\Homag\\__vantage_200\\");
    
    /*
     * Chemin d'enregistrement des fichiers tests
     */
    define("_TESTDIRECTORY", "C:\\PROGRAMMES_V200\\__TEST\\");
    
    /*
     * Chemin d'enregistrement des fichiers de programmes unitaires
     */
    define("_UNITARYPROGRAMSDIRECTORY", "C:\\PROGRAMMES_V200\\__Programmes_unitaires\\");
    
    /*
     * Chemin des programmes génériques
     */
    define("_GENERICPROGRAMSDIRECTORY", "Planificateur\\lib\\");
    
    /*
     * Chemin du fichier d'origine de la base de données de panneaux
     */
    define("MMATV9_MDB", "C:\\V90\\FABRIDOR\\SYSTEM_DATA\\LIBs\\mmatv9.mdb");
    
    /*
     * Chemin du fichier d'origine des paramètres globaux des fichiers mpr
     */
    define("WWGLOB_VAR", "C:\\MACHINE1\\a1\\ml4\\wwglob.var");

    /*
     * Paramètres de connexion au répertoires d'impression d'étiquettes
     */
    define("LABEL_PRINT_SERVER_SHARE_INTERNAL_PATH", "Print_Server");
    define("LABEL_PRINT_SERVER_SHARE_DOMAIN", "");
    define("LABEL_PRINT_SERVER_SHARE_USERNAME", "Print_Server");
    define("LABEL_PRINT_SERVER_SHARE_PASSWORD", "");
?>