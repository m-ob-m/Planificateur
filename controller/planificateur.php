<?php

include_once __DIR__ . '/../lib/config.php';		// Fichier de configuration
include_once __DIR__ . '/../lib/connect.php';	// Classe de connection à la base de données
include_once __DIR__ . '/../sections/batch/model/batch.php'; //Classe modèle de batch
include_once __DIR__ . '/../sections/job/model/job.php';

/**
 * \name		PlanificateurController
 * \author    	Mathieu Grenier
 * \version		1.0
 * \date       	2017-01-18
 *
 * \brief 		Cette classe est utilisée comme controleur pour la page de planification
 * \details 	Cette classe est utilisée comme controleur pour la page de planification
 */
class PlanificateurController 
{
	
	private $_db;	//Connection à la base de donn�es
	
	private $_batches;	// 
	
	function __construct(){
		$this->_db = new FabPlanConnection();
		$this->fetchBatch();
	}
	
	function __destruct(){
		$this->_db = NULL;
	}
	
	
	function connexion(){
		return $this->_db;
	}
	
	
	
	/**
	 * \name		fetchBatch
	 * \author    	Mathieu Grenier
	 * \version		1.0
	 * \date       	2017-01-31
	 *
	 * \brief       Prend les batchs entre les deux bornes spécifiées
	 * \details    	Prend les batchs entre les deux bornes spécifiées
	 *
	 * \return    	PlanificateurController Cet objet
	 */
	function fetchBatch($start = null, $end = null) : PlanificateurController
	{	
		$this->_batches = array();
		
		//Convert inputs to date
		if($end === null)
		{
		    $end = date_create();
		}
		else
		{
		    $end = date_create_from_format('Y/m/d', $end);
		}
		
		if($start === null)
		{
		    $start = $end->add(date_interval_create_from_date_string("-1 month"));
		}
		else
		{
		    $start = date_create_from_format('Y/m/d', $start);
		}
		
		
		//  Retrieve batches between specified boundaries.
		$stmt1 = $this->_db->getConnection()->prepare('
            SELECT `b`.`id_batch` AS `id`, `b`.`nom_batch` AS `name`, `b`.`date_debut` AS `startDate`, `b`.`date_fin` AS `endDate`, 
                `b`.`jour_complet` AS `fullDay`, `b`.`commentaire` AS `comments`, `b`.`etat` AS `status`, 
                `b`.`etat_mpr` AS `mprStatus`, `b`.`carrousel` AS `carrousel`, `b`.`estampille` AS `timestamp`
            FROM `fabplan`.`batch` AS `b`
            WHERE `b`.`date_debut` BETWEEN :start AND :end 
            ORDER BY `b`.`id_batch` DESC;
        ');
		$stmt1->bindValue(':start', $start->format('Y/m/d'), PDO::PARAM_STR);
		$stmt1->bindValue(':end', $end->format('Y/m/d'), PDO::PARAM_STR);
		$stmt1->execute();
		
        // Add batches to object
        while ($batchRow = $stmt1->fetch())
        {
            $batch = new StdClass();
            $batch->id = $batchRow["id"];
            $batch->name = $batchRow["name"];
            $batch->start = $batchRow["startDate"];
            $batch->end = $batchRow["endDate"];
            $batch->fullDay = $batchRow["fullDay"];
            $batch->status = $batchRow["status"];
            $batch->jobs = array();
            
            $stmt2 = $this->_db->getConnection()->prepare('
                SELECT `j`.`numero` AS `name`
                FROM `fabplan`.`batch` AS `b`
                INNER JOIN `fabplan`.`batch_job` AS `bj` ON `b`.`id_batch` = `bj`.`batch_id`
                INNER JOIN `fabplan`.`job` AS `j` ON `bj`.`job_id` = `j`.`id_job`
                WHERE `b`.`id_batch` = :batchId
                ORDER BY `j`.`numero` ASC;
            ');
            $stmt2->bindValue(':batchId', $batch->id, PDO::PARAM_INT);
            $stmt2->execute();
            
            while ($jobRow = $stmt2->fetch())
            {
                array_push($batch->jobs, $jobRow["name"]);
            }
            
            array_push($this->_batches, $batch);
        }
		
		return $this;
	}
	
	
	
	/**
	 * \name		batchEvents
	 * \author    	Mathieu Grenier
	 * \version		1.0
	 * \date       	2017-01-31
	 *
	 * \brief       Génère les évènements des batchs
	 * \details    	Génère les évènements des batchs
	 *
	 * \return    	String javascript des batchs
	 */
	function batchEvents()
	{
		
		$events = array();
		
		foreach ($this->_batches as $batch)
		{
			$event = new stdClass();
			$event->editable = true;
			
			$event->color = $this->couleurEtat($batch->status , $batch->end);
							
			$event->url = "sections/batch/index.php?id={$batch->id}";
			$event->id = $batch->id;
			
			$jobList = "";
			foreach($batch->jobs as $job)
			{
			    $jobList .= (empty($jobList) ? "{$job}" : " {$job}");
			}
			
			$event->title = "{$batch->name}\n{$jobList}";
				
			if($batch->fullDay == 'Y')
			{
				$event->allDay = true;
			}
			else
			{
			    $event->allDay = false;
			}
					
			$event->start = $batch->start;
			$event->end = $batch->end;
			
			array_push($events, $event);
		}
		
		return $events;
	}
	
	
	/**
	 * \name		couleurEtat
	 * \author    	Mathieu Grenier
	 * \version		1.0
	 * \date       	2017-01-31
	 *
	 * \brief       Génère la couleur du fond selon l'état
	 * \details    	Génère la couleur du fond selon l'état
	 *
	 * \param		$etat Etat du nest
	 * \param		$date_fin Date de fin du nest
	 *
	 * \return    	Code HEX de la couleur
	 */
	public static function couleurEtat($etat, $date_fin)
	{
		switch ($etat)
		{
			case 'T':	// Terminée
				return '#3B3131';
                break;
			case 'X':	// En exécution
				return  '#127031';
				break;
			default:				
				if($date_fin <= new DateTime()) // Si en retard
				{
					return  '#990012';
				}
				
				switch ($etat)
				{
					case 'E':	// Entrée
						return  '#0b5788';
						break;
					case 'A':	// Attente
						return  '#848482';
						break;
					case 'N':	// Non-livrée
						return  '#DAA520';
						break;
					case 'P':	// Pressant
						return  '#CC6600';
						break;
				}
		}
		return null;	// état invalide == NULL		
	}
}
?>