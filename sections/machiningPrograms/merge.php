<?php
	/**
	 * \name		linearize.php
	 * \author    	Marc-Olivier Bazin-Maurice
	 * \version		1.0
	 * \date       	2018-12-18
	 *
	 * \brief 		Interface de linéarisation des programmes mpr
	 * \details 	Cette interface permet de linéariser (simplifier) un fichier mpr.
	 *
	 * Licence pour la vue :
	 * 	Verti by HTML5 UP
	 html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
	*/

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
		<title>Fabridor - Programmes d'usinage</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="../../assets/css/responsive.css" />
		<link rel="stylesheet" href="../../assets/css/fabridor.css" />
		<link rel="stylesheet" href="../../assets/css/parametersTable.css" />
		<link rel="stylesheet" href="../../assets/css/imageButton.css" />
		<link rel="stylesheet" href="../../assets/css/loader.css" />
	</head>
	<body class="homepage">
		<div id="page-wrapper">
			<div id="header-wrapper">
				<header id="header" class="container">
					<!-- Logo -->
					<div id="logo">
						<h1>
							<a href="../../index.php">
								<img src="../../images/fabridor.jpg">
							</a>
						</h1>
						<span>Combiner des programmes</span>
					</div>
					<div style="float: right;">
						<nav id="nav" style="display: block;">
							<ul>
								<li>
									<a href="../../sections/machiningPrograms/linearize.php" class="imageButton">
										<img src="../../images/lightning.png">
									Simplifier un programme</a>
								</li>
								<li>
									<a href="javascript: void(0);" onclick="goToIndex();" class="imageButton">
										<img src="../../images/exit.png"> 
									Sortir</a>
								</li>
							</ul>
						</nav>
					</div>
				</header>
			</div>

			<!-- Features -->
			<div id="features-wrapper">
				<div class="container">
					<div style="display: flex; flex-flow: row; width: 100%; margin-top: 5px; margin-bottom: 5px;">
						<div style="flex: 0 0 auto; margin-right: 5px;">Fichiers en entrée : </div>
						<div id="inputFilesContainer" style="flex: 1 0 auto;"></div>
					</div>
					<div style="display: flex; flex-flow: row; width: 100%; margin-top: 5px; margin-bottom: 5px;">
    					<label for="outputFileName" style="flex: 0 0 auto; margin-right: 5px;">Fichier en sortie : </label>
    					<input id="outputFileName" type="text" value="" style="flex: 1 0 auto;">
    				</div>
					<div style="margin-top: 5px; margin-bottom: 5px;">
						<button type="button" onclick="mergePrograms();">Combiner</button>
					</div>
   				</div>
			</div>
		</div>

		<!--  Fenêtre Modal pour message d'erreurs -->
		<div id="errMsgModal" class="modal" onclick='this.style.display = "none";' >
			<div id="errMsg" class="modal-content" style='color: #FF0000;'></div>
		</div>
		
		<!--  Fenêtre Modal pour message de validation -->
		<div id="validationMsgModal" class="modal" onclick='this.style.display = "none";' >
			<div id="validationMsg" class="modal-content" style='color: #FF0000;'></div>
		</div>
		
		<!--  Fenêtre Modal pour chargement -->
		<div id="loadingModal" class="modal loader-modal">
			<div id="loader" class="loader modal-content"></div>
		</div>

		<script type="text/javascript" src="../../assets/js/ajax.js"></script>
		<script type="text/javascript" src="../../assets/js/docReady.js"></script>
		<script type="text/javascript" src="../../js/main.js"></script>
		<script type="text/javascript" src="../../js/toolbox.js"></script>
		<script type="text/javascript" src="js/main.js"></script>
		<script type="text/javascript" src="js/merge.js"></script>
	</body> 
</html>