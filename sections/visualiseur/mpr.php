<?php
	/**
	 * \name		mpr.php
	* \author    	Mathieu Grenier
	* \version		1.0
	* \date       	2017-02-10
	*
	* \brief 		Affiche le contenu d'un fichier MPR
	* \details 		Affiche le contenu d'un fichier MPR
	*/

	// INCLUDE
	include '../../lib/config.php';	// Fichier de configuration

	$mprPath = CR_FABRIDOR . "SYSTEM_DATA\\mpr\\" . $_GET["mprName"];
	$mprFile = fopen($mprPath, "r") or die("Unable to open mpr file!");

    // Initialize the session
	session_start();
                                                                            
	// Check if the user is logged in, if not then redirect him to login page
	if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			throw new \Exception("You are not logged in.");
		}
		else
		{
			header("location: /Planificateur/lib/account/logIn.php");
		}
		exit;
	}

	// Closing the session to let other scripts use it.
	session_write_close();
    
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	<body>
		<?php $fileContents = utf8_encode(fread($mprFile, filesize($mprPath))); ?>
		<?= str_replace("\r\n", "<br>", htmlspecialchars($fileContents, ENT_QUOTES, "UTF-8", true)); ?>
	</body>
</html>

<?php fclose($mprFile); ?>