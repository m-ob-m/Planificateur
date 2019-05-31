<?php
    /**
     * \name		Planificateur de porte
    * \author    	Marc-Olivier Bazin-Maurice
    * \version		1.0
    * \date       	2017-01-27
    *
    * \brief 		Menu de création / modification / suppression de type
    * \details 		Menu de création / modification / suppression de type
    *
    * Licence pour la vue :
    * 	Verti by HTML5 UP
    html5up.net | @ajlkn
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
    */
    
    /* INCLUDE */
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Fabridor - Liste des tests</title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="../../assets/css/responsive.css" />
		<link rel="stylesheet" href="../../assets/css/fabridor.css" />
		<link rel="stylesheet" href="../../assets/css/parametersTable.css"/>
		<link rel="stylesheet" href="../../assets/css/imageButton.css">
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
						<span>Liste des tests</span>
					</div>
					
					<div style="float:right;">
    					<!-- Nav -->
    					<nav id="nav" style="display: block;">
    						<ul>
    							<li>
    								<a href="../../parametres/test/view.php" class="imageButton">
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
					<form id="testsSelectionForm" class="parametersForm" action="javascript: void(0);" 
        				onSubmit="refreshTests();">
    					<div class="formContainer" style="margin-bottom: 10px;">
							<div class="hFormElement" style="display: inline-block;">
            					<label for="startDate">Du :
                					<input id="startDate" name="startDate" type="datetime-local" step="1" 
                						onblur="$('#testsSelectionForm').submit();">
                        		</label>
                    		</div>
                    		<div class="hFormElement" style="display: inline-block;">
            					<label for="endDate">Au :
                					<input id="endDate" name="endDate" type="datetime-local" step="1" 
                						onblur="$('#testsSelectionForm').submit();">
                        		</label>
                    		</div>
                    	</div>
            		</form>
					<table id="parametersTable" class="parametersTable" style="width:100%">
						<thead>
							<tr>
								<th class="firstVisibleColumn" style="width:16.66%;">ID</th>
								<th style="width:16.66%;">Nom</th>
								<th style="width:16.66%;">Modèle</th>
								<th style="width:16.66%;">Type</th>
								<th style="width:16.66%;">Générique</th>
								<th class="lastVisibleColumn" style="width:16.66%;">Dernière modification</th>
							</tr>
						</thead>
						<tbody>
					  	</tbody>
					</table>
				</div>
			</div>
		</div>
		
		<!--  Fenêtre Modal pour message d'erreurs -->
		<div id="errMsgModal" class="modal" onclick='this.style.display = "none";'>
			<div id="errMsg" class="modal-content" style='color:#FF0000;'></div>
		</div>
		
		<!--  Fenêtre Modal pour message de validation -->
		<div id="validationMsgModal" class="modal" onclick='this.style.display = "none";'>
			<div id="validationMsg" class="modal-content" style='color:#FF0000;'></div>
		</div>
		
		<!--  Fenêtre Modal pour chargement -->
		<div id="loadingModal" class="modal loader-modal">
			<div id="loader" class="loader modal-content"></div>
		</div>
			
	    <!-- Scripts -->
	    <script type="text/javascript" src="../../assets/js/moment.min.js"></script>
		<script type="text/javascript" src="../../assets/js/moment-timezone.js"></script>
		<script type="text/javascript" src="../../assets/js/ajax.js"></script>
		<script type="text/javascript" src="../../assets/js/docReady.js"></script>
		<script type="text/javascript" src="../../js/main.js"></script>
		<script type="text/javascript" src="../../js/toolbox.js"></script>
		<script type="text/javascript" src="js/index.js"></script>
		<script type="text/javascript" src="js/test.js"></script>
		<script type="text/javascript" src="js/main.js"></script>
	</body>
</html>