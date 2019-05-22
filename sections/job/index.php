<?php
/**
 * \name		Planificateur de porte
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-01-18
*
* \brief 		Menu de validation des jobs
* \details 		Ce menu permet de visualiser une job et d'en faire la validation
*
* Licence pour la vue :
* 	Verti by HTML5 UP
html5up.net | @ajlkn
Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
*/

/* INCLUDE */
include_once __DIR__ . "/controller/jobController.php";		// Classe contrôleur de la classe Job
include_once __DIR__ . "/../../parametres/model/controller/modelController.php";		// Classe contrôleur de la classe Modèle
include_once __DIR__ . "/../../parametres/type/controller/typeController.php";		// Classe contrôleur de la classe Type

$db = new \FabPlanConnection();
$job = null;
try
{
    $db->getConnection()->beginTransaction();
    if(isset($_GET["jobId"]))
    {
        $job = \Job::withID($db, $_GET["jobId"]);
        $models = (new \ModelController())->getModels();
        $types = (new TypeController())->getTypes();
    }
    else
    {
        $job = new \Job();
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

$batchId = $_GET["batchId"] ?? null;

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Fabridor - Validation</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="/Planificateur/assets/css/responsive.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/fabridor.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersTable.css"/>
		<link rel="stylesheet" href="/Planificateur/assets/css/imageButton.css">
		<link rel="stylesheet" href="/Planificateur/assets/css/loader.css" />
	</head>
	<body class="homepage">
    	<div id="page-wrapper">
			<!-- Header -->
			<div id="header-wrapper">
				<header id="header" class="container">
					<!-- Logo -->
					<div id="logo">
						<h1>
							<a href="/Planificateur/index.php"><img src="/Planificateur/images/fabridor.jpg"></a>
						</h1>
						<div class="AL" style="line-height: 25px;float:right;padding:20px;">
							<h3>Commande #<?= $job->getName(); ?></h3>
							<label for="deliveryDate">Date de livraison : 
								<input type="date" id="date_livraison" style="width:200px; height:24px;" 
									value="<?= $job->getDeliveryDate(); ?>" onchange="dataHasChanged(true);">
							</label>
						</div>
					</div>
					
    				<div style="display:inline-block; float:right;">
    					<!-- Nav -->
						<nav id="nav">
							<ul>
								<li>
									<a href="javascript: void(0);" onclick="saveConfirm();" class="imageButton">
										<img src="/Planificateur/images/save.png"> 
									Sauvegarder</a>
								</li>
								<li>
									<a href="javascript: void(0);" onclick="goToBatch(<?= $batchId; ?>);" class="imageButton">
										<img src="/Planificateur/images/exit.png">
									Sortir</a>
								</li>
							</ul>
						</nav>
    				</div>
    			</header>
    		</div>
    
    		<input type="hidden" id="batch_id" value="<?= $batchId; ?>">
			<input type="hidden" id="job_id" value="<?= $job->getId(); ?>">

			<!-- Job types table -->
			<div id="features-wrapper">
				<div id="blocksContainer" class="container">
				</div>
			</div>
		</div>
		
		<!--  Fenêtre modale pour l'édition des paramètres -->
		<div id="parametersEditor" class="modal">
			<div class="modal-content" style="width: 70%;">
				<span class="editMenu">
                    <img id="acceptEdit" src="/Planificateur/images/ok16.png" class="editIcon">
                    <img id="cancelEdit" src="/Planificateur/images/cancel16.png" class="editIcon">
                </span>
				<table class="parametersTable hoverEffectDisabled" style="margin-bottom: 20px;">
					<thead>
						<tr>
							<th class="firstVisibleColumn lastVisibleColumn" colspan=2>Modèle et type de porte</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="firstVisibleColumn" style="width:20%;">Modèle</td>
							<td class="lastVisibleColumn">
								<select id="modelId">
									<?php foreach($models as $model): ?>
										<option value="<?= $model->getId(); ?>"><?= $model->getDescription(); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr id="parametersEditorTypeSelectionRow">
							<td style="width: 10%;" class="firstVisibleColumn">Type</td>
							<td class="lastVisibleColumn">
								<select id="typeNo">
									<?php foreach($types as $type): ?>
										<option value="<?= $type->getImportNo(); ?>"><?= $type->getDescription(); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr id="parametersEditorMprFileSelectionRow" style="display: none;">
							<td class="firstVisibleColumn" style="width: 10%;">Fichier mpr</td>
							<td class="lastVisibleColumn">
								<input type="file" value=""
									onchange="readMpr.apply($(this).prop('files')[0], $('#parametersEditionTextArea'));">
							</td>
						</tr>
						<tr>
							<td class="firstVisibleColumn lastVisibleColumn" colspan=2>
								<!-- This td is only here to ensure that the last row of this table is visible. -->
							</td>
						</tr>
					</tbody>
				</table>
				<div id="customFileTableBody" style="display: none;">
					<textarea id=mprFileContents style="resize: none; width: 100%; height: 100%;"></textarea>
				</div>
				<table id="parametersArray" class="parametersTable hoverEffectDisabled">
					<thead>
						<tr>
							<th class="firstVisibleColumn" style="width: 10%;">Clé</th>
							<th style="width: 45%;">Valeur</th>
							<th class="lastVisibleColumn" style="width: 45%;">Description</th>
							<th style="display: none;">Valeur par défaut</th>
							<th style="display: none;">Édition rapide</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
		
		<!--  Fenêtre modale pour changement de bloc -->
		<div id="moveBetweenBlocksModal" class="modal" onclick='$(this).css({"display": "none"});' >
			<div class="modal-content" style='color:#FF0000;'>
				<h4>Déplacer vers un autre bloc</h4>
				<div id="blocksList"></div>
				<h1>Cliquer sur cette fenêtre pour la fermer</h1>
			</div>
		</div>
		
		<!--  Fenêtre modale pour messages d'erreur -->
		<div id="errMsgModal" class="modal" onclick='$(this).css({"display": "none"});' >
			<div id="errMsg" class="modal-content" style='color:#FF0000;'></div>
		</div>
		
		<!--  Fenêtre modale pour messages de validation -->
		<div id="validationMsgModal" class="modal" onclick='$(this).css({"display": "none"});' >
			<div id="validationMsg" class="modal-content" style='color:#FF0000;'></div>
		</div>
		
		<!--  Fenêtre modale pour chargement -->
		<div id="loadingModal" class="modal loader-modal">
			<div id="loader" class="loader modal-content"></div>
		</div>	
	
		<!-- Scripts -->
		<script src="/Planificateur/assets/js/moment.min.js"></script>
		<script src="/Planificateur/assets/js/moment-timezone.js"></script>
		<script src="/Planificateur/assets/js/jquery.min.js"></script>
		<script src="/Planificateur/assets/js/jquery.dropotron.min.js"></script>
		<script src="/Planificateur/assets/js/skel.min.js"></script>
		<script src="/Planificateur/assets/js/util.js"></script>
		<script src="/Planificateur/assets/js/main.js"></script>
		<script src="/Planificateur/js/main.js"></script>
		<script src="/Planificateur/js/toolbox.js"></script>
		<script src="js/index.js"></script>
		<script src="js/job.js"></script>
		<script src="js/jobType.js"></script>
		<script src="js/main.js"></script>
		<script src="js/parameterEditor.js"></script>
	</body>
</html>