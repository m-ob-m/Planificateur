<?php
    /**
     * \name		Visualisateur de Nest
    * \author    	Mathieu Grenier
    * \version		1.0
    * \date       	2017-02-07
    *
    * \brief 		Menu pour imprimer des étiquettes
    * \details 		Menu pour imprimer des étiquettes
    */
    
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/batch/controller/batchController.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/visualiseur/model/nestedPanelCollection.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/clientInformation/clientInformation.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/numberFunctions/numberFunctions.php";

    // Initialize the session
    session_start();
                                                                            
    // Check if the user is logged in, if not then redirect him to login page
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        if(!empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest")
        {
            throw new \Exception("You are not logged in.");
        }
        else
        {
            header("location: /Planificateur/lib/account/logIn.php");
        }
        exit;
    }

    
    // Getting a connection to the database.
    $db = new \FabPlanConnection();

    // Closing the session to let other scripts use it.
    session_write_close();

    $error = null;
    $batch = null;
    if(is_positive_integer_or_equivalent_string($_GET["id"] ?? null))
    {
        try
        {
            $db->getConnection()->beginTransaction();
            $batch = \Batch::withID($db, intval($_GET["id"])) ?? new \Batch();
            $db->getConnection()->commit();
        }
        catch(\Exception $e)
        {
            $db->getConnection()->rollback();
        }
    }
    else 
    {
        $batch = new \Batch();
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
    
    $tempDirectory = $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/visualiseur/temp/";
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
<html>
	<head>
		<title><?= $batch->getName(); ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="../../assets/css/responsive.css" />
		<link rel="stylesheet" href="../../assets/css/fabridor.css" />
		<link rel="stylesheet" href="../../assets/css/imageButton.css">
        <link rel="stylesheet" href="css/labelling.css">
	</head>
    <body style="background-image: none; background-color: #FFFFFF;">
        <div class="no-print" style="padding: 5px;">
                <label for="findBatchName">Nom de batch : </label>
                <input id="batchName" value="">
                <button id="findBatch">Trouver</button>
                <button class="no-print" onclick="window.close();" style="float: right; margin-right: 2px;">
                    <img src="../../images/exit.png" style="width: 16px; height: 16px;">
                Sortir</button>
        </div>
		<div style="display: flex; flex-flow: row;">
			<div style="flex: 1 1 auto;">
			<?php if($collection !== null && !empty($collection->getPanels())): ?>
            	<?php foreach($collection->getPanels() as $index => $panel): ?>
        			<div class="pannelContainer" style="page-break-after: always;">
                    	<!-- Entete de navigation (on veut l'avoir sur chaque page lors de l'impression) -->
                    	<div style="width: 100%; margin-top: 2px; margin-bottom: 2px; text-align: center; overflow: hidden;">
                            <button title="Premier" class="no-print goToFirst">&lt;&lt;</button>
                            <button title="Précédent" class="no-print goToPrevious">&lt;</button>
                    		<div id="index" style="display: inline-block; border: 1px black solid; padding: 2px;"><?= 
                                ($index + 1) . " / " . count($collection->getPanels()); 
                            ?></div>
                            <button title="Suivant" class="no-print goToNext">&gt;</button>
                            <button title="Dernier" class="no-print goToLast">&gt;&gt;</button>
                            <button class="no-print printAll">Imprimer tout</button> 
                    		<div id="quantity" style="display: inline-block; border: 1px black solid; padding: 2px;">Qté : <?= 
                                $panel->getQuantity(); 
                            ?></div>
                    		<div id="batchName" style="display: inline-block; border: 1px black solid; padding: 2px;"><?= 
                                $batch->getName(); 
                             ?></div>
                    	</div>
                    	
                    	<div style="display: flex; flex-flow: row;">
                        	<div style="flex: 1 1 auto; float: left;"></div>
                        	<div style="flex: 0 1 auto;">
                        		<?php $sourceFileName = $batch->getName() . fillZero($index + 1, 4) . ".jpg";?>
                        		<?php $sourceFilePath = CR_FABRIDOR . "SYSTEM_DATA\\DATA\\" . $sourceFileName; ?>
                        		<?php $destinationFilePathFromRoot = "/Planificateur/sections/visualiseur/temp/panel_{$sourceFileName}"; ?>
                                <?php $destinationFilePath = $_SERVER["DOCUMENT_ROOT"] . $destinationFilePathFromRoot; ?>
                                <?php copy($sourceFilePath, $destinationFilePath); ?>
                        		<div class="pannel">
                        			<img src="temp/panel_<?= $sourceFileName; ?>" style="display: block;">
                        			<?php foreach($panel->getParts() as $part): ?>
                        				<?php $idjtp = $part->getJobTypePorteId(); ?>
										<?php $jobTypePorte = \JobTypePorte::withID(new \FabplanConnection(), $idjtp); ?>
										<?php $jobType = \JobType::withID(new \FabplanConnection(), $jobTypePorte->getJobTypeId()); ?>
										<?php $job = \Job::withID(new \FabplanConnection(), $jobType->getJobId()); ?>
										<?php $model = $jobType->getModel(); ?>
                        				<?php $mpr = $part->getMprName(); ?>
										<?php $rot0_180 = $part->getRotation() == 0 || $part->getRotation() == 180 ?>
                        				<?php $x = $part->getXCoordinate(); ?>
										<?php $y = $part->getYCoordinate(); ?>
										<?php $w = $part->getWidth(); ?>
										<?php $l = 100 * ($x - ($rot0_180 ? $part->getHeight() : $w) / 2) / $panel->getLength(); ?>
                    				    <?php $t = 100 * ($y - ($rot0_180 ? $w : $part->getHeight()) / 2) / $panel->getWidth(); ?>
    									<?php $w = 100 * ($rot0_180 ? $part->getHeight() : $w) / $panel->getLength(); ?>
                    				    <?php $h = 100 * ($rot0_180 ? $w : $part->getHeight()) / $panel->getWidth(); ?>
    									<div class="porte" data-id="<?= $idjtp; ?>" 
    										style="left: <?= $l; ?>%; top: <?= $t; ?>%; width: <?= $w; ?>%; height: <?= $h; ?>%;">
                        					<?= $job->getName(); ?><br>
                        					<?= $model->getDescription(); ?><br>
                        					<?= $part->getHeightIn() . " X " . $part->getWidthIn(); ?>
                        				</div>
                        			<?php endforeach; ?>
                        		</div>
                            </div>
                            <div style="flex: 1 1 auto; float: right;"></div>
                    	</div>
                	</div>
                <?php endforeach; ?>
                <?php else: ?>
                    <p>Il n'y a rien à afficher. Veuillez regénérer le projet.</p>
                <?php endif;?>
            </div>
    	</div>
    	
    	<script type="text/javascript" src="../../assets/js/ajax.js"></script>
		<script type="text/javascript" src="../../assets/js/docReady.js"></script>
		<script type="text/javascript" src="../../js/main.js"></script>
		<script type="text/javascript" src="../../js/toolbox.js"></script>
        <script type="text/javascript" src="<?= "js/viewer.js"; ?>"></script>
        <script type="text/javascript" src="<?= "js/labelling.js"; ?>"></script>
	</body>
</html>	