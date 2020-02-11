<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/config.php";

/**
 * \name		FabPlanConnection
 * \author    	Mathieu Grenier
 * \version		1.0
 * \date       	2017-01-16
 *
 * \brief 		Couche d'abstraction pour la connection à la base de données
 */

class FabPlanConnection 
{
	private $_pdo;
	private $_userName;
	private $_hostName;
	private $_password;
	private $_databaseName;
	private $_characterSet;
	private $_options;
	
	function __construct(string $mysqlUserName = null)
	{	
		$this->_databaseName = DATABASE_NAME;
		$this->_characterSet = DATABASE_CONNECTION_CHARACTER_SET;
		$this->_options = array(
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			\PDO::ATTR_EMULATE_PREPARES => false
		);

		if($mysqlUserName !== null) 
		{
			$this->_userName = $mysqlUserName;
			$this->_hostName = DATABASE_HOST_NAME;
		}
		elseif(isset($_SESSION["username"]))
		{
			$this->determineMysqlUserNameFromCurrentSession();
		}
		else 
		{
			throw new \Exception("There is no active session.");
		}

		$this->_password = DATABASE_CONNECTION_PASSWORDS["{$this->_userName}@{$this->_hostName}"];
		
		
		try
		{
			$this->_pdo = new \PDO(
				"mysql:host={$this->_hostName};dbname={$this->_databaseName};charset={$this->_characterSet}", 
				$this->_userName, 
				$this->_password, 
				$this->_options
			);
		}
		catch (\Exception $e)
		{
			// Create new Exception to avoid giving the password away in the default Exception message.
			throw new \Exception(
				"User \"{$this->_userName}\"@\"{$this->_hostName}\" failed to connect to the \"{$this->_databaseName}\" database."
			);
		}
		
	}

	private function determineMysqlUserNameFromCurrentSession()
	{
		$pdo = null;
		try
		{

			$pdo = new \PDO(
				"mysql:host=" . DATABASE_HOST_NAME . ";dbname={$this->_databaseName};charset={$this->_characterSet}", 
				DATABASE_AUTHENTICATION_USER_NAME, 
				DATABASE_CONNECTION_PASSWORDS[DATABASE_AUTHENTICATION_USER_NAME . "@" . DATABASE_HOST_NAME], 
				$this->_options
			);
		}
		catch (\Exception $e)
		{
			// Create new Exception to avoid giving the password away in the default Exception message.
			throw new \Exception("Failed to connect to the \"" . DATABASE_NAME . "\" database for user authentication.");
		}

		$userName = $_SESSION["username"];
		$stmt = $pdo->prepare("
			SELECT `u`.`mysqlHostName` AS `HostName`, `u`.`mysqlUserName` AS `UserName` 
			FROM `fabplan`.`users` AS `u`
			WHERE `u`.`username` = :userName;
		");
		$stmt->bindValue(":userName", $userName, \PDO::PARAM_STR);
		$stmt->execute();

		if($stmt->rowCount() === 1)
		{
			if($row = $stmt->fetch(\PDO::FETCH_ASSOC))
			{
				$this->_userName = $row["UserName"];
				$this->_hostName = $row["HostName"];
			}
			else
			{
				throw new \Exception("Failed to fetch connection information for Fabplan user \"{$userName}\".");
			}
		}
		else 
		{
			throw new \Exception("Fabplan user \"{$userName}\" doesn't exist.");
		}
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