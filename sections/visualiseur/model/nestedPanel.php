<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/connect.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/parametres/material/model/material.php";

/**
 * \name		NestedPanel
 * \author    	Mathieu Grenier
 * \version		1.0
 * \date       	2017-02-09
 *
 * \brief 		Represents a nested pannel in Cut Rite
 * \details 	Represents a nested pannel in Cut Rite
 */


class NestedPanel
{
	private $_number;
	private $_quantity;
	private $_length;
	private $_width;
	private $_thickness;
	private $_material;
	private $_parts;

	/**
	 * NestedPannel constructor
	 *
	 * @param int $number The sequential number associate to the NestedPannel
	 * @param int $quantity The quantity of this NestedPannel
	 * @param float $length The length of this NestedPannel
	 * @param float $width The width of this NestedPannel
	 * @param float $thickness The thickness of this NestedPannel
	 * @param \Material $material The material of this NestedPannel
	 * @param \NestedParts[] $parts the parts nested on this NestedPannel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Panneau
	 */ 
	function __construct(int $number, int $quantity, float $length, float $width, float $thickness, \Material $material, array $parts = array())
	{
		$this->setNumber($number);
		$this->setQuantity($quantity);
		$this->setLength($length);
		$this->setWidth($width);
		$this->setThickness($thickness);
		$this->setMaterial($material);
		$this->setParts($parts);
	}
	
	/**
	 * A NestedPanel constructor that uses a line from a pc2 file
	 *
	 * @param string $line A line from a .pc2 file
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanel This NestedPanel
	 */ 
	public static function withPc2Line(string $line) : \NestedPanel
	{
		$parts = explode(",", $line);
		$material = \Material::withCutRiteCode(new FabplanConnection(), $parts[10]);
		return new self(intval($parts[1]), intval($parts[12]), floatval($parts[4]), floatval($parts[6]), floatval($parts[8]), $material);
	}
	
	/**
	 * Adds a NestedPart to the NestedPanel
	 *
	 * @param \NestedPart $nestedPart The NestedPart to add to the NestedPanel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanel This NestedPanel
	 */ 
	public function addPart(\NestedPart $nestedPart) : \NestedPanel
	{
		array_push($this->_parts, $nestedPart);
		return $this;
	}
    
	/**
	 * Returns this NestedPanel's sequential number
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The sequential number of this NestedPanel
	 */ 
	public function getNumber() : int
	{
		return $this->_number;
	}
	
	/**
	 * Returns this NestedPanel's quantity
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The quantity of this NestedPanel
	 */ 
	public function getQuantity() : int
	{
		return $this->_quantity;
	}
	
	/**
	 * Returns this NestedPanel's length
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The length of this NestedPanel
	 */ 
	public function getLength() : float
	{
		return $this->_length;
	}
	
	/**
	 * Returns this NestedPanel's width
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The width of this NestedPanel
	 */ 
	public function getWidth() : float
	{
		return $this->_width;
	}
	
	/**
	 * Returns this NestedPanel's thickness
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return float The thickness of this NestedPanel
	 */ 
	public function getThickness() : float
	{
		return $this->_thickness;
	}
	
	/**
	 * Returns this NestedPanel's Material
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \Material The Material of this NestedPanel
	 */ 
	public function getMaterial() : \Material
	{
		return $this->_material;
	}
	
	/**
	 * Returns the array of NestedPart in this NestedPanel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPart[] The array of NestedPart in this NestedPanel
	 */ 
	public function getParts() : array
	{
		return $this->_parts;
	}

	/**
	 * Sets this NestedPanel's sequential number
	 * @param int $number The sequential number of this NestedPanel
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanel This NestedPanel
	 */ 
	public function setNumber(int $number) : \NestedPanel
	{
		$this->_number = $number;
		return $this;
	}
	
	/**
	 * Sets this NestedPanel's quantity
	 * @param int $quantity The quantity of this NestedPanel
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanel This NestedPanel
	 */ 
	public function setQuantity(int $quantity) : \NestedPanel
	{
		$this->_quantity = $quantity;
		return $this;
	}
	
	/**
	 * Sets this NestedPanel's length
	 * @param float $length The length of this NestedPanel
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanel This NestedPanel
	 */ 
	public function setLength(float $length) : \NestedPanel
	{
		$this->_length = $length;
		return $this;
	}
	
	/**
	 * Sets this NestedPanel's width
	 * @param float $width The width of this NestedPanel
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanel This NestedPanel
	 */ 
	public function setWidth(float $width) : \NestedPanel
	{
		$this->_width = $width;
		return $this;
	}
	
	/**
	 * Sets this NestedPanel's thickness
	 * @param float $thickness The thickness of this NestedPanel
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanel This NestedPanel
	 */ 
	public function setThickness(float $thickness) : \NestedPanel
	{
		$this->_thickness = $thickness;
		return $this;
	}
	
	/**
	 * Sets this NestedPanel's Material
	 * @param \Material $material The Material of this NestedPanel
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanel This NestedPanel
	 */ 
	public function setMaterial(\Material $material) : \NestedPanel
	{
		$this->_material = $material;
		return $this;
	}
	
	/**
	 * Sets this NestedPanel's NestedParts array
	 * @param \NestedParts[] $parts The NestedParts array of this NestedPanel
	 * 
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanel This NestedPanel
	 */ 
	public function setParts(array $parts) : \NestedPanel
	{
		$this->_parts = $parts;
		return $this;
	}
}

?>