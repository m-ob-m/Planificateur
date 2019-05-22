<?php
    /**
     * \name		index.php
    * \author    	Marc-Olivier Bazin-Maurice
    * \version		1.0
    * \date       	2017-05-04
    *
    * \brief 		Menu de création / modification de batchs
    * \details 		Ce menu permet de créer / modifier et supprimer des batchs
    *
    * Licence pour la vue :
    * 	Verti by HTML5 UP
    html5up.net | @ajlkn
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
    */
    
    include_once __DIR__ . "/controller/batchController.php";
    include_once __DIR__ . "/../../parametres/materiel/controller/materielCtrl.php";
    
    $batch = null;
    $materials = null;
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $materials = (new \MaterielController())->getMateriels();
        
        if(isset($_GET["id"]))
        {
            if(preg_match("/^\d+$/", $_GET["id"]))
            {
                $batch = \Batch::withID($db, $_GET["id"]);
            }
            else 
            {
                $batch = new \Batch();
            }
        }
        else
        {
            $batch = new \Batch();
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
    
    $id = $batch->getId();
    $isFullDay = ($batch->getFullDay() === "Y") ? true : false;
    $momentType = ($batch->getFullDay() === "Y") ? "date" : "datetime-local";
    $start = getDateTimeInCorrectFormat($batch->getStart(), $isFullDay);
    $end = getDateTimeInCorrectFormat($batch->getEnd(), $isFullDay);
    
    /**
     * Converts a date-time from the database in a format suitable for the date-time or date inputs depending on 
     * if the all day checkbox is checked.
     *
     * @param string $stringDateTime The date-time string to convert.
     * @param bool $onlyDate A boolean that determines if the expected format is a date (true) or a date-time (false).
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The converted datetime.
     */
    function getDateTimeInCorrectFormat(?string $stringDateTime, bool $onlyDate = false) : ?string
    {
        if($onlyDate)
        {       
            $dateTime = DateTime::createFromFormat("Y-m-d", $stringDateTime);
            
            if($dateTime === false)
            {
                $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $stringDateTime);
            }
            
            if($dateTime !== false)
            {
                return $dateTime->format("Y-m-d");
            }
            else
            {
                return null;
            }
        }
        else
        {
            $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $stringDateTime);
            
            if($dateTime === false)
            {
                $dateTime = DateTime::createFromFormat("Y-m-d", $stringDateTime);
            }
            
            if($dateTime !== false)
            {
                return $dateTime->format("Y-m-d\TH:i:s");
            }
            else
            {
                return null;
            }
        }
    }
?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>Fabridor - Batch</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="/Planificateur/assets/css/responsive.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/fabridor.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersTable.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/imageButton.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/loader.css" />
	</head>
	<body class="homepage">
		<div id="page-wrapper">
			<div id="header-wrapper">
				<header id="header" class="container">
					<!-- Logo -->
					<div id="logo">
						<h1>
							<a href="index.php">
								<img src="/Planificateur/images/fabridor.jpg">
							</a>
						</h1>
						<span>Nest de production</span>
					</div>
					<div style="display: inline-block;float: right;">
						<nav id="nav">
							<ul>
								<li>
									<a href="javascript: void(0);" onclick="saveConfirm();" class="imageButton">
										<img src="/Planificateur/images/save.png"> 
									Sauvegarder</a>
								</li>
								<?php if($id !==  null): ?>
									<li>
										<a href="javascript: void(0);" onclick="downloadConfirm();" class="imageButton">
											<img src="/Planificateur/images/download.png"> 
										Télécharger</a>
									</li>
    								<li>
    									<a href="javascript: void(0);" onclick="deleteConfirm();" class="imageButton">
    										<img src="/Planificateur/images/cancel16.png"> 
    									Supprimer</a>
    								</li>
								<?php endif; ?>
								<li>
									<a href="javascript: void(0);" onclick="goToIndex();" class="imageButton">
										<img src="/Planificateur/images/exit.png"> 
									Sortir</a>
								</li>
							</ul>
						</nav>
					</div>
				</header>
			</div>

			<!-- Features -->
			<div id="features-wrapper">
				<div class="container">
					<div style="width: 50%; float: left; padding: 5px;">
    					<table id="batch" class="parametersTable">	
    						<tbody>						
        						<tr>
        							<td class="firstVisibleColumn lastVisibleColumn" colspan="2" 
        								style="background-color: #127031; color: #ffffff;">
        								Planification
        							</td>													
        						</tr>							
            					<tr hidden=1>
            						<td class="firstVisibleColumn" style="width: 150px; background-color: #c6e0b4;">
         								Identifiant unique
         							</td>
            						<td class="lastVisibleColumn">
            							<input type="text" id="batchId" value="<?= $id; ?>" disabled>
            						</td>
            					</tr>
            					<tr>
            						<td class="firstVisibleColumn" style="width: 150px; background-color: #c6e0b4;">Titre</td>
            						<td class="lastVisibleColumn">
            							<input type="text" id="batchName" name="batchName" value="<?= $batch->getName(); ?>">
            						</td>
            					</tr>
        						<tr>
        							<td class="firstVisibleColumn" style="background-color: #c6e0b4;">Début</td>
        							<td class="lastVisibleColumn">
        								<input type=<?= $momentType; ?> step=1 id="startDate" value="<?= $start; ?>">
        							</td>
        						</tr>						
            					<tr>
            						<td class="firstVisibleColumn" style="background-color: #c6e0b4; border-bottom: none;">
										Fin
									</td>
            						<td class="lastVisibleColumn">
            							<input type=<?= $momentType; ?> step=1 id="endDate" value="<?= $end; ?>">
            						</td>
            					</tr>
            					<tr>
            						<td class="firstVisibleColumn" style="background-color: #c6e0b4; border-top: none;"></td>
            						<td class="lastVisibleColumn">
            							<label style="cursor: pointer;">
            								<?php $fullDayChecked = $isFullDay ? "checked" : ""; ?>
            								<?php $fullDayValue = $isFullDay ? "Y" : "N"; ?>
            								<input type="checkbox" id="fullDay" value="<?= $fullDayValue; ?>" 
            								    <?= $fullDayChecked; ?>>
            							Toute la journée</label>
            						</td>
            					</tr>
            				</tbody>
            				<tbody>					
        						<tr>
        							<td class="firstVisibleColumn lastVisibleColumn enteteBatch" colspan=2>
        								Matériel / Essence et panneaux
        							</td>													
        						</tr>
        						<tr>
        							<td class="firstVisibleColumn" style="background-color: #c6e0b4;">Matériel</td>
        							<td class="lastVisibleColumn">
        								<select id="material" style="text-align-last: center;" onchange="updatePannelsList();">
        									<option value="0">[Non spécifié]</option>
        									<?php foreach($materials as $material) : ?>
        										<?php $materialId = $material->getId(); ?>
												<?php $selected = $batch->getMaterialId() === $materialId ? "selected" : ""; ?>
        										<option value="<?= $material->getId(); ?>" <?= $selected ?>><?= 
        								            $material->getDescription(); 
        								        ?></option>
        									<?php endforeach; ?>
        								</select>
        							</td>
        						</tr>						
            					<tr>
            						<td class="firstVisibleColumn" style="background-color: #c6e0b4;">Panneaux</td>
            						<td class="lastVisibleColumn">
            							<select id="boardSize" style="text-align-last: center;">
            								<option value="<?= $batch->getBoardSize(); ?>" selected><?= 
            								    $batch->getBoardSize(); 
            								?></option>
            							</select>
            						</td>
            					</tr>
            				</tbody>
            				<tbody>
            					<tr>
        							<td class="firstVisibleColumn lastVisibleColumn enteteBatch" colspan=2>
        								État et optimisation de CutRite
        							</td>													
        						</tr>
        						<tr>
        							<td class="firstVisibleColumn" style="background-color: #c6e0b4;">État</td>
        							<td class="lastVisibleColumn">
        								<select id="status" class="lastVisibleColumn" style="text-align-last: center;">
        									<?php $status = $batch->getStatus(); ?>
        									<option value="E" <?= ($status === "E") ? "selected" : ""; ?>>Entrée</option>
        									<option value="P" <?= ($status === "P") ? "selected" : ""; ?>>Pressant</option>
        									<option value="X" <?= ($status === "X") ? "selected" : ""; ?>>En exécution</option>
        									<option value="A" <?= ($status === "A") ? "selected" : ""; ?>>Attente</option>
        									<option value="N" <?= ($status === "N") ? "selected" : ""; ?>>Non livrée</option>
        									<option value="T" <?= ($status === "T") ? "selected" : ""; ?>>Terminée</option>
        								</select>
        							</td>
        						</tr>
        						<tr>
        							<td class="firstVisibleColumn" style="background-color: #c6e0b4;">Optimisation</td>
        							<?php if($batch->getMprStatus() === "N"): ?>
										<td id="mprStatus" class="etatRouge lastVisibleColumn">
											<div style="width: max-content; display: inline-block; float: left;">
												<p style="margin-bottom: 0px;">Non téléchargé</p>
											</div>
											<a class="imageButton" href="javascript: void(0);" onclick="generateConfirm();"
												style="width: max-content; float: right; text-decoration: underline;">
												<img src="/Planificateur/images/download.png" style="margin-right: 2px;">
											Télécharger</a>
										</td>
									<?php elseif($batch->getMprStatus() === "A"): ?>
										<td id="mprStatus" class="etatJaune lastVisibleColumn">En attente</td>
									<?php elseif($batch->getMprStatus() === "P"): ?>
										<td id="mprStatus" class="etatBleu lastVisibleColumn">En cours</td>
									<?php elseif($batch->getMprStatus() === "E"): ?>
										<td id="mprStatus" class="etatRouge lastVisibleColumn">Erreur</td>
									<?php elseif($batch->getMprStatus() === "G"): ?>
										<td id="mprStatus" class="etatVert lastVisibleColumn">
											<p style="float: left; width: min-content;">Prêt</p>
											<a class="imageButton" href="#" onclick="viewPrograms(<?= $id; ?>); return false;" 
												style="float: right; color: black; text-decoration: underline; width: auto;">
												<img src="/Planificateur/images/search16.png" style="margin-right: 2px;">
											Visualiser</a>
										</td>
									<?php else: ?>
										<td id="mprStatus" class="etatRouge lastVisibleColumn">
											<div style="width: max-content; display: inline-block; float: left;">
												<p style="margin-bottom: 0px;">Non téléchargé</p>
											</div>
											<a class="imageButton" href="javascript: void(0);" onclick="generateConfirm();"
												style="width: max-content; float: right; text-decoration: underline;">
												<img src="/Planificateur/images/download.png" style="margin-right: 2px;">
											Télécharger</a>
										</td>
    								<?php endif; ?>
    							</tr>
    						</tbody>
    						<tbody>
    							<tr>
    								<td class="firstVisibleColumn lastVisibleColumn enteteBatch" colspan=2>Commentaires</td>
    							</tr>
    							<tr>
    								<td class="firstVisibleColumn lastVisibleColumn" colspan=2 style="height: 150px;">
    									<textarea id="comments" class="notResizable"
    										style="background-color: white; color: black; margin: 0px; text-align: left;"><?= 
                                            $batch->getComments(); 
                                        ?></textarea>
    								</td>
    							</tr>
    						</tbody>
    					</table>
					</div>
					<div style="width: 50%; float: right; padding: 5px;">
						<input type="hidden" id="carrouselState" value="2">
						<!-- Carrousel -->
    					<table style="width: 100%;">
    						<tbody style="background-color: white; border: 3px solid black;">
    							<tr style="height: 50px;">
    								<td id="carrousel1" class="carrouselCell" style="width: 16.7%;"><?= 
    								    $batch->getCarrousel()->getTools()[0]; 
    								?></td>
    								<td id="carrousel2" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[1]; 
    								?></td>
    								<td id="carrousel3" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[2]; 
    								?></td>
    								<td id="carrousel4" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[3]; 
    								?></td>
    								<td id="carrousel5" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[4]; 
    								?></td>
    							</tr>
    							<tr style="height: 50px;">
    								<td id="carrousel14" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[13]; 
    								?></td>
    								<td class="carrouselCell" style="background-color: #003333; color: white;" colspan=3 
    									rowspan="2">
    									Carrousel
    								</td>
    								<td id="carrousel6" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[5]; 
    								?></td>
    							</tr>
    							<tr style="height: 50px;">
    								<td id="carrousel13" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[12]; 
    								?></td>
    								<td id="carrousel7" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[6]; 
    								?></td>
    							</tr>
    							<tr style="height: 50px;">
    								<td id="carrousel12" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[11]; 
    								?></td>
    								<td id="carrousel11" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[10]; 
    								?></td>
    								<td id="carrousel10" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[9]; 
    								?></td>
    								<td id="carrousel9" class="carrouselCell" style="width: 16.7%;"><?=
    									 $batch->getCarrousel()->getTools()[8]; 
    								?></td>
    								<td id="carrousel8" class="carrouselCell" style="width: 16.7%;"><?=
    								    $batch->getCarrousel()->getTools()[7]; 
    								?></td>
    							</tr>
    						</tbody>
    					</table>
    					<br>
    					<!-- ### COMMANDES DU NEST ### -->
    					<table id="orders" class="parametersTable" style="width=100%;">
    						<thead>	
    							<tr>
    								<th class="firstVisibleColumn lastVisibleColumn" colspan="4">Commandes du nest</th>
    							</tr>				
    							<tr>
    								<th class="firstVisibleColumn" style="width: 24px;"></th>
    								<th hidden=1>Id</th>
    								<th style="width: 80px;">Numéro</th>
    								<th style="width: 150px;">Date de livraison</th>
    								<th class="lastVisibleColumn" style="width: 80px;">Portes</th>
    							</tr>
    						</thead>
    						<tbody>
    						</tbody>
    						<tfoot>
    							<tr>
    								<td class="firstVisibleColumn lastVisibleColumn" colspan="4" style="height: min-content;">
    									<div style="margin: 2px; display: flex; flex-flow: row;">
    										<h5 style="flex: 0 1 auto; align-self: center; margin: 5px;">
    											Commandes à ajouter : 
    										</h5>
        									<input type="text" id="jobNumber" style="flex: 1 1 auto; margin: 5px;">
        									<button id="addJobButton" type="button" onclick="addJobButtonPressed();" 
        										style="float: right; width: auto; flex: 0 1 auto; margin: 5px;">
        										Ajouter
        									</button>
        								</div>
    								</td>
    							</tr>
    						</tfoot>
    					</table>
    				</div>
   				</div>
			</div>
		</div>

		<!--  Fenêtre Modal pour message d'erreurs -->
		<div id="errMsgModal" class="modal" onclick='$(this).css({"display": "none"});' >
			<div id="errMsg" class="modal-content" style='color: #FF0000;'></div>
		</div>
		
		<!--  Fenêtre Modal pour message de validation -->
		<div id="validationMsgModal" class="modal" onclick='$(this).css({"display": "none"});' >
			<div id="validationMsg" class="modal-content" style='color: #FF0000;'></div>
		</div>
		
		<!--  Fenêtre Modale pour envoi des données pour génération des programmes -->
		<div id="downloadMsgModal" class="modal" onclick='$(this).css({"display": "none"});' 
			style="color: #FF0000; text-align: center;">
			<div id="downloadMsg" class="modal-content">
				<h4>Choisissez une option</h4>
				<hr>
				<button onclick="generateConfirm(1);">Télécharger vers CutRite</button>
				<div style="width: 50px; display: inline-block;"></div>
				<button onclick="generateConfirm(2);">Télécharger au format zip</button>
				<br>
				<hr>
				<h1>Cliquer sur cette fenêtre pour la fermer...</h1>
			</div>
		</div>
		
		<!--  Fenêtre Modal pour chargement -->
		<div id="loadingModal" class="modal loader-modal">
			<div id="loader" class="loader modal-content"></div>
		</div>

		<script src="/Planificateur/assets/js/moment.min.js"></script>
		<script src="/Planificateur/assets/js/moment-timezone.js"></script>
		<script src="/Planificateur/assets/js/jquery.min.js"></script>
		<script src="/Planificateur/assets/js/jquery.dropotron.min.js"></script>
		<script src="/Planificateur/assets/js/skel.min.js"></script>
		<script src="/Planificateur/assets/js/util.js"></script>
		<script src="/Planificateur/assets/js/main.js"></script>
		<script src="/Planificateur/js/main.js"></script>
		<script src="/Planificateur/js/toolbox.js"></script>
		<script src="/Planificateur/sections/batch/js/batch.js"></script>
		<script src="/Planificateur/sections/batch/js/index.js"></script>
		<script src="/Planificateur/sections/batch/js/jobsTable.js"></script>
		<script src="/Planificateur/sections/batch/js/main.js"></script>
		<script src="/Planificateur/sections/batch/js/sessionDataStorage.js"></script>
	</body> 
</html>