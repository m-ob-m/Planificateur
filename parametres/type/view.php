<?php
    /**
     * \name		Planificateur de porte
    * \author    	Mathieu Grenier
    * \version		1.0
    * \date       	2017-01-27
    *
    * \brief 		Visualisation d'un type de porte
    * \details 		Visualisation d'un type de porte
    *
    * Licence pour la vue :
    * 	Verti by HTML5 UP
    html5up.net | @ajlkn
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
    */
    
    /* INCLUDE */
    include_once __DIR__ . '/../type/controller/typeController.php';		// Classe contrôleur de cette vue
    include_once __DIR__ . '/../generic/controller/genericController.php'; //Contrôleur de générique
    
    $generics = array();
    $type = null;
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $generics = (new \GenericController())->getGenerics();
        $type = isset($_GET["id"]) ? \Type::withID($db, intval($_GET["id"])) : new \Type();
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
							<span>Liste des types de porte</span>
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
    								<?php if($type->getId() !== null): ?>
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
				<?php if($type->getId() !== null): ?>
    				<h1 style="color:darkred;">
    					Attention! si vous modifiez le paramètre "Générique", les pièces créées avec le type actuel, 
    					mais un générique différent risquent de ne plus fonctionner correctement.
    				</h1>
    			<?php endif;?>
				<table style="width:100%" class="parametersTable">
					<thead>
						<tr>
							<th class="firstVisibleColumn lastVisibleColumn" colspan=2>Type</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="firstVisibleColumn" style="width:200px;">Identificateur</td>
							<td class="lastVisibleColumn disabled" id="id"><?= $type->getId(); ?></td>
						</tr>
						<tr>
							<td class="firstVisibleColumn">Numéro SIA</td>
							<td class="lastVisibleColumn">
								<input type="text" id="importNo" autocomplete="off" maxlength="2" 
									value="<?= $type->getImportNo(); ?>">
							</td>
						</tr>
						<tr>
							<td class="firstVisibleColumn">Description</td>
							<td class="lastVisibleColumn">
								<input type="text" id="description" autocomplete="off" maxlength="128"
									value="<?= $type->getDescription(); ?>">
							</td>
						</tr>
						<tr>
							<td class="firstVisibleColumn">Générique</td>
							<td class="lastVisibleColumn">
								<select id="generic" onchange="updateCopyParametersFrom.apply($(this));"
									style="text-align-last:center;">
                                	<?php if(!empty($generics)):?>
										<?php foreach($generics as $generic): ?>
											<?php $selGeneric = $type->getGeneric(); ?>
											<?php $selGenericId = ($selGeneric <> null) ? $selGeneric->getId() : null; ?>
											<?php $isSel = (($generic->getId() === $selGenericId) ? "selected" : ""); ?>
											<option value=<?= $generic->getId(); ?> <?= $isSel; ?>>
												<?= $generic->getFilename(); ?>
											</option>
										<?php endforeach;?>	
									<?php endif; ?>
								</select>
							</td>
						</tr>
						<?php if($type->getId() === null): ?>
        					<tr>
        						<td class="firstVisibleColumn">Copier les paramètres de : </td>
        						<td class="lastVisibleColumn">
        							<select id="copyParametersFrom" style="text-align-last:center;">
        							</select>
        						</td>
        					</tr>
						<?php endif; ?>
					</tbody>
				</table>
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