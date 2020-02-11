<?php
    /**
     * \name		Planificateur de porte
    * \author    	Mathieu Grenier
    * \version		1.0
    * \date       	2017-01-27
    *
    * \brief 		Menu de modification des valeurs des variables des génériques
    * \details 		Menu de modification des valeurs des variables des génériques
    *
    * Licence pour la vue :
    * 	Verti by HTML5 UP
    html5up.net | @ajlkn
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
    */
    
    /* INCLUDE */
	require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/generic/controller/genericController.php"; // Classe contrôleur de cette vue
	require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php"; // Classe contrôleur de cette vue
    
    // Initialize the session
	session_start();
        
	// Check if the user is logged in, if not then redirect him to login page
	if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
		if(!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest")
		{
			throw new \Exception("You are not logged in.");
		}
		else
		{
			header("location: /Planificateur/lib/account/logIn.php");
		}
		exit;
	}

	// Getting a connection to the database.
	$db = new \FabPlanConnection();
		
	// Closing the session to let other scripts use it.
	session_write_close();
    
    $selectedGenericId = $_GET["id"] ?? 1;
    
    try
    {
        $db->getConnection()->beginTransaction();
        $generics = (new \GenericController($db))->getGenerics();
        $db->getConnection()->commit();
    }
    catch(\Exception $e)
    {
        $db->getConnection()->rollback();
        throw $e;
    }
    finally
    {
        $db = null;
    }
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Fabridor - Liste des valeurs par défaut</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="/Planificateur/assets/css/responsive.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/fabridor.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/loader.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersTable.css"/>
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersForm.css"/>
		<link rel="stylesheet" href="/Planificateur/assets/css/imageButton.css">
	</head>
	<body class="homepage">
		<div id="page-wrapper">
			<!-- Header -->
			<div id="header-wrapper">
				<header id="header" class="container">
					<!-- Logo -->
					<div id="logo">
						<h1>
							<a href="index.php">
								<img src="../../images/fabridor.jpg">
							</a>
						</h1>
						<span>Liste des valeurs par défaut</span>
					</div>
					
					<div style="float:right;">
    					<!-- Nav -->
    					<nav id="nav" style="display: block;">
    						<ul>
    							<li>
    								<a href="javascript: void(0);" onclick="saveConfirm();" class="imageButton">
    									<img src="../../images/save.png">
    								Sauvegarder</a>
    							</li>
    							<li>
    								<a href="../../index.php" class="imageButton">
    									<img src="../../images/exit.png">
    								Sortir</a>
    							</li>
    						</ul>
    					</nav>
    				</div>
				</header>
			</div>
			
		    <!-- Parameters -->
			<div id="features-wrapper">
				<div class="container">
					<!-- Sélection du générique dont on veut éditer les paramètres par défaut -->
        			<form id="genericSelectionForm" action="javascript: void(0);">
        				<div class="formContainer">
							<div class="hFormElement">
                    			<label for="generic">Générique :
                        			<select id="generic" name="generic" onchange="refreshParameters();">
                        				<?php foreach($generics as $generic):?>
                        					<?php $selected =  (($selectedGenericId == $generic->getId()) ? "selected" : ""); ?>
                        					<option value="<?= $generic->getId(); ?>" <?= $selected; ?>>
                        					   <?= $generic->getDescription(); ?>
                        					</option>
                        				<?php endforeach;?>
                        			</select>
                    			</label>
                    		</div>
                    	</div>
        			</form>
        			
					<h1 style="color:darkred;">
						Attention : La modification de ces variables peut entrainer des problèmes indésirables! 
						Procéder avec prudence.
					</h1>
					<table id="parametersTable" class="parametersTable" style="width:100%;">
						<thead>
							<tr>
								<th class="firstVisibleColumn spaceEfficientText" style="width:10%;">Clé</th>
								<th class="spaceEfficientText" style="width:35%;">Valeur par défaut</th>
								<th class="spaceEfficientText" style="width:35%;">Description</th>
								<th class="spaceEfficientText" style="width:10%;">Édition rapide</th>
								<th class="lastVisibleColumn spaceEfficientText" style="width:10%;"></th>
							</tr>
						</thead>
						<tbody>
							<!-- Paramètres -->
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
		<!--  Fenetre Modal pour chargement -->
		<div id="loadingModal" class="modal loader-modal">
			<div id="loader" class="loader modal-content"></div>
		</div>	
		
		<!-- Scripts -->
		<script type="text/javascript" src="/Planificateur/assets/js/ajax.js"></script>
		<script type="text/javascript" src="/Planificateur/assets/js/docReady.js"></script>
		<script type="text/javascript" src="/Planificateur/js/main.js"></script>
		<script type="text/javascript" src="/Planificateur/js/toolbox.js"></script>
		<script type="text/javascript" src="/Planificateur/parametres/vardefaut/js/main.js"></script>
		<script type="text/javascript" src="/Planificateur/parametres/vardefaut/js/index.js"></script>
	</body>
</html>