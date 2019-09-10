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
    require_once __DIR__ . '/../type/controller/typeController.php';		// Classe contrôleur la Type
    require_once __DIR__ . '/../model/controller/modelController.php';		// Classe contrôleur de Model
	require_once __DIR__ . '/../test/controller/testController.php';        // Classe contrôleur de Test
	
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
    
    $types = array();
    $models = array();
    $test = null;
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $types = (new \TypeController())->getTypes();
        $models = (new \ModelController())->getModels();
        $defaultModel = $models[0] ?? null;
        $defaultType = $types[0] ?? null;
        $test = isset($_GET["id"]) ? \Test::withID($db, $_GET["id"]) : new \Test(null, "", $defaultModel, $defaultType);
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
    
    $disabled = (($test->getId() === null) ? null : "disabled");
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Fabridor - Création de tests</title>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="/Planificateur/assets/css/responsive.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/fabridor.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersTable.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/loader.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersForm.css" />
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
						<span>Création de tests</span>
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
    							<?php if($test->getId() !== null): ?>
        							<li>
        								<a href="javascript: void(0);" onclick="deleteConfirm();" class="imageButton">
        									<img src="../../images/cancel16.png">
        								Supprimer</a>
        							</li>
    							<?php endif; ?>
    							<li>
    								<a href="index.php" class="imageButton">
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
				<div id="parametersFormContainer" class="container">
					<!-- Sélection du modèle/type dont on veut éditer les paramètres par défaut -->
					<form id="parametersForm" class="parametersForm" action="javascript: void(0);">
						<div class="formContainer">
        					<div class="hFormElement">
            					<label for="type">Type : 
                					<select id="type" <?= $disabled; ?> onchange="refreshParameters();">
                						<?php foreach($types as $type):?>
                							<?php $typeNo = $type->getImportNo(); ?>
                							<?php $testTypeImportNo = $test->getType()->getImportNo(); ?>
                        					<?php $selected =  (($testTypeImportNo == $typeNo) ? "selected" : ""); ?>
                        					<option value=<?= $type->getImportNo(); ?> <?= $selected; ?>>
                        						<?= $type->getDescription(); ?>
                        					</option>
                        				<?php endforeach;?>
                        			</select>
                    			</label>
                			</div>
                			<div class="hFormElement">
            					<label for="model">Modèle : 
                        			<select id="model" <?= $disabled; ?> onchange="refreshParameters();">
                					<?php foreach($models as $model):?>
                						<?php if($model->getId() >= 2): ?>
                							<?php $modelId = $model->getId(); ?>
                							<?php $testModelId = $test->getModel()->getId(); ?>
                        					<?php $selected =  (($testModelId == $modelId) ? "selected" : ""); ?>
                        					<option value=<?= $model->getId(); ?> <?= $selected; ?>>
                        						<?= $model->getDescription(); ?>
                        					</option>
                    					<?php endif; ?>
                					<?php endforeach;?>
                        			</select>
                    			</label>
                    		</div>
                    		<div class="hFormElement">
                				<label for="name">Nom du test : 
                    				<input id="name" name="name" autocomplete="off"
                    					value="<?= $test->getName(); ?>" <?= $disabled; ?>>
                    			</label>
                    		</div>
                    		<br>
                    		<div id="mprFileDialogContainer" class="hFormElement">
                				<label for="mprFileDialog">Sélectionner un fichier : 
                    				<input type="file" id="mprFileDialog" name="mprFileDialog" value="">
                    			</label>
                    		</div>
                    		<div class="hFormElement" style="display:none;">
            					<input id="id" disabled hidden=1 value=<?= $test->getId(); ?>>
                    		</div>
                		</div>
            		</form>
				</div>
				<div id="parametersEditorContainer" class="container">
					<!-- Insert parameters editor here -->
				</div>
			</div>
		</div>
		
		<!--  Fenetre Modal pour message d'erreurs -->
		<div id="errMsgModal" class="modal" onclick='this.style.display = "none";'>
			<div id="errMsg" class="modal-content" style='color:#FF0000;'></div>
		</div>
		
		<!--  Fenetre Modal pour message de validation -->
		<div id="validationMsgModal" class="modal" onclick='this.style.display = "none";'>
			<div id="validationMsg" class="modal-content" style='color:#FF0000;'></div>
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
		<script type="text/javascript" src="/Planificateur/parametres/test/js/main.js"></script>
		<script type="text/javascript" src="/Planificateur/parametres/test/js/view.js"></script>
		<script type="text/javascript" src="/Planificateur/parametres/test/js/test.js"></script>
	</body>
</html>