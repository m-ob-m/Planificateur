<?php
/**
 * \name		Panneau
 * \author    	Mathieu Grenier
 * \version		1.0
 * \date       	2017-02-09
 *
 * \brief 		Représente un panneau de CutRite
 * \details 	Représente un panneau de CutRite
 */


class Panneau
{
	private $_numero;
	private $_quantite;
	private $_portes;

	/**
	 * Panneau constructor
	 *
	 * @param int $numero The sequential number associate to the pannel
	 * @param int $quantite The quantity of this pannel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Panneau
	 */ 
	function __construct(int $numero, int $quantite)
	{
		$this->_numero = $numero;
		$this->_quantite = $quantite;
		$_portes = array();
	}
	
	/**
	 * A Panneau constructor that uses a line from a pc2 file
	 *
	 * @param string $line A line from a .pc2 file
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Panneau This Panneau
	 */ 
	public static function withLine(string $line) : Panneau 
	{
		$parts = explode("," , $line);
		return new self($parts[1],$parts[12]);
	}
	
	/**
	 * Adds a Porte to the Panneau
	 *
	 * @param Porte $door The Porte to add to the Panneau
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Panneau This Panneau
	 */ 
	public function addPorte(Porte $door) : Panneau
	{
		$this->_portes[count($this->_portes)] = $door;
		return $this;
	}
    
	/**
	 * Returns this Panneau's sequential number
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The sequential number of this Panneau
	 */ 
	public function getNumero() : int
	{
		return $this->_numero;
	}
	
	/**
	 * Returns this Panneau's quantity
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The quantity of this Panneau
	 */ 
	public function getQuantite() : int
	{
		return $this->_quantite;
	}
	
	/**
	 * Returns the array of Porte in this Panneau
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array[Porte] The array of Porte in this Panneau
	 */ 
	public function getPortes() : array
	{
		return $this->_portes;
	}
}

?>