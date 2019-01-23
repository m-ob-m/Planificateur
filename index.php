<?php 
    /**
     * \name		Planificateur de porte
     * \author    	Mathieu Grenier
     * \version		1.0
     * \date       	2017-01-18
     *
     * \brief 		Menu principal du planificateur de porte
     * \details 	Ce menu permet d'avoir un aperçu des batchs en cours
     * 
     * Licence pour la vue :
     * 	Verti by HTML5 UP
    	html5up.net | @ajlkn
    	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
     */
    
    /* INCLUDE */
    include_once __DIR__ . '/lib/config.php';	// Fichier de configuration
    include_once __DIR__ . '/lib/connect.php';	// Classe de connection à la base de données
    
    include_once __DIR__ . '/sections/batch/model/batch.php';	// Modèle d'une batch
    include_once __DIR__ . '/sections/job/model/job.php';		// Modèle d'une job
    include_once __DIR__ . '/controller/planificateur.php';		// Classe controleur de cette vue
    
    $planificateur = new PlanificateurController();
?>

<!DOCTYPE HTML>
<html style="height:100%;">
	<head>
		<title>Fabridor - Planificateur de production</title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" href="/Planificateur/assets/css/responsive.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/fabridor.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/loader.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/imageButton.css" />
		<link rel='stylesheet' href='/Planificateur/lib/fullcalendar/fullcalendar.css' />
		<link rel='stylesheet' href='/Planificateur/css/index.css' />
	</head>
	<body class="homepage" style="height:100%;">
		<div id="page-wrapper" style="display:flex; flex-direction:column; height:100%;">
    		<!-- Header -->
    		<div id="header-wrapper" class="container" style="flex:0 1 auto;">
				<header id="header">
					<!-- Logo -->
					<div id="logo">
						<h1>
							<a href="/Planificateur/index.php">
								<img src="images/fabridor.jpg">
							</a>
						</h1>
						<span>Planificateur de production</span>
					</div>
					<!-- Navigation menu -->
					<div style="display:inline-block;float:right;">
    					<nav id="nav">
    						<ul>									
    							<li>
    								<a href="sections/batch/index.php" class="imageButton">
    									<img src="images/cal16.png">
    								Planifier un nest</a>
    							</li>
    							
    							<li  class="current">
    								<a href="#" class="imageButton"  class="imageButton">
    									<img src="images/config16.png">
    								Paramètres</a>
    								<ul>
    									<li>
    										<a href="parametres/materiel/index.php" class="imageButton">
    											<img src="images/inventory.png"> 
    										Matériel</a>
    									</li>
    									<li>
    										<a href="parametres/generic/index.php" class="imageButton">
    											<img src="images/sheet16.png">
    										Génériques</a>
    									</li>
    									<li>
    										<a href="parametres/model/index.php" class="imageButton">
    											<img src="images/modele16.png">
    										Modèles de porte</a>
    									</li>
    									<li>
    										<a href="parametres/type/index.php" class="imageButton">
    											<img src="images/type16.png"> 
    										Types de porte</a>
    									</li>
    									<li>
    										<a href="#"  class="imageButton">
    											<img src="images/variables16.png"> 
    										Variables</a>
    										<ul>
    											<li>
    												<a href="parametres/vardefaut/index.php" class="imageButton">
    													<img src="images/sheet16.png"> 
    												Par défaut</a>
    											</li>
    											<li>
    												<a href="parametres/varmodtype/index.php" class="imageButton">
    													<img src="images/import16.png">
    												Modèle / Type</a>
    											</li>
    										</ul>
    									</li>
    									<li>
    										<a href="#" class="imageButton">
            									<img src="images/porte.png">
            								Programmes individuels</a>
    										<ul>
        										<li>
                    								<a href="parametres/test/index.php" class="imageButton">
                    									<img src="images/porte.png">
                    								Tests</a>
                    							</li>
                    							<li>
            										<a href="javascript:updateUnitaryPrograms();" class="imageButton">
            											<img src="images/lightning.png">
            										Programmes unitaires</a>
        										</li>
        										<li>
        											<a href="sections/machiningPrograms/linearize.php" class="imageButton">
        												<img src="images/lightning.png">
            										Simplifier un programme</a>
        										</li>
        										<li>
        											<a href="sections/machiningPrograms/merge.php" class="imageButton">
        												<img src="images/lightning.png">
            										Combiner des programmes</a>
        										</li>
    										</ul>
    									</li>
    								</ul>
    							</li>
    							<li  class="current">
    								<a href="#" class="imageButton">
    									<img src="images/help16.png">
    								Légende</a>
    								<ul class="legend">
    									<li class="state" style='background-color: #0b5788;'>Entrée</li>
    									<li class="state" style='background-color: #127031;'>En exécution</li>
    									<li class="state" style='background-color: #848482;'>Attente</li>
    									<li class="state" style='background-color: #CC6600;'>Pressant</li>
    									<li class="state" style='background-color: #990012;'>En retard</li>
    									<li class="state" style='background-color: #DAA520;'>Non-livrée</li>
    									<li class="state" style='background-color: #3B3131;'>Terminée</li>
    								</ul>
    							</li>
    						</ul>
    					</nav>
    					<div style="float: right; margin-bottom: 5px;">
    						<form id="findBatchByJobNumberForm" action="javascript: void(0);" 
    							onsubmit="findJobByProductionNumber();">
    							<label for="jobNumero">Numéro de job : </label>
    							<input type="text" name="jobNumero" value="" autocomplete="off">
    							<input type="submit" value="Trouver">
    						</form>
    					</div>
					</div>
				</header>
			</div>

			<!-- Features -->
			<div id="features-wrapper" class="container" 
				style="flex:1 1 auto; min-height:300px; position:relative; padding-bottom:0px; margin-bottom:1.5em;"> 
				<div id='calendar' style="height:100%; position:absolute;"></div>
			</div>

			<!--  Fenetre Modal pour message d'erreurs -->
    		<div id="errMsgModal" class="modal" onclick='$(this).css({"display": "none"});' style="z-index:4;">
    			<div id="errMsg" class="modal-content" style='color:#FF0000;'></div>
    		</div>
    		
    		<!--  Fenetre Modal pour message de validation -->
    		<div id="validationMsgModal" class="modal" onclick='$(this).css({"display": "none"});' style="z-index:4;">
                <!-- Modal content -->
    			<div id="validationMsg" class="modal-content" style='color:#FF0000;'></div>
    		</div>
    		
    		<!--  Fenetre Modal pour chargement -->
    		<div id="loadingModal" class="modal loader-modal" style="z-index:4;">
    			<div id="loader" class="loader modal-content"></div>
    		</div>			

		    <!-- Scripts -->
			<script src='/Planificateur/lib/fullcalendar/lib/jquery.min.js'></script>
			<script src='/Planificateur/lib/fullcalendar/lib/jquery-ui.min.js'></script>
			<script src='/Planificateur/lib/fullcalendar/lib/moment.min.js'></script>
			<script src='/Planificateur/lib/fullcalendar/fullcalendar.js'></script>	
			<script src='/Planificateur/lib/fullcalendar/locale/fr-ca.js'></script>
			<script src="/Planificateur/assets/js/jquery.dropotron.min.js"></script>
			<script src="/Planificateur/assets/js/skel.min.js"></script>
			<script src="/Planificateur/assets/js/util.js"></script>
			<script src="/Planificateur/assets/js/main.js"></script>
			
			<script src="js/main.js"></script>
			<script src="js/index.js"></script>
		</div>
	</body>
</html>