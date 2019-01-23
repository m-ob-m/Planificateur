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

?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	<body>
		<?= str_replace("\r\n", "<br>", htmlspecialchars(utf8_encode(fread($mprFile, filesize($mprPath))), ENT_QUOTES, "UTF-8", true)); ?>
	</body>
</html>

<?php 
fclose($mprFile);
?>