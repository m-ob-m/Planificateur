<?php

/**
 * \name		FabPlanConnection
 * \author    	Mathieu Grenier
 * \version		1.0
 * \date       	2017-01-16
 *
 * \brief 		Couche d'abstraction pour la connection � la base de donn�es
 */

class FabPlanConnection {
	
	private $_pdo;
	
	function __construct(){
		
		$host = DB_HOST;
		$db   = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		$charset = 'utf8';
		
		$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
		$opt = [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => false,
		];
		$this->_pdo = new PDO($dsn, $user, $pass, $opt);
		
	}
	
	function __destruct(){
		$_pdo = NULL;
	}
	
	
	/**
	 * \name		getConnection
	 * \author    	Mathieu Grenier
	 * \version		1.0
	 * \date       	2017-01-16
	 *
	 * \brief       Retourne la connection PDO � la base de donn�es
	 *
	 * \return    	Connection PDO � la base de donn�es
	 */
	public function getConnection(){
		return $this->_pdo;
	}
	
	
	
}

?>