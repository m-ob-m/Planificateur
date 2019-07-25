<?php
	/**
	 * \name		Planificateur de porte
	* \author    	Mathieu Grenier
	* \version		1.0
	* \date       	2017-01-27
	*
	* \brief 		Visualisation d'un mat�riel
	* \details 		Visualisation d'un mat�riel
	*
	* Licence pour la vue :
	* 	Verti by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
	*/

	/* INCLUDE */
	require_once __DIR__ . '/controller/materielCtrl.php';		// Classe contrôleur de cette vue

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
    
	$material = null;
	$db = new \FabPlanConnection();
	try
	{
		$db->getConnection()->beginTransaction();
		if(isset($_GET["id"]))
		{
			$material = (new \MaterielController())->getMateriel($_GET["id"]);
		}
		else
		{
			$material = new \Materiel();
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
			<title>Fabridor - Liste des matériaux</title>
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
							<span>Liste des matériaux</span>
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
									<?php if($material->getId() != ""): ?>
										<li>
											<a href="javascript: void(0);" onclick="deleteConfirm();" class="imageButton">
												<img src="../../images/cancel16.png">
											Supprimer</a>
										</li>
									<?php endif; ?>
									<li>
										<a id="exitButton" href="index.php" class="imageButton">
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
						<table id="parametersTable" class="parametersTable" style="width:100%;">
							<thead>
								<tr>
									<th class="firstVisibleColumn lastVisibleColumn" colspan="2">Matériel</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="firstVisibleColumn" style="width:200px;">Identificateur</td>
									<td class="lastVisibleColumn disabled">
										<input type="text" id="id_materiel" readonly value="<?= $material->getId(); ?>">
									</td>
								</tr>
								<tr>
									<td class="firstVisibleColumn">Description</td>
									<td class="lastVisibleColumn">
										<input type="text" id="description" autocomplete="off" maxlength="128"
											value="<?= $material->getDescription(); ?>">
									</td>
								</tr>
								<tr>
									<td class="firstVisibleColumn">Code SIA</td>
									<td class="lastVisibleColumn">
										<input type="text" id="codeSIA" autocomplete="off" maxlength="30"
											value="<?= $material->getCodeSIA(); ?>">
									</td>
								</tr>
								<tr>
									<td class="firstVisibleColumn">Code CutRite</td>
									<td class="lastVisibleColumn">
										<input type="text" id="codeCutRite" autocomplete="off" maxlength="30"
											value="<?= $material->getCodeCutRite(); ?>">
									</td>
								</tr>
								<tr>
									<td class="firstVisibleColumn">Epaisseur</td>
									<td class="lastVisibleColumn">
										<input type="text" id="epaisseur" autocomplete="off" maxlength="30"
											value="<?= $material->getEpaisseur(); ?>">
									</td>
								</tr>
								<tr>
									<td class="firstVisibleColumn">Essence</td>
									<td class="lastVisibleColumn">
										<input type="text" id="essence" autocomplete="off" maxlength="30"
											value="<?= $material->getEssence(); ?>">
									</td>
								</tr>
								<tr>
									<td class="firstVisibleColumn">Grain</td>
									<td class="lastVisibleColumn">
										<label style="cursor:pointer;">
											<?php $checked = (($material->getGrain() === "Y") ? true : false); ?>
											<input type="radio" name="has_grain" value="Y" <?= $checked ? "checked" : ""; ?>>
										Oui</label>
										<label style="cursor:pointer;">
											<input type="radio" name="has_grain" value="N" <?= $checked ? "" : "checked"; ?>>
										Non</label>
									</td>
								</tr>
								<tr>
									<td class="firstVisibleColumn">MDF</td>
									<td class="lastVisibleColumn">
										<?php $checked = (($material->getEstMDF() === "Y") ? true : false); ?>
										<label style="cursor:pointer;">
											<input type="radio" name="est_mdf" value="Y" <?= $checked ? "checked" : ""; ?>>
										Oui</label>
										<label style="cursor:pointer;">
											<input type="radio" name="est_mdf" value="N" <?= $checked ? "" : "checked"; ?>>
										Non</label>
									</td>
								</tr>
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
			<script type="text/javascript" src="js/view.js"></script>
			<script type="text/javascript" src="js/main.js"></script>

		</body>
</html>