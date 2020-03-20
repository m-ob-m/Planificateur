<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/numberFunctions/numberFunctions.php";

/**
 * \name		NestedPart
 * \author    	Mathieu Grenier
 * \version		1.0
 * \date       	2017-02-09
 *
 * \brief 		Represents a nested part in Cut Rite
 * \details 	Represents a nested part in Cut Rite
 */
class NestedPart
{
	private $_number;
	private $_xCoordinate;
	private $_yCoordinate;
	private $_height;
	private $_width;
	private $_rotation;
	private $_mprName;
	private $_idJobTypePorte;
	
	private const SCALE = 0.492;
	private const OFFSET_X = 0;
	private const OFFSET_Y = 30;
    
	/**
	 * Porte NestedPart
	 *
	 * @param int $number The sequential number associate to the NestedPart
	 * @param float $x The X coordinate of the NestedPart on its NestedPanel in millimeters
	 * @param float $y The Y coordinate of the NestedPart on its NestedPanel in millimeters
	 * @param float $h The height of the NestedPart in millimeters
	 * @param float $w The width of the NestedPart in millimeters
	 * @param float $r The rotation angle of the NestedPart on its NestedPanel in degrees
	 * @param int $idJobTypePorte The id of the JobTypePorte associated to this Porte
	 * @param string $mprName The name of the mpr file associated to this Porte
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPart
	 */ 
	function __construct(int $number, float $x, float $y, float $h, float $w, float $r, int $jobTypePorteId, string $mprName)
	{
		$this->setNumber($number);
		$this->setXCoordinate($x);
		$this->setYCoordinate($y);
		$this->setHeight($h);
		$this->setWidth($w);
		$this->setRotation($r);
		$this->setJobTypePorteId($jobTypePorteId);
		$this->setMprName($mprName);
	}

	/**
	 * A NestedPart constructor that uses a line from a .pc2 file and the contents of a .ctt file
	 *
	 * @param \Batch $batch The batch that owns the NestedPanel referenced by the pc2 line
	 * @param string $pc2Line A line from a .pc2 file
	 * @param string $cttFileContents The contents of a .ctt file
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPart A \NestedPart
	 */ 
	public static function withPc2LineAndCttFile(\Batch $batch, string $pc2Line, string $cttFileContents) : \NestedPart
	{	
		$parts = explode(",", $pc2Line);

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
				
				if(preg_match("/\APNL3,P{$parts[1]}:TC,1,,2\z/", $PNL3))
				{	
				    // Si PNL3 contient P#, c'est la bonne section
					$matches = array();
					preg_match("/(?<=,IdJobTypePorte=)(?P<idJobTypePorte>\d+)(?=,|\\z)/", $PNL4, $matches);
					$idJobTypePorte = isset($matches["idJobTypePorte"]) ? intval($matches["idJobTypePorte"]) : null;
					preg_match("/(?<=\\APNL1,)(?P<mprName>.*?)(?=,)/", $PNL1, $matches);
					$mprName = $matches["mprName"] ?? null;
				
					break;	// Sortir de la boucle
				}
			}			
		}
		
		$part = null;
		foreach($batch->getJobs() as $job)
		{
			foreach($job->getJobTypes() as $jtype)
			{
				foreach($jtype->getParts() as $jtpart)
				{
					if($jtpart->getId() === intval($idJobTypePorte))
					{
						$part = $jtpart;
						break 3;
					}
				}
			}
		}
        
		if($part !== null)
		{
		    $numero = intval($parts[1]);
		    $x = floatval($parts[2]);
		    $y = floatval($parts[3]);
		    $h = $jtype->getType()->getGeneric()->getHeightParameter() == "LPX" ? $part->getLength() : $part->getWidth();
		    $w = $jtype->getType()->getGeneric()->getHeightParameter() == "LPX" ? $part->getWidth() : $part->getLength();
		    $rotation = floatval($parts[6]);
		    return new self($numero, $x, $y, $h, $w, $rotation, $idJobTypePorte, $mprName);
		}
		else 
		{
		    throw new \Exception("This Batch was modified and must be reprocessed.");
		}
	}
	
	/**
	 * Returns the sequential number of this NestedPart
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The sequential number of this NestedPart
	 */ 
	public function getNumber() : int
	{
		return $this->_number;
	}
	
	/**
	 * Returns the left coordinate of this NestedPart on the view
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The left coordinate of this NestedPart on the view
	 */ 
	public function getViewLeft() : float
	{			
		if($this->_rotation == 0 || $this->_rotation == 180)
		{
			return ($this->getXCoordinate() - $this->getHeight() / 2) * self::SCALE + self::OFFSET_X;
		}
		else 
		{
            return ($this->getXCoordinate() - $this->getWidth() / 2) * self::SCALE + self::OFFSET_X;
		}
	}
	
	/**
	 * Returns the X coordinate of this NestedPart on its associated NestedPanel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The X coordinate of this NestedPart on its associated NestedPanel
	 */ 
	public function getXCoordinate() : float
	{
		return $this->_xCoordinate;
	}
	
	/**
	 * Returns the top coordinate of this NestedPart on the view
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The top coordinate of this NestedPart on the view
	 */ 
	public function getViewTop() : float
	{	
		if($this->_rotation == 0 || $this->_rotation == 180)
		{
			return ($this->getYCoordinate() - $this->getWidth() / 2) * self::SCALE + self::OFFSET_Y;
		}
		else 
		{
		    return ($this->getYCoordinate() - $this->getHeight() / 2) * self::SCALE + self::OFFSET_Y;
		}
	}
	
	/**
	 * Returns the Y coordinate of this NestedPart on its associated NestedPanel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The Y coordinate of this NestedPart on its associated NestedPanel
	 */ 
	public function getYCoordinate() : float
	{
		return $this->_yCoordinate;
	}
	
	/**
	 * Returns the height of this NestedPart on the view
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The height of this NestedPart on the view
	 */ 
	public function getViewHeight() : float
	{	
		if($this->_rotation == 0 || $this->_rotation == 180)
		{
			return $this->getHeight() * self::SCALE;
		}
		else 
		{
            return $this->getWidth() * self::SCALE;
		}
	}
	
	/**
	 * Returns the width of this NestedPart on the view
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The width of this NestedPart on the view
	 */ 
	public function getViewWidth() : float
	{
		
		if($this->_rotation == 0 || $this->_rotation == 180)
		{
			return $this->getWidth() * self::SCALE;
		}
		else   
		{
		    return $this->getHeight() * self::SCALE;
		}
	}
	
	/**
	 * Returns the height in millimeters of this NestedPart
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The height in millimeters of this NestedPart
	 */ 
	public function getHeight() : float
	{		
		return $this->_height;
	}
	
	/**
	 * Returns the width in millimeters of this NestedPart
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The width in millimeters of this NestedPart
	 */ 
	public function getWidth() : float
	{
		return $this->_width;
	}
	
	/**
	 * Returns the height in inches of this NestedPart
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The height in inches of this NestedPart
	 */ 
	public function getHeightIn() : string
	{
		return toMixedNumber($this->_height / 25.4 , 16);
	}
	
	/**
	 * Returns the width in inches of this NestedPart
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The width in inches of this NestedPart
	 */ 
	public function getWidthIn() : string
	{
		return toMixedNumber($this->_width / 25.4 , 16);
	}
	
	/**
	 * Returns the rotation in degrees of this NestedPart on its associated NestedPanel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The rotation in degrees of this NestedPart on its associated NestedPanel
	 */ 
	public function getRotation() : float
	{
		return $this->_rotation;
	}
	
	/**
	 * Returns the id of the JobTypePorte associated to this NestedPart
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The id of the JobTypePorte associated to this NestedPart
	 */ 
	public function getJobTypePorteId() : int
	{
		return $this->_jobTypePorteId;
	}
	
	/**
	 * Returns the name of the .mpr file associated to this NestedPart
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The name of the .mpr file associated to this NestedPart
	 */ 
	public function getMprName() : string
	{
		return $this->_mprName;
	}
	
	/**
	 * Sets this NestedPart's sequential number
	 * @param int $number The sequential number of this NestedPart
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPart This NestedPart
	 */ 
	public function setNumber(int $number) : \NestedPart
	{
		$this->_number = $number;
		return $this;
	}

	/**
	 * Sets this NestedPart's X coordinate
	 * @param float $xCoordinate The X coordinate of this NestedPart on its associated NestedPanel
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPart This NestedPart
	 */ 
	public function setXCoordinate(float $xCoordinate) : \NestedPart
	{
		$this->_xCoordinate = $xCoordinate;
		return $this;
	}

	/**
	 * Sets this NestedPart's Y coordinate
	 * @param float $yCoordinate The Y coordinate of this NestedPart on its associated NestedPanel
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPart This NestedPart
	 */ 
	public function setYCoordinate(float $yCoordinate) : \NestedPart
	{
		$this->_yCoordinate = $yCoordinate;
		return $this;
	}

	/**
	 * Sets this NestedPart's height
	 * @param float $height The height of this NestedPart
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPart This NestedPart
	 */ 
	public function setHeight(float $height) : \NestedPart
	{
		$this->_height = $height;
		return $this;
	}

	/**
	 * Sets this NestedPart's width
	 * @param float $width The width of this NestedPart
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPart This NestedPart
	 */ 
	public function setWidth(float $width) : \NestedPart
	{
		$this->_width = $width;
		return $this;
	}

	/**
	 * Sets this NestedPart's rotation
	 * @param float $width The rotation of this NestedPart on its associated NestedPanel
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPart This NestedPart
	 */ 
	public function setRotation(float $rotation) : \NestedPart
	{
		$this->_rotation = $rotation;
		return $this;
	}

	/**
	 * Sets this NestedPart's associated JobtypePorte's ID
	 * @param int $jobTypePorteId The id of the JobtypePorte associated to this NestedPart
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPart This NestedPart
	 */ 
	public function setJobTypePorteId(int $jobTypePorteId) : \NestedPart
	{
		$this->_jobTypePorteId = $jobTypePorteId;
		return $this;
	}

	/**
	 * Sets this NestedPart's associated mpr file name
	 * @param int $mprName The name of the mpr file associated to this NestedPart
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPart This NestedPart
	 */ 
	public function setMprName(string $mprName) : \NestedPart
	{
		$this->_mprName = $mprName;
		return $this;
	}
}
?>