<?php
/**
     * \name		Planificateur de porte
    * \author    	Mathieu Grenier
    * \version		1.0
    * \date       	2017-01-27
    *
    * \brief 		Menu de création / modification / suppression de matériel
    * \details 		Menu de création / modification / suppression de matériel
    *
    * Licence pour la vue :
    * 	Verti by HTML5 UP
    html5up.net | @ajlkn
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
    */
    
    /* INCLUDE */
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/material/controller/materialCtrl.php";
	require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
	
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
    
    $materials= array();
    try
    {
        $db->getConnection()->beginTransaction();
        $materials = (new \MaterialController($db))->getMaterials();
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
		<title>Fabridor - Liste des matériaux</title>
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<link rel="stylesheet" href="/Planificateur/assets/css/responsive.css"/>
		<link rel="stylesheet" href="/Planificateur/assets/css/fabridor.css"/>
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersTable.css"/>
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
							<a href="../../index.php">
								<img src="../../images/fabridor.jpg">
							</a>
						</h1>
						<span>Liste des matériaux</span>
					</div>
					
					<div style="float:right;">
    					<nav id="nav" style="display: block;">
    						<ul>
    							<li>
    								<a href="javascript:openMaterial();" class="imageButton">
    									<img src="../../images/add.png">
    								Ajouter</a>
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
			
		     <!-- Features -->
		     <div id="features-wrapper">
				<div class="container">
    				<table class="parametersTable" style="width:100%">
    					<thead>
    						<tr>
    							<th class="firstVisibleColumn" style="width:60px;">ID</th>
    							<th>Description</th>
    							<th style="width:100px;">Code SIA</th>
    							<th style="width:100px;">Code CutRite</th>
    							<th style="width:100px;">Épaisseur</th>
    							<th style="width:100px;">Essence</th>
    							<th style="width:60px;">Grain</th>
    							<th class="lastVisibleColumn" style="width:60px;">MDF</th>
    						</tr>
    					</thead>
    					<tbody>		
    						<?php foreach ($materials as $material): ?>											
    							<tr class="link" onclick="javascript:openMaterial(<?= $material->getId(); ?>)">
    								<!-- id -->
    								<td class="firstVisibleColumn"><?= $material->getId(); ?></td>
    								<!-- Description -->
    								<td><?= $material->getDescription(); ?></td>
    								<!-- Code SIA -->
    								<td><?= $material->getCodeSIA(); ?></td>
    								<!-- Code CutRite -->
    								<td><?= $material->getCodeCutRite(); ?></td>
    								<!-- Épaisseur -->
    								<td><?= $material->getEpaisseur(); ?></td>
    							    <!-- Essence -->
    								<td><?= $material->getEssence(); ?></td>
    								<!-- Grain -->
    								<td><?= $material->getGrain(); ?></td>
    								<!-- MDF -->
    								<td class="lastVisibleColumn"><?= $material->getEstMDF(); ?></td>
    							</tr>
    						<?php endforeach; ?>
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
		<script type="text/javascript" src="/Planificateur/parametres/material/js/main.js"></script>

	</body>
</html>