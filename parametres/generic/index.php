<?php
    /**
     * \name		Planificateur de porte - visualisation des génériques
    * \author    	Marc-Olivier bazin-Maurice
    * \version		1.0
    * \date       	2018-03-21
    *
    * \brief 		Liste des génériques
    * \details 		Liste des génériques
    *
    * Licence pour la vue :
    * 	Verti by HTML5 UP
    html5up.net | @ajlkn
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
    */
    
    /* INCLUDE */
	require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/generic/controller/genericController.php";
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
    
    $generics = array();
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
		<title>Fabridor - Liste des programmes génériques</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="/Planificateur/assets/css/responsive.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/fabridor.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/imageButton.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersTable.css" />
	</head>
	<body class="homepage">
		<div id="page-wrapper">
			<!-- Header -->
			<div id="header-wrapper">
				<header id="header" class="container">
					<!-- Logo -->
					<div id="logo">
						<h1>
							<a href="index.php"><img src="../../images/fabridor.jpg"></a>
						</h1>
						<span>Liste des génériques</span>
					</div>
					
					<div style="float:right;">
    					<!-- Nav -->
    					<nav id="nav" style="display: block;">
    						<ul>
    							<li>
    								<a href="javascript: void(0);" onclick="openGeneric();" class="imageButton">
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
			
			<div id="features-wrapper">
				<div class="container">
					<table style="width:100%;" class="parametersTable">
						<thead>
							<tr>
								<th class="firstVisibleColumn" style="width:10%;">ID</th>
								<th style="width:30%;">Nom de fichier</th>
								<th class="lastVisibleColumn" style="width:60%;">Description</th>
							</tr>
						</thead>
						<tbody>
                            <?php if(!empty($generics)): ?>
								<?php foreach ($generics as $generic): ?>
									<tr class="link" onclick="javascript:openGeneric(<?= $generic->getId(); ?>)">
										<!-- Id -->
										<td class="firstVisibleColumn" >
											<?= $generic->getId(); ?>
										</td>
										<!-- Nom de fichier -->
										<td>
											<?= $generic->getFilename(); ?>
										</td>
										<!-- Description -->
										<td class="lastVisibleColumn">
											<?= $generic->getDescription(); ?>
										</td>
									</tr>
							  	<?php endforeach; ?>
						  	<?php endif; ?>
					  	</tbody>
					</table>
				</div>
			</div>
		</div>

	    <!-- Scripts -->
		<script type="text/javascript" src="/Planificateur/assets/js/ajax.js"></script>
		<script type="text/javascript" src="/Planificateur/assets/js/docReady.js"></script>
		<script type="text/javascript" src="/Planificateur/js/main.js"></script>
		<script type="text/javascript" src="/Planificateur/js/toolbox.js"></script>
		<script type="text/javascript" src="/Planificateur/parametres/generic/js/main.js"></script>
	</body>
</html>