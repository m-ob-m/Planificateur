<?php
	/**
	 * \name		Planificateur de porte
	* \author    	Mathieu Grenier
	* \version		1.0
	* \date       	2017-01-27
	*
	* \brief 		Visualisation d'un Model
	* \details 		Visualisation d'un Model
	*
	* Licence pour la vue :
	* 	Verti by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
	*/

	/* INCLUDE */
	require_once __DIR__ . '/controller/modelController.php';		// Classe contrôleur de cette vue

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
    
	$exists = null;
	$thisModel = null;
	$models = array();
	$db = new \FabPlanConnection();
	try
	{
		$db->getConnection()->beginTransaction();
		$models = (new \ModelController())->getModels();
		if(isset($_GET["id"]))
		{
			$thisModel = \Model::withID($db, $_GET["id"]);
			$exists = true;
		}
		else
		{
			$thisModel = new \Model();
			$exists = false;
		}
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
		<title>Fabridor - Liste des modèles</title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="../../assets/css/responsive.css" />
		<link rel="stylesheet" href="../../assets/css/fabridor.css" />
		<link rel="stylesheet" href="../../assets/css/loader.css" />
		<link rel="stylesheet" href="../../assets/css/parametersTable.css">
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
						<span>Liste des modèles</span>
					</div>
					
					<div style="float:right;">
    					<nav id="nav" style="display: block;">
    						<ul>
    							<li>
    								<a href="javascript: void(0);" onclick="saveConfirm();" class="imageButton">
    									<img src="../../images/save.png">
    								Sauvegarder</a>
    							</li>
    							<?php if($exists): ?>
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

			<div id="features-wrapper">
				<div class="container">
					<table class="parametersTable" style="width:100%;">
						<thead>
    						<tr>
    							<th class="firstVisibleColumn lastVisibleColumn" colspan=2>Modèle</th>
    						</tr>
						</thead>
						<tbody>
    						<tr>
    							<td class="firstVisibleColumn" style="width:250px;">Identificateur</td>
    							<td class="lastVisibleColumn disabled">
    								<?php $id = $thisModel->getId(); ?>
    								<input type="text" id="id" value="<?= $id ?>" maxlength="11" disabled>
    							</td>
    						</tr>
    						<tr>
    							<td class="firstVisibleColumn">Description</td>
    							<td class="lastVisibleColumn">
    								<?php $description = $thisModel->getDescription(); ?>
    								<input type="text" id="description" autocomplete="off" maxlength="128"
    									value="<?= $description; ?>">
    							</td>
    						</tr>
    						<?php if($thisModel->getId() === null): ?>
            					<tr>
            						<td class="firstVisibleColumn">Copier les paramètres de : </td>
            						<td class="lastVisibleColumn">
            							<select id="copyParametersFrom" style="text-align-last:center;">
            								<option value="" selected>Aucun</option>
                                        	<?php if(!empty($models)):?>
            									<?php foreach ($models as $model): ?>
            										<option value=<?= $model->getId() ?>><?= $model->getDescription(); ?>
													</option>
            									<?php endforeach;?>	
            								<?php endif; ?>
            							</select>
            						</td>
            					</tr>
    						<?php endif; ?>
						</tbody>
					</table>
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
		<script type="text/javascript" src="../../assets/js/ajax.js"></script>
		<script type="text/javascript" src="../../assets/js/docReady.js"></script>
		<script type="text/javascript" src="../../js/main.js"></script>
		<script type="text/javascript" src="../../js/toolbox.js"></script>
		<script type="text/javascript" src="js/main.js"></script>
		<script type="text/javascript" src="js/view.js"></script>
	</body>
</html>