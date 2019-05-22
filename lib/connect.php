<?php

/**
 * \name		FabPlanConnection
 * \author    	Mathieu Grenier
 * \version		1.0
 * \date       	2017-01-16
 *
 * \brief 		Couche d'abstraction pour la connection � la base de donn�es
 */

class FabPlanConnection 
{
	private $_pdo;
	
	function __construct(){
		
		$host = DB_HOST;
		$db   = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		$charset = 'utf8';
		
		$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
		$opt = array(
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false
		);
		$this->_pdo = new PDO($dsn, $user, $pass, $opt);
		
	}
	
	function __destruct(){
		$_pdo = NULL;
	}
	
	
	/**
	 * Retourne la connexion \PDO à la base de données.
	 * 
	 * @return \PDO
	 */
	public function getConnection() : \PDO
	{
		return $this->_pdo;
	}
}

class MYSQLDatabaseLockingReadTypes
{
    public const NONE = 0;
    public const FOR_SHARE = 1;
    public const FOR_UPDATE = 2;
    private $lockingReadType;
    
    public function __construct(int $lockingReadType = 0)
    {
        $this->lockingReadType = $lockingReadType;
    }
    
    public function toLockingReadString()
    {
        switch($this->lockingReadType)
        {
            case self::NONE: 
                return "";
                break;
            case self::FOR_SHARE:
                return "FOR SHARE";
                break;
            case self::FOR_UPDATE:
                return "FOR UPDATE";
                break;
            default:
                throw new \Exception("Invalid locking read type identifier provided.");
        }
    }
}

?>