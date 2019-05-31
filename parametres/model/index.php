<?php
/**
 * \name		Planificateur de porte
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-01-27
*
* \brief 		Menu de création / modification / suppression de modèle
* \details 		Menu de création / modification / suppression de modèle
*
* Licence pour la vue :
* 	Verti by HTML5 UP
html5up.net | @ajlkn
Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
*/

/* INCLUDE */
include_once __DIR__ . '/controller/modelController.php';		// Classe contrôleur de cette vue

$models = array();
$db = new \FabPlanConnection();
try
{
    $db->getConnection()->beginTransaction();
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
		<title>Fabridor - Liste des modèles</title>
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
							<a href="index.php">
								<img src="../../images/fabridor.jpg">
							</a>
						</h1>
						<span>Liste des modèles</span>
					</div>
					
					<div style="float:right;">
    					<!-- Nav -->
    					<nav id="nav" style="display: block;">
    						<ul>
    							<li>
    								<a href="javascript: void(0);" onclick="openModel();" class="imageButton">
    									<img src="../../images/add.png" class="imageButton">
    								Ajouter</a>
    							</li>
    							<li>
    								<a href="../../index.php" class="imageButton">
    									<img src="../../images/exit.png" class="imageButton">
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
    							<th class="firstVisibleColumn" style="width:200px;">ID</th>
    							<th class="lastVisibleColumn">Description</th>
    						</tr>
						</thead>
						<tbody>
    						<?php foreach ($models as $model): ?>
    							<tr class="link" onclick="openModel(<?= $model->getId(); ?>)">
    								<!-- Id -->
    								<td class="firstVisibleColumn"><?= $model->getId(); ?></td>
    								<!-- Description -->
    								<td class="lastVisibleColumn"><?= $model->getDescription(); ?></td>											
    							</tr>
    						<?php endforeach;?>	
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
	</body>
</html>