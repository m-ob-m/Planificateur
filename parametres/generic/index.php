<?php
    /**
     * \name		Planificateur de porte - visualisation des génériques
    * \author    	Marc-Olivier bazin-Maurice
    * \version		1.0
    * \date       	2018-03-21
    *
    * \brief 		Liste des génériques
    * \details 		Liste des génériques
    *
    * Licence pour la vue :
    * 	Verti by HTML5 UP
    html5up.net | @ajlkn
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
    */
    
    /* INCLUDE */
    include_once __DIR__ . '/controller/genericController.php';		// Classe contrôleur de cette vue
    
    $generics = array();
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
		<title>Fabridor - Liste des programmes génériques</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="../../assets/css/responsive.css" />
		<link rel="stylesheet" href="../../assets/css/fabridor.css" />
		<link rel="stylesheet" href="../../assets/css/imageButton.css" />
		<link rel="stylesheet" href="../../assets/css/parametersTable.css" />
	</head>
	<body class="homepage">
		<div id="page-wrapper">
			<!-- Header -->
			<div id="header-wrapper">
				<header id="header" class="container">
					<!-- Logo -->
					<div id="logo">
						<h1>
							<a href="index.php"><img src="../../images/fabridor.jpg"></a>
						</h1>
						<span>Liste des génériques</span>
					</div>
					
					<div style="display:inline-block;float:right;">
    					<!-- Nav -->
    					<nav id="nav" style="display: block;">
    						<ul>
    							<li>
    								<a href="javascript: void(0);" onclick="openGeneric();" class="imageButton">
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
					<table style="width:100%;" class="parametersTable">
						<thead>
							<tr>
								<th class="firstVisibleColumn" style="width:10%;">ID</th>
								<th style="width:30%;">Nom de fichier</th>
								<th class="lastVisibleColumn" style="width:60%;">Description</th>
							</tr>
						</thead>
						<tbody>
                            <?php if(!empty($generics)): ?>
								<?php foreach ($generics as $generic): ?>
									<tr class="link" onclick="javascript:openGeneric(<?= $generic->getId(); ?>)">
										<!-- Id -->
										<td class="firstVisibleColumn" >
											<?= $generic->getId(); ?>
										</td>
										<!-- Nom de fichier -->
										<td>
											<?= $generic->getFilename(); ?>
										</td>
										<!-- Description -->
										<td class="lastVisibleColumn">
											<?= $generic->getDescription(); ?>
										</td>
									</tr>
							  	<?php endforeach; ?>
						  	<?php endif; ?>
					  	</tbody>
					</table>
				</div>
			</div>
			
			<!--  Fenêtre modale pour message de validation -->
    		<div id="validationMsgModal" class="modal" onclick='this.style.display = "none";' >
                <!-- Modal content -->
				<div id="validationMsg" class="modal-content" style='color:#FF0000;'></div>
			</div>
			
    		<!--  Fenêtre modale pour message d'erreurs -->
    		<div id="errMsgModal" class="modal" onclick='this.style.display = "none";' >
                <!-- Modal content -->
				<div id="errMsg" class="modal-content" style='color:#FF0000;'></div>
			</div>
		</div>

	    <!-- Scripts -->
		<script type="text/javascript" src="../../assets/js/ajax.js"></script>
		<script type="text/javascript" src="../../assets/js/docReady.js"></script>
		<script type="text/javascript" src="../../js/main.js"></script>
		<script type="text/javascript" src="../../js/toolbox.js"></script>
		<script type="text/javascript" src="js/main.js"></script>
	</body>
</html>