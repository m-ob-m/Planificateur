<?php
    /**
     * \name		Visualisateur de Nest
    * \author    	Mathieu Grenier
    * \version		1.0
    * \date       	2017-02-07
    *
    * \brief 		Menu qui visualise les panneaux de Nest
    * \details 		Menu qui visualise les panneaux de Nest
    */
    
    include_once __DIR__ . "/../batch/controller/batchController.php";
    include_once __DIR__ . "/model/collectionPanneaux.php";
    
    $batch =  (isset($_GET["id"]) ? (new \BatchController())->getBatch($_GET["id"]) : new \Batch());
    
    $pc2Path = CR_FABRIDOR . "\\SYSTEM_DATA\\DATA\\{$batch->getName()}.pc2";
    $cttPath = CR_FABRIDOR . "\\SYSTEM_DATA\\DATA\\{$batch->getName()}.ctt";
    $pc2FileContents = null;
    $cttFileContents = null;
    
    if($pc2File = @fopen($pc2Path, "r"))
    {
        $pc2FileContents = fread($pc2File, filesize($pc2Path));
        fclose($pc2File);
    }
    
    if($cttFile = @fopen($cttPath, "r"))
    {
        $cttFileContents = fread($cttFile, filesize($cttPath));
        fclose($cttFile);
    }
    
    $collection = (new CollectionPanneaux($batch, $pc2FileContents, $cttFileContents));
    
    $now = time();
    
    // Suppression des vieilles images
    $scan = scandir("temp\\");
    foreach($scan as $file)
    {
        // Si le nom du fichier est plus grand que trois lettres pour enlever les répertoires . et ..
        if(strlen($file) > 3)
        {
            // Déterminer la date de création du fichier
            $fdate =  filectime("temp\\\\" . $file);
            if($now - $fdate > 600)
            {
                // Effacer le fichier s'il est plus vieux que 10 minutes
                unlink("temp\\\\" . $file);
            }
        }
    }
    
    /**
     * Appends 0's at the beginning of a number
     * 
     * @param mixed $i Then number to fill
     * @param int $nb The desired length for the resulting string
     * 
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The filled number
     */
    function fillZero($i, int $nb)
    {
        $ret = strval($i);
        while(strlen($ret) < $nb)
        {
            $ret = "0{$ret}";
        }
        return $ret;
    }
?>


<!DOCTYPE HTML>
<html>
	<head>
		<title><?= $batch->getName(); ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="/Planificateur/assets/css/responsive.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/fabridor.css" />
		<link rel="stylesheet" href="/Planificateur/assets/css/parametersTable.css"/>
		<link rel="stylesheet" href="/Planificateur/assets/css/imageButton.css">
	</head>
	<body style="background-image: none; background-color: #FFFFFF;">
		<div style="display: flex; flex-flow: row;">
			<div style="flex: 1 1 auto;">
        	<?php foreach($collection->getPanneaux() as $index => $panneau): ?>
    			<div class="pannelContainer" style="page-break-after: always;">
                	<!-- Entete de navigation (on veut l'avoir sur chaque page lors de l'impression) -->
                	<div style="width: 100%; margin-top: 2px; margin-bottom: 2px; text-align: center; overflow: hidden;">
                		<button title="Premier" class="no-print" onclick="goToFirst();">&lt;&lt;</button>
                		<button title="Précédent" class="no-print" onclick="goToPrevious();">&lt;</button>
                		<div id="index" style="display: inline-block; border: 1px black solid; padding: 2px;"><?= 
                            ($index + 1) . " / " . count($collection->getPanneaux()); 
                        ?></div>
                		<button title="Suivant" class="no-print" onclick="goToNext();">&gt;</button>
                		<button title="Dernier" class="no-print" onclick="goToLast();">&gt;&gt;</button>
                		<button class="no-print" onclick="printPannel();">Imprimer</button>
                		<button class="no-print" onclick="printAllPannels();">Imprimer tout</button> 
                		<div id="quantity" style="display: inline-block; border: 1px black solid; padding: 2px;">Qté : <?= 
                            $panneau->getQuantite(); 
                        ?></div>
                		<div id="batchName" style="display: inline-block; border: 1px black solid;  padding: 2px;"><?= 
                            $batch->getName(); 
                         ?></div>
                		<button class="no-print" onclick="window.close();" style="float: right; margin-right: 2px;">
                			<img src="/Planificateur/images/exit.png" style="width: 16px; height: 16px;">
                		Sortir</button>
                	</div>
                	
                	<div style="display: flex; flex-flow: row;">
                    	<div style="flex: 1 1 auto; float: left;"></div>
                    	<div style="flex: 0 1 auto;">
                    		<?php $sourceFileName = $batch->getName() . fillZero($index + 1, 4) . ".jpg";?>
                    		<?php $sourceFilePath = CR_FABRIDOR . "SYSTEM_DATA\\DATA\\" . $sourceFileName; ?>
                    		<?php $destinationFilePath = __DIR__ . "/temp/panel_{$sourceFileName}"; ?>
                            <?php copy($sourceFilePath, $destinationFilePath); ?>
                    		<div class="pannel">
                    			<img src="temp/panel_<?= $sourceFileName; ?>">
                    			<?php foreach($panneau->getPortes() as $porte): ?>
                    				<?php $idjt = $porte->getIdJobType(); ?>
                    				<?php $idjtp = $porte->getIdJobTypePorte(); ?>
                    				<?php $mpr = $porte->getNomMpr(); ?>
                    				<?php $l = $porte->getViewLeft(); ?>
                				    <?php $t = $porte->getViewTop() - 30; // Le haut de la pièce est décalé de 30px vers le bas. ?>
									<?php $w = $porte->getViewHeight(); ?>
                				    <?php $h = $porte->getViewWidth(); ?>
									<div class="porte no-print" onclick="displayDoorProperties(<?= $idjtp; ?>);" 
										style="left: <?= $l; ?>px; top: <?= $t; ?>px; width: <?= $w; ?>px; height: <?= $h; ?>px;">
                    					<?= $porte->getNoCommande(); ?><br>
                    					<?= $porte->getModele(); ?><br>
                    					<?= $porte->getHauteurPo() . " X " . $porte->getLargeurPo(); ?>
                    				</div>
                    			<?php endforeach; ?>
                    		</div>
                        </div>
                        <div style="flex: 1 1 auto; float: right;"></div>
                	</div>
            	</div>
            <?php endforeach; ?>
            </div>
        	<div id="rightPannel" class="no-print" style="flex: 0 1 auto; display: none;">	
                <!-- Visualisation des propriétés -->
        		<table class="parametersTable" style="width: 100%;">
            		<thead>
                		<tr>
                			<td>Propriétés de la porte</td>
                			<td>
                				<img src="/Planificateur/images/closewin.png" onclick="closePropertiesWindow();" 
                					style="float: right; padding-right: 2px; cursor: pointer;">
                			</td>
                		</tr>
            		<thead>
					<tbody>
					</tbody>
        		</table>
        	</div>
    	</div>
    	
    	<!--  Fenêtre modale pour messages d'erreur -->
		<div id="errMsgModal" class="modal" onclick='$(this).css({"display": "none"});'>
			<div id="errMsg" class="modal-content" style='color:#FF0000;'></div>
		</div>
    	
    	<script src="/Planificateur/assets/js/jquery.min.js"></script>
		<script src="/Planificateur/assets/js/jquery.dropotron.min.js"></script>
		<script src="/Planificateur/assets/js/skel.min.js"></script>
		<script src="/Planificateur/assets/js/util.js"></script>
		<script src="/Planificateur/assets/js/main.js"></script>
		<script src="/Planificateur/js/main.js"></script>
		<script src="/Planificateur/js/toolbox.js"></script>
		<script src="/Planificateur/sections/visualiseur/js/main.js"></script>
	</body>
</html>	