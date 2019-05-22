<?php
    /**
     * \name		Planificateur de porte
    * \author    	Mathieu Grenier
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
    include_once __DIR__ . '/controller/typeController.php';		// Classe contrôleur de cette vue
    include_once __DIR__ . "/../generic/controller/genericController.php";
    
    $types = array();
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $types = (new \TypeController())->getTypes();
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
		<title>Fabridor - Liste des types de porte</title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="/Planificateur/assets/css/responsive.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/fabridor.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersTable.css"/>
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
								<img src="/Planificateur/images/fabridor.jpg">
							</a>
						</h1>
						<span>Liste des types de porte</span>
					</div>
					
					<div style="display:inline-block;float:right;">
    					<!-- Nav -->
    					<nav id="nav" class="basicNavigationMenu">
    						<ul>
    							<li>
    								<a class="imageButton" href="javascript: void(0);" onclick="openType();">
    									<img src="/Planificateur/images/add.png">
    								Ajouter</a>
    							</li>
    							<li>
    								<a class="imageButton" href="/Planificateur/index.php">
    									<img src="/Planificateur/images/exit.png">
    								Sortir</a>
    							</li>	
    						</ul>
    					</nav>
					</div>
				</header>
			</div>
			
			<div id="features-wrapper">
				<div class="container">
					<table class="parametersTable" style="width:100%">
						<thead>
    						<tr>
    							<th class="firstVisibleColumn" style="width:60px;">ID</th>
    							<th style="width:60px;"># SIA</th>
    							<th>Description</th>
    							<th class="lastVisibleColumn" style="width:300px;">Générique</th>
    						</tr>
						</thead>
						<tbody>
    						<?php foreach ($types as $type): ?>
    							<?php $genericFilename = $type->getGeneric()->getFilename(); ?>
    							<tr class="link" onclick="javascript:openType(<?= $type->getId(); ?>);">
    								<td class="firstVisibleColumn"><?= $type->getId(); ?></td>
    								<td><?= $type->getImportNo(); ?></td>
    								<td><?= $type->getDescription(); ?></td>
    								<td class="lastVisibleColumn"><?= $genericFilename; ?></td>
    							</tr>
    					  	<?php endforeach;?>
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
		<script src="/Planificateur/assets/js/jquery.min.js"></script>
		<script src="/Planificateur/assets/js/jquery.dropotron.min.js"></script>
		<script src="/Planificateur/assets/js/skel.min.js"></script>
		<script src="/Planificateur/assets/js/util.js"></script>
		<script src="/Planificateur/assets/js/main.js"></script>
		<script src="js/main.js"></script>
	</body>
</html>