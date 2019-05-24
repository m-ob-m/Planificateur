<?php
    /**
     * \name		Planificateur de porte
    * \author    	Mathieu Grenier
    * \version		1.0
    * \date       	2017-01-27
    *
    * \brief 		Menu de création / modification / suppression de générique
    * \details 		Menu de création / modification / suppression de générique
    *
    * Licence pour la vue :
    * 	Verti by HTML5 UP
    html5up.net | @ajlkn
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
    */
    
    /* INCLUDE */
    include_once __DIR__ . '/controller/genericcontroller.php';		// Classe contrôleur de cette vue
    
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $generics = (new \GenericController())->getGenerics();
        if(isset($_GET["id"]))
        {
            $thisGeneric = \Generic::withID($db, intval($_GET["id"]));
        }
        else
        {
            $thisGeneric = new \Generic();
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
		<title>Fabridor - Liste des types de porte</title>
		<meta charset="utf-8" />
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
						<span>Liste des génériques</span>
					</div>
					
					<div style="display:inline-block;float:right;">
					   <!-- Nav -->
    					<nav id="nav">
    						<ul>
    							<li>
    								<a href="javascript: void(0);" onclick="saveConfirm();" class="imageButton">
    									<img src="../../images/save.png"> 
    								Sauvegarder</a>
    							</li>
    							<?php if($thisGeneric->getId() != null): ?>
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
		</div>
		
		<div id="features-wrapper">
			<div class="container">
				<table class="parametersTable" style="width:100%">
					<thead>
    					<tr>
    						<th class="firstVisibleColumn lastVisibleColumn" colspan=2>Générique</th>
    					</tr>
					</thead>
					<tbody>
    					<tr>
    						<td class="firstVisibleColumn" style="width:300px;">Identificateur</td>
    						<td class="lastVisibleColumn disabled">
    							<input type="text" id="id" readonly value="<?= $thisGeneric->getId(); ?>">
    						</td>
    					</tr>
    					<tr>
    						<td class="firstVisibleColumn">Nom de fichier</td>
    						<td class="lastVisibleColumn">
    							<input type="text" id="filename" autocomplete="off" maxlength="128"
    								value="<?= $thisGeneric->getFileName(); ?>">
    						</td>
    					</tr>
    					<tr>
    						<td class="firstVisibleColumn">Description</td>
    						<td class="lastVisibleColumn">
    							<input type="text" id="description" autocomplete="off" maxlength="128"
    								 value="<?= $thisGeneric->getDescription(); ?>">
    						</td>
    					</tr>
    					<tr>
    						<td class="firstVisibleColumn">Hauteur</td>
    						<td class="lastVisibleColumn">
    							<select id="heightParameter"  style="text-align-last:center;">
    								<?php $heightParameter = $thisGeneric->getHeightParameter(); ?>
    								<option value="LPX" <?= ($heightParameter === "LPX") ? "selected" : ""; ?>>LPX</option>
    								<option value="LPY" <?= ($heightParameter === "LPY") ? "selected" : ""; ?>>LPY</option>
    							</select>
    						</td>
    					</tr>
    					<?php if($thisGeneric->getId() === null): ?>
        					<tr>
        						<td class="firstVisibleColumn">Copier les paramètres de : </td>
        						<td class="lastVisibleColumn">
        							<select id="copyParametersFrom" style="text-align-last:center;">
        								<option value="" selected>Aucun</option>
                                    	<?php if(!empty($generics)):?>
        									<?php foreach ($generics as $generic): ?>
        										<option value=<?= $generic->getId() ?>><?= $generic->getFilename(); ?></option>
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
		
		<!--  Fenetre Modal pour message de validation -->
		<div id="validationMsgModal" class="modal" onclick='$(this).css({"display": "none"});'>
            <!-- Modal content -->
			<div id="validationMsg" class="modal-content" style='color:#FF0000;'></div>
		</div>
		
		<!--  Fenetre Modal pour message d'erreurs -->
		<div id="errMsgModal" class="modal" onclick='$(this).css({"display": "none"});'>
			<div id="errMsg" class="modal-content" style='color:#FF0000;'></div>
		</div>
		
		<!--  Fenetre Modal pour chargement -->
		<div id="loadingModal" class="modal loader-modal">
			<div id="loader" class="loader modal-content"></div>
		</div>	
		
		<!-- Scripts -->
		<script src="../../assets/js/jquery.min.js"></script>
		<script src="../../assets/js/jquery.dropotron.min.js"></script>
		<script src="../../assets/js/skel.min.js"></script>
		<script src="../../assets/js/util.js"></script>
		<script src="../../assets/js/main.js"></script>
		<script src="../../js/main.js"></script>
		<script src="../../js/toolbox.js"></script>
		<script src="js/main.js"></script>
		<script src="js/view.js"></script>
	</body>
</html>