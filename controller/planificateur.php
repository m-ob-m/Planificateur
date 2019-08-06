<?php

require_once __DIR__ . '/../lib/config.php';		// Fichier de configuration
require_once __DIR__ . '/../lib/connect.php';	// Classe de connection à la base de données
require_once __DIR__ . '/../sections/batch/model/batch.php'; //Classe modèle de batch
require_once __DIR__ . '/../sections/job/model/job.php';

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
	
	private $_db;	//Connection à la base de données
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
	function fetchBatch($start = null, $end = null) : \PlanificateurController
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
		$this->_db->getConnection()->beginTransaction();
		$stmt = $this->_db->getConnection()->prepare('
            SELECT `b`.`id_batch` AS `batchId`, `b`.`nom_batch` AS `batchName`, `b`.`date_debut` AS `batchStartDate`, 
                `b`.`date_fin` AS `batchEndDate`, `b`.`jour_complet` AS `batchFullDay`, 
                `b`.`commentaire` AS `batchComments`, `b`.`etat` AS `batchStatus`, `b`.`etat_mpr` AS `batchMprStatus`, 
                `b`.`carrousel` AS `batchCarrousel`, `b`.`estampille` AS `batchTimestamp`, `j`.`numero` AS `jobName`
			FROM `batch` AS `b`
			INNER JOIN `batch_job` AS `bj` ON `b`.`id_batch` = `bj`.`batch_id`
            INNER JOIN `job` AS `j` ON `bj`.`job_id` = `j`.`id_job`
            WHERE `b`.`date_debut` BETWEEN :start AND :end 
            ORDER BY `b`.`id_batch` DESC;
        ');
		$stmt->bindValue(':start', $start->format('Y/m/d'), PDO::PARAM_STR);
		$stmt->bindValue(':end', $end->format('Y/m/d'), PDO::PARAM_STR);
		$stmt->execute();
		
        // Add batches to object
        while ($row = $stmt->fetch())
        {
			if(empty($this->batches) || end($this->batches)->id !== $row["batchId"])
			{
				$this->_batches[] = (object)array(
					"id" => $row["batchId"],
					"name" => $row["batchName"],
					"start" => $row["batchStartDate"],
					"end" => $row["batchEndDate"],
					"fullDay" => $row["batchFullDay"],
					"status" => $row["batchStatus"],
					"jobs" => array($row["jobName"])
				);
			}
			else
			{
				end($this->batches)->jobs[] = $row["jobName"];
			}
        }
        $this->_db->getConnection()->commit();
        
		return $this;
	}
	
	
	
	/**
	 * Returns an array ob batches for the javascript interface.
	 * @return \StdClass[] The array of batches.
	 */
	function batchEvents()
	{
		$events = array();
		
		foreach ($this->_batches as $batch)
		{
			$event = new \stdClass();
			$event->editable = true;
			
			$event->color = $this->couleurEtat($batch->status , \DateTime::createFromFormat("Y-m-d H:i:s", $batch->end));
							
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
	 * Determines the color of the event block on the javascript interface.
	 * @param string $etat The status of the batch.
	 * @param \DateTime $date_fin The end date of the event.
	 * 
	 * @return string The hex code of the color of the event block on the javascript interface.
	 */
	public static function couleurEtat(?string $etat, ?\DateTime $date_fin) : ?string
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