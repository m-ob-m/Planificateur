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
    include_once __DIR__ . '/../generic/controller/genericController.php';		// Classe contrôleur de cette vue
    
    $selectedGenericId = $_GET["id"] ?? 1;
    
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $generics = (new \GenericController())->getGenerics();
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
		<link rel="stylesheet" href="../../assets/css/responsive.css" />
		<link rel="stylesheet" href="../../assets/css/fabridor.css" />
		<link rel="stylesheet" href="../../assets/css/loader.css" />
		<link rel="stylesheet" href="../../assets/css/parametersTable.css"/>
		<link rel="stylesheet" href="../../assets/css/parametersForm.css"/>
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
							<a href="index.php">
								<img src="../../images/fabridor.jpg">
							</a>
						</h1>
						<span>Liste des valeurs par défaut</span>
					</div>
					
					<div style="float:right;">
    					<!-- Nav -->
    					<nav id="nav">
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
        			<form id="genericSelectionForm" action="javascript: void(0);" onsubmit="refreshParameters();">
        				<div class="formContainer">
							<div class="hFormElement">
                    			<label for="generic">Générique :
                        			<select id="generic" name="generic" onchange="$('#genericSelectionForm').submit();">
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
								<th class="spaceEfficientText" style="width:45%;">Valeur par défaut</th>
								<th class="spaceEfficientText" style="width:25%;">Description</th>
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
		
		<!--  Fenetre Modal pour message d'erreurs -->
		<div id="errMsgModal" class="modal" onclick='$(this).css({"display": "none"});'>
			<div id="errMsg" class="modal-content" style='color:#FF0000;'></div>
		</div>
		
		<!--  Fenetre Modal pour message de validation -->
		<div id="validationMsgModal" class="modal" onclick='$(this).css({"display": "none"});'>
			<div id="validationMsg" class="modal-content" style='color:#FF0000;'></div>
		</div>
		
		<!--  Fenetre Modal pour chargement -->
		<div id="loadingModal" class="modal loader-modal">
			<div id="loader" class="loader modal-content"></div>
		</div>	
		
		<!-- Scripts -->
		<script type="text/javascript" src="../../assets/js/jquery.min.js"></script>
		<script type="text/javascript" src="../../assets/js/jquery.dropotron.min.js"></script>
		<script type="text/javascript" src="../../assets/js/skel.min.js"></script>
		<script type="text/javascript" src="../../assets/js/util.js"></script>
		<script type="text/javascript" src="../../assets/js/main.js"></script>
		<script type="text/javascript" src="../../js/main.js"></script>
		<script type="text/javascript" src="../../js/toolbox.js"></script>
		<script type="text/javascript" src="js/main.js"></script>
		<script type="text/javascript" src="js/index.js"></script>
	</body>
</html>