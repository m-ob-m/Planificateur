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
    
    require_once __DIR__ . "/../batch/controller/batchController.php";
    require_once __DIR__ . "/model/nestedPanelCollection.php";
   
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
    
    $error = null;
    $batch = null;
    $db = new \FabPlanConnection();
    try
    {
        $db->getConnection()->beginTransaction();
        $batch =  \Batch::withID($db, $_GET["id"]) ?? new \Batch();
        $db->getConnection()->commit();
    }
    catch(\Exception $e)
    {
        $db->getConnection()->rollback();
    }
    finally
    {
        $db = null;
    }
    
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
    
    $collection = null;
    try
    {
        $collection = (new \NestedPanelCollection($batch, $pc2FileContents, $cttFileContents));
    }
    catch(\Exception $e)
    {
        /* Do nothing. */
    }
    
    
    $now = time();
    
    $tempDirectory = __DIR__ . "/temp/";
    if (!file_exists($tempDirectory)) {
        mkdir($tempDirectory, 0777, true);
    }
    
    // Suppression des vieilles images
    $scan = scandir($tempDirectory);
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
<html style="height: 100%;">
	<head>
		<title><?= $batch->getName(); ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="../../assets/css/responsive.css" />
		<link rel="stylesheet" href="../../assets/css/fabridor.css" />
		<link rel="stylesheet" href="../../assets/css/parametersTable.css"/>
		<link rel="stylesheet" href="../../assets/css/imageButton.css">
	</head>
	<body style="background-image: none; background-color: #FFFFFF; height: 100%;">
		<div style="display: flex; flex-flow: row; height: 100%;">
			<div style="flex: 1 1 auto; height: 100%;">
			<?php if($collection !== null && !empty($collection->getPanels())): ?>
            	<?php foreach($collection->getPanels() as $index => $panel): ?>
        			<div class="pannelContainer" style="height: 100%; display: flex; flex-direction: column;">
                    	<!-- Entete de navigation (on veut l'avoir sur chaque page lors de l'impression) -->
                    	<div style="width: 100%; padding-top: 2px; padding-bottom: 2px; text-align: center; overflow: hidden; flex: 0 0 auto;">
                            <button title="Premier" class="no-print goToFirst">&lt;&lt;</button>
                            <button title="Précédent" class="no-print goToPrevious">&lt;</button>
                    		<div id="index" style="display: inline-block; border: 1px black solid; padding: 2px;"><?= 
                                ($index + 1) . " / " . count($collection->getPanels()); 
                            ?></div>
                            <button title="Suivant" class="no-print goToNext">&gt;</button>
                            <button title="Dernier" class="no-print goToLast">&gt;&gt;</button>
                    		<button class="no-print printSingle">Imprimer</button>
                    		<button class="no-print printAll">Imprimer tout</button> 
                    		<div id="quantity" style="display: inline-block; border: 1px black solid; padding: 2px;">Qté : <?= 
                                $panel->getQuantity(); 
                            ?></div>
                    		<div id="batchName" style="display: inline-block; border: 1px black solid;  padding: 2px;"><?= 
                                $batch->getName(); 
                             ?></div>
                    		<button class="no-print" onclick="window.close();" style="float: right; margin-right: 2px;">
                    			<img src="../../images/exit.png" style="width: 16px; height: 16px;">
                    		Sortir</button>
                    	</div>
                    	
                        <div style="flex: 1 1 auto; display: flex; flex-flow: row; height: 100%;">
                            <div style="flex: 1 1 auto; float: left;"></div>
                            <div class="pannel" style="max-width: 100%; max-height: 100%;">
                                <?php $sourceFileName = $batch->getName() . fillZero($index + 1, 4) . ".jpg";?>
                                <?php $sourceFilePath = CR_FABRIDOR . "SYSTEM_DATA\\DATA\\" . $sourceFileName; ?>
                                <?php $destinationFilePath = __DIR__ . "/temp/panel_{$sourceFileName}"; ?>
                                <?php copy($sourceFilePath, $destinationFilePath); ?>
                                <img src="temp/panel_<?= $sourceFileName; ?>" style="max-height:100%; max-width: 100%;">
                                <?php foreach($panel->getParts() as $part): ?>
                                    <?php $idjtp = $part->getJobTypePorteId(); ?>
                                    <?php $jobTypePorte = \JobTypePorte::withID(new \FabplanConnection(), $idjtp); ?>
                                    <?php $jobType = \JobType::withID(new \FabplanConnection(), $jobTypePorte->getJobTypeId()); ?>
                                    <?php $job = \Job::withID(new \FabplanConnection(), $jobType->getJobId()); ?>
                                    <?php $model = $jobType->getModel(); ?>
                                    <?php $mpr = $part->getMprName(); ?>
                                    <?php $l = 100 * ($part->getXCoordinate() - (in_array($part->getRotation(), array(0, 180)) ? $part->getHeight() : $part->getWidth()) / 2) / $panel->getLength(); ?>
                                    <?php $t = 100 * ($part->getYCoordinate() - (in_array($part->getRotation(), array(0, 180)) ? $part->getWidth() : $part->getHeight()) / 2) / $panel->getWidth(); // Haut de pièce décalé de 30px vers le bas. ?>
                                    <?php $w = 100 * (in_array($part->getRotation(), array(0, 180)) ? $part->getHeight() : $part->getWidth()) / $panel->getLength(); ?>
                                    <?php $h = 100 * (in_array($part->getRotation(), array(0, 180)) ? $part->getWidth() : $part->getHeight()) / $panel->getWidth(); ?>
                                    <div class="porte no-print" style="left: <?= $l; ?>%; top: <?= $t; ?>%; width: <?= $w; ?>%; height: <?= $h; ?>%;">
                                        <?= $job->getName(); ?><br>
                                        <?= $model->getDescription(); ?><br>
                                        <?= $part->getHeightIn() . " X " . $part->getWidthIn(); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="flex: 1 1 auto; float: right;"></div>
                    	</div>
                	</div>
                <?php endforeach; ?>
            <?php else: ?>
    			<p>Il n'y a rien à afficher. Veuillez regénérer le projet.</p>
    		<?php endif;?>
            </div>
        	<div id="rightPannel" class="no-print" style="flex: 0 1 auto; display: none;">	
                <!-- Visualisation des propriétés -->
        		<table class="parametersTable" style="width: 100%;">
            		<thead>
                		<tr>
                			<td style="padding-left: 5px; padding-right: 5px;">Propriétés de la porte</td>
                			<td>
                				<img id="propertiesWindowCloseButton" src="../../images/closewin.png" 
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
		<div id="errMsgModal" class="modal" onclick='this.style.display = "none";'>
			<div id="errMsg" class="modal-content" style='color:#FF0000;'></div>
		</div>
    	
    	<script type="text/javascript" src="../../assets/js/ajax.js"></script>
		<script type="text/javascript" src="../../assets/js/docReady.js"></script>
		<script type="text/javascript" src="../../js/main.js"></script>
		<script type="text/javascript" src="../../js/toolbox.js"></script>
        <script type="text/javascript" src="js/viewer.js"></script>
        <script type="text/javascript" src="js/index.js"></script>
	</body>
</html>	