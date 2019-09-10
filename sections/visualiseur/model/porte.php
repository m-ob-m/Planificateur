<?php
require_once __DIR__ . "/../../../lib/numberFunctions/numberFunctions.php";

/**
 * \name		Porte
 * \author    	Mathieu Grenier
 * \version		1.0
 * \date       	2017-02-09
 *
 * \brief 		Représente une porte d'un panneau de CutRite
 * \details 	Représente une porte d'un panneau de CutRite
 */
class Porte
{
	private $_numero;
	private $_coorX;
	private $_coorY;
	private $_hauteur;
	private $_largeur;
	private $_rotation;
	
	private $_noCommande;
	private $_idJobType;
	private $_idJobTypePorte;
	private $_modele;
	private $_nomMpr;
	
	private $_scale = 0.492;
	private $_offSetX = 10;
	private $_offSetY = 35;
    
	/**
	 * Porte constructor
	 *
	 * @param int $numero The sequential number associate to the Porte
	 * @param float $coordX The X coordinate of the porte on the pannel
	 * @param float $coordY The Y coordinate of the porte on the pannel
	 * @param float $height The height of the Porte in millimeters
	 * @param float $width The width of the Porte in millimeters
	 * @param string $heightIn The height of the Porte in inches
	 * @param string $widthIn The width of the Porte in inches
	 * @param float $rotation The rotation angle of the Porte in degrees
	 * @param string $ordno The order number associated to this Porte
	 * @param int $idJobType The id of the JobType associated to this Porte
	 * @param int $idJobTypePorte The id of the JobTypePorte associated to this Porte
	 * @param string $model The name of the model associated to this Porte
	 * @param string $mprName The name of the mpr file associated to this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Panneau
	 */ 
	function __construct(int $numero, float $coordX, float $coordY, float $height, float $width, string $heightIn, string $widthIn, 
	    float $rotation, string $ordNo, int $idJobType, int $idJobTypePorte, string $model, string $mprName)
	{
		$this->_numero = $numero;
		$this->_coorX = $coordX;
		$this->_coorY = $coordY;
		$this->_hauteur = $height;
		$this->_largeur = $width;
		$this->_hauteurPo = $heightIn;
		$this->_largeurPo = $widthIn;
		$this->_rotation = $rotation;
		$this->_noCommande = $ordNo;
		$this->_idJobType = $idJobType;
		$this->_idJobTypePorte = $idJobTypePorte;
		$this->_modele = $model;
		$this->_nomMpr = $mprName;
	}

	/**
	 * A Porte constructor that uses a line from a .pc2 file and .ctt file
	 *
	 * @param Batch $batch The batch associated to this Porte
	 * @param string $line A line from a .pc2 file
	 * @param string $cttFileContents The contents of a .ctt file
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Porte
	 */ 
	public static function withLine(\Batch $batch, string $line, string $cttFileContents) : \Porte
	{	
		$parts = explode(",", $line);

		$cttLines = explode("\r\n", $cttFileContents);
		foreach($cttLines as $cttLine)
		{	
			$head = substr($cttLine,0,4);	//Entete de la ligne
			
			if($head === "PNL1")
			{
				$PNL1 = $cttLine;
			}
		    elseif($head === "PNL2")
		    {
				$PNL2 = $cttLine;
		    }
			elseif($head === "PNL3")
			{
				$PNL3 = $cttLine;
			}
			elseif($head === "PNL4")
			{
				$PNL4 = $cttLine;
				
				if(strpos($PNL3, "P" . $parts[1]) > -1)
				{	
				    // Si PNL3 contient P#, c'est la bonne section
					$matches = array();
					preg_match("/(?<=,Comm=)[^,\r\n]*(?=,|\z)/", $PNL4, $matches);
					$matches2 = array();
					preg_match("/\A(?P<noCommande>.*)_(?P<idJobTypePorte>\d+)\z/", $matches[0], $matches2);
					$noCommande = $matches2["noCommande"];
					$idJobType = explode("_",explode("," , $PNL1)[1])[2];
					$idJobTypePorte = $matches2["idJobTypePorte"];
					$modele = explode("_",explode("," , $PNL1)[1])[0];
					$nomMpr = explode("," , $PNL1)[1];
				
					break;	// Sortir de la boucle
				}
			}			
		}
		
		$hauteur = null;
		$largeur = null;
		foreach($batch->getJobs() as $job)
		{
			foreach($job->getJobTypes() as $jtype)
			{
				foreach($jtype->getParts() as $porte)
				{
					if($porte->getId() === intval($idJobTypePorte))
					{
						$hauteur = $porte->getLength();
						$largeur = $porte->getWidth();
						break;
					}
				}
			}
		}
        
		if($hauteur !== null && $largeur !== null)
		{
		    $numero  = intval($parts[1]);
		    $x = floatval($parts[2]);
		    $y = floatval($parts[3]);
		    $h = floatval($parts[4]);
		    $w = floatval($parts[5]);
		    $hIn = toMixedNumber(floatval($hauteur) / 25.4 , 16);
		    $wIn = toMixedNumber(floatval($largeur) / 25.4, 16);
		    $rotation = floatval($parts[6]);
		    $idjt = intval($idJobType);
		    $idjtp = intval($idJobTypePorte);
		    return new self($numero, $x, $y, $h, $w, $hIn, $wIn, $rotation, $noCommande, $idjt, $idjtp, $modele, $nomMpr);
		}
		else 
		{
		    throw new \Exception("This Batch was modified and must be reprocessed.");
		}
	}
	
	/**
	 * Returns the numero of this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The numero of this Porte
	 */ 
	public function getNumero() : int
	{
		return $this->_numero;
	}
	
	/**
	 * Returns the left coordinate of this Porte on the view
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The left coordinate of this Porte on the view
	 */ 
	public function getViewLeft() : float
	{			
		if($this->_rotation == 0 || $this->_rotation == 180)
		{
			return ($this->getCoorX() - $this->getHauteur() / 2) * $this->_scale + $this->_offSetX;
		}
		else 
		{
            return ($this->getCoorX() - $this->getLargeur() / 2) * $this->_scale + $this->_offSetX;
		}
	}
	
	/**
	 * Returns the X coordinate of this Porte on its associated Pannel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The X coordinate of this Porte on its associated Pannel
	 */ 
	public function getCoorX() : float
	{
		return $this->_coorX;
	}
	
	/**
	 * Returns the top coordinate of this Porte on the view
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The top coordinate of this Porte on the view
	 */ 
	public function getViewTop() : float
	{	
		if($this->_rotation == 0 || $this->_rotation == 180)
		{
			return ($this->getCoorY() - $this->getLargeur() / 2) * $this->_scale + $this->_offSetY;
		}
		else 
		{
		    return ($this->getCoorY() - $this->getHauteur() / 2) * $this->_scale + $this->_offSetY;
		}
	}
	
	/**
	 * Returns the Y coordinate of this Porte on its associated Pannel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The Y coordinate of this Porte on its associated Pannel
	 */ 
	public function getCoorY() : float
	{
		return $this->_coorY;
	}
	
	/**
	 * Returns the height of this Porte on the view
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The height of this Porte on the view
	 */ 
	public function getViewHeight() : float
	{	
		if($this->_rotation == 0 || $this->_rotation == 180)
		{
			return $this->getHauteur() * $this->_scale;
		}
		else 
		{
            return $this->getLargeur() * $this->_scale;
		}
	}
	
	/**
	 * Returns the width of this Porte on the view
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The width of this Porte on the view
	 */ 
	public function getViewWidth() : float
	{
		
		if($this->_rotation == 0 || $this->_rotation == 180)
		{
			return $this->getLargeur() * $this->_scale;
		}
		else   
		{
		    return $this->getHauteur() * $this->_scale;
		}
	}
	
	/**
	 * Returns the height in millimeters of this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The height in millimeters of this Porte
	 */ 
	public function getHauteur() : float
	{		
		return $this->_hauteur;
	}
	
	/**
	 * Returns the width in millimeters of this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The width in millimeters of this Porte
	 */ 
	public function getLargeur() : float
	{
		return $this->_largeur;
	}
	
	/**
	 * Returns the height in inches of this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The height in inches of this Porte
	 */ 
	public function getHauteurPo() : string
	{
		return $this->_hauteurPo;
	}
	
	/**
	 * Returns the width in inches of this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The width in inches of this Porte
	 */ 
	public function getLargeurPo() : string
	{
		return $this->_largeurPo;
	}
	
	/**
	 * Returns the rotation in degrees of this Porte on its associated Panneau
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The rotation in degrees of this Porte on its associated Panneau
	 */ 
	public function getRotation() : float
	{
		return $this->_rotation;
	}
	
	/**
	 * Returns the order number of this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The order number of this Porte
	 */ 
	public function getNoCommande() : string
	{
		return $this->_noCommande;
	}
	
	/**
	 * Returns the id of the JobType associated to this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of the JobType associated to this Porte
	 */ 
	public function getIdJobType() : int
	{
		return $this->_idJobType;
	}
	
	/**
	 * Returns the id of the JobTypePorte associated to this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of the JobTypePorte associated to this Porte
	 */ 
	public function getIdJobTypePorte() : int
	{
		return $this->_idJobTypePorte;
	}
	
	/**
	 * Returns the name of the Model associated to this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The name of the Model associated to this Porte
	 */ 
	public function getModele() : string
	{
		return $this->_modele;
	}
	
	/**
	 * Returns the name of the .mpr file associated to this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The name of the .mpr file associated to this Porte
	 */ 
	public function getNomMpr() : string
	{
		return $this->_nomMpr;
	}
		
}
?>