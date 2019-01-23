<?php
/**
 * \name		CollectionPanneaux
 * \author    	Mathieu Grenier
 * \version		1.0
 * \date       	2017-02-09
 *
 * \brief 		Représente une collection de panneaux
 * \details 	Représente une collection de panneaux
 */

include_once __DIR__ . "/panneau.php";
include_once __DIR__ . "/porte.php";

class CollectionPanneaux
{
	private $_panneaux;
	private $_index;
    
	/**
	 * CollectionPanneaux constructor
	 *
	 * @param Batch $batch A Batch object
	 * @param string $pc2FileContents The contents of the .pc2 file associated to $batch
	 * @param string $cttFileContents The contents of the .ctt file associated to $batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return CollectionPanneaux
	 */ 
	function __construct(Batch $batch, ?string $pc2FileContents, ?string $cttFileContents)
	{		
		$this->_panneaux = array();
		$this->createPanneauxFromPc2($batch,$pc2FileContents,$cttFileContents);
	}
    
	/**
	 * Fills the CollectionPanneaux object with the contents of a .pc2 file and of its associated .ct2 file.
	 *
	 * @param Batch $batch A Batch object
	 * @param string $pc2File The contents of the .pc2 file associated to $batch
	 * @param string $cttFile The contents of the .ctt file associated to $batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return CollectionPanneaux
	 */ 
	private function createPanneauxFromPc2(Batch $batch, ?string $pc2FileContent, ?string $cttFileContent) : CollectionPanneaux
	{
		$lines = explode("\r\n", $pc2FileContent);
				
		$p = null;
		foreach($lines as $line)
		{
			if(trim($line) !== "")
			{
				switch (substr($line, 0, 1))
				{
					case "0":
						$this->addPanneau(Panneau::withLine($line));
						$this->last();
						break;
				
					case "5":
						$this->_panneaux[$this->_index]->addPorte(Porte::withLine($batch, $line, $cttFileContent));
						break;
				}
			}
		}
		
		$this->first();
		return $this;
	}
    
	/**
	 * Sets the current index in the collection.
	 *
	 * @param int $index The new index ranging from 0 to the amount of pannels in the collection - 1. 
	 *                     If negative, the index is set starting from the last element (-1 means last element).
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return CollectionPanneaux
	 */ 
	private function setIndex(int $index) : CollectionPanneaux
	{
		if($index > (count($this->_panneaux) -1))
		{
		    throw new \Exception("Invalid index \"{$index}\" in collection.");
		}
		elseif($index < -count($this->_panneaux))
	    {
	        throw new \Exception("Invalid index \"{$index}\" in collection.");
	    }
	    elseif($index < 0)
	    {
	        $this->_index = count($this->_panneaux) + $index;
	    }
	    else 
	    {
	        $this->_index = $index;
	    }
	    return $this;
	}
	
	/**
	 * Returns the current index in the collection.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The current index in the collection
	 */ 
	public function getIndex() : int
	{
	    return $this->_index;
	}
	
	/**
	 * Adds a pannel to the collection.
	 *
	 * @param Panneau $panneau The pannel to add to the collection
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return CollectionPanneaux
	 */
	private function addPanneau(Panneau $panneau) : CollectionPanneaux
	{
	    array_push($this->_panneaux, $panneau);
	    return $this;
	}
	
	/**
	 * Removes the current pannel to the collection.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return CollectionPanneaux
	 */
	private function removePanneau() : CollectionPanneaux
	{
	    array_splice($this->_panneaux, $this->getIndex(), 1);
	    return $this;
	}
	
	/**
	 * Returns a pannel from the collection.
	 *
	 * @param optional int $index The index of the pannel in the collection (if null, current element is returned)
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return CollectionPanneaux
	 */
	public function getPanneau(?int $index = null) : Panneau
	{
	    return  $this->_panneaux($this->getIndex());
	}
	
	/**
	 * Go to the specified index in the collection.
	 * 
	 * @param int $index The index of the element to go to.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return bool True if no error occured
	 */
	public function goTo(int $index) : bool
	{
	    try
	    {
	        $this->setIndex($index);
	        return true;
	    }
	    catch(\Exception $e)
	    {
	        return false;
	    }
	}
	
	/**
	 * Go to the first pannel in the collection.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return bool True if no error occured
	 */
	public function first() : bool
	{
	    return $this->goTo(0);
	}
	
	/**
	 * Go to the last pannel in the collection.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return bool True if no error occured
	 */
	public function last() : bool
	{
	    return $this->goTo(-1);
	}
	
	/**
	 * Go to the previous pannel in the collection.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return bool True if no error occured
	 */
	public function previous() : bool
	{
	    return $this->goTo($this->getIndex() - 1);
	}
	
	/**
	 * Go to the next pannel in the collection.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return bool True if no error occured
	 */
	public function next() : bool
	{
	    return $this->goTo($this->getIndex() + 1);
	}
	

	/**
	 * Get the array of pannels in the collection.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array[Panneau] The array of pannels in the collection
	 */
	public function getPanneaux()
	{	
		return $this->_panneaux;	
	}
}
?>