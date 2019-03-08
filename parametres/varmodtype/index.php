<?php
    /**
     * \name		Planificateur de porte
     * \author    	Marc-Olivier Bazin-Maurice
     * \version		1.0
     * \date       	2018-03-27
     *
     * \brief 		Menu de modification des valeurs des variables des modèles/types
     * \details 	Menu de modification des valeurs des variables des modèles/types
     *
     * Licence pour la vue :
     * 	Verti by HTML5 UP
     html5up.net | @ajlkn
     Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
     */
    
    /* INCLUDE */
    include_once __DIR__ . '/../type/controller/typeController.php';		// Classe contrôleur de Type
    include_once __DIR__ . '/../model/controller/modelController.php';		// Classe contrôleur de Model
    
    $selectedModelId = isset($_GET["modelId"]) ? $_GET["modelId"] : 7000;
    $selectedTypeNo = isset($_GET["typeNo"]) ? $_GET["typeNo"] : 0;
    
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $types = (new \TypeController())->getTypes();
        $models = (new \ModelController())->getModels();
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
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="/Planificateur/assets/css/responsive.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/fabridor.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/loader.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersTable.css"/>
		<link rel="stylesheet" href="/Planificateur/assets/css/imageButton.css">
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersForm.css"/>
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
								<img src="/Planificateur/images/fabridor.jpg">
							</a>
						</h1>
						<span>Variables modèle-type</span>
					</div>
					
					<div style="display:inline-block;float:right;">
    					<!-- Nav -->
    					<nav id="nav">
    						<ul>
    							<li>
    								<a href="javascript: void(0);" onclick="exportParameters();" class="imageButton">
    									<img src="/Planificateur/images/export32.png">
    								Exporter</a>
    							</li>
    							<li>
    								<a href="javascript: void(0);" onclick="saveConfirm();" class="imageButton">
    									<img src="/Planificateur/images/save.png">
    								Sauvegarder</a>
    							</li>
    							<li>
    								<a href="/Planificateur/index.php" class="imageButton">
    									<img src="/Planificateur/images/exit.png">
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
					<!-- Sélection du modèle/type dont on veut éditer les paramètres par défaut -->
        			<form id="modelTypeSelectionForm" class="parametersForm" action="javascript: void(0);" 
        				onSubmit="refreshParameters();">
    					<div class="formContainer">
							<div class="hFormElement">
            					<label for="type">Type :
                					<select id="type" name="type" onchange="$('#modelTypeSelectionForm').submit();">
                        				<?php foreach($types as $type):?>
                        					<?php $selected =  (($selectedTypeNo == $type->getImportNo()) ? "selected" : ""); ?>
                        					<option value=<?= $type->getImportNo(); ?> <?= $selected; ?>>
                        						<?= $type->getDescription(); ?>
                        					</option>
                        				<?php endforeach;?>
                        			</select>
                        		</label>
                    		</div>
                    		<div class="hFormElement">
            					<label for="model">Modèle :
                        			<select id="model" name="model" onchange="$('#modelTypeSelectionForm').submit();">
                        				<?php foreach($models as $model):?>
                        					<?php if($model->getId() >= 10): ?>
                            					<?php $selected =  (($selectedModelId == $model->getId()) ? "selected" : ""); ?>
                            					<option value=<?= $model->getId(); ?> <?= $selected; ?>>
                            						<?= $model->getDescription(); ?>
                            					</option>
                            				<?php endif;?>
                        				<?php endforeach;?>
                        			</select>
                        		</label>
                    		</div>
                    	</div>
            		</form>
					<table id="parametersTable" class="parametersTable" style="width:100%;">
						<thead>
							<tr>
								<th class="firstVisibleColumn spaceEfficientText" style="width:10%;">Clé</th>
								<th class="spaceEfficientText" style="width:35%;">Valeur</th>
								<th class="spaceEfficientText" style="width:20%;">Description</th>
								<th class="lastVisibleColumn spaceEfficientText" style="width:35%;">Valeur par défaut</th>
								<th style="display:none">Valeur précédente</th>
							</tr>
						</thead>
						<tbody>
							<!-- Parameters are inserted here. -->
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
		<script type="text/javascript" src="/Planificateur/assets/js/jquery.min.js"></script>
		<script type="text/javascript" src="/Planificateur/assets/js/jquery.dropotron.min.js"></script>
		<script type="text/javascript" src="/Planificateur/assets/js/skel.min.js"></script>
		<script type="text/javascript" src="/Planificateur/assets/js/util.js"></script>
		<script type="text/javascript" src="/Planificateur/assets/js/main.js"></script>
		<script type="text/javascript" src="/Planificateur/js/main.js"></script>
		<script type="text/javascript" src="/Planificateur/js/toolbox.js"></script>
		<script type="text/javascript" src="js/main.js"></script>
		<script type="text/javascript" src="js/index.js"></script>
	</body>
</html>