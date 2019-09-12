<?php
/**
 * \name		NestedPanelCollection
 * \author    	Mathieu Grenier
 * \version		1.0
 * \date       	2017-02-09
 *
 * \brief 		Represents a nested pannels collection in Cut Rite
 * \details 	Represents a nested pannels collection in Cut Rite
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/visualiseur/model/nestedPanel.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/sections/visualiseur/model/nestedPart.php";

class NestedPanelCollection
{
	private $_panels;
	private $_currentIndex;
    
	/**
	 * NestedPanelCollection constructor
	 *
	 * @param \Batch $batch A Batch object
	 * @param string $pc2FileContents The contents of the .pc2 file associated to $batch
	 * @param string $cttFileContents The contents of the .ctt file associated to $batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanelCollection
	 */ 
	function __construct(\Batch $batch, ?string $pc2FileContents, ?string $cttFileContents)
	{
		$this->_panels = array();
		$this->createPanneauxFromPc2($batch, $pc2FileContents, $cttFileContents);
	}
    
	/**
	 * Fills the NestedPanelCollection object with the contents of a .pc2 file and of its associated .ct2 file.
	 *
	 * @param \Batch $batch A Batch object
	 * @param string $pc2File The contents of the .pc2 file associated to $batch
	 * @param string $cttFile The contents of the .ctt file associated to $batch
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanelCollection
	 */ 
	private function createPanneauxFromPc2(\Batch $batch, ?string $pc2FileContent, ?string $cttFileContent) : \NestedPanelCollection
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
						$this->addPanel(\NestedPanel::withPc2Line($line));
						$this->last();
						break;
				
					case "5":
						$this->_panels[$this->_currentIndex]->addPart(\NestedPart::withPc2LineAndCttFile($batch, $line, $cttFileContent));
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
	 * @return \NestedPanelCollection
	 */ 
	private function setIndex(int $index) : \NestedPanelCollection
	{
		if($index > (count($this->_panels) -1))
		{
		    throw new \Exception("Invalid index \"{$index}\" in collection.");
		}
		elseif($index < -count($this->_panels))
	    {
	        throw new \Exception("Invalid index \"{$index}\" in collection.");
	    }
	    elseif($index < 0)
	    {
	        $this->_currentIndex = count($this->_panels) + $index;
	    }
	    else 
	    {
	        $this->_currentIndex = $index;
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
	    return $this->_currentIndex;
	}
	
	/**
	 * Adds a NestedPanel to the NestedPanelCollection.
	 *
	 * @param \NestedPanel $panel The NestedPanel to add to the NestedPanelCollection
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanelCollection
	 */
	private function addPanel(\NestedPanel $panel) : \NestedPanelCollection
	{
	    array_push($this->_panels, $panel);
	    return $this;
	}
	
	/**
	 * Removes the current pannel to the collection.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanelCollection
	 */
	private function removePanel() : \NestedPanelCollection
	{
	    array_splice($this->_panels, $this->getIndex(), 1);
	    return $this;
	}
	
	/**
	 * Returns a panel from the collection.
	 *
	 * @param int|null [$index = null] The index of the panel in the collection (if null, current element is returned)
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanel
	 */
	public function getPanneau(?int $index = null) : \NestedPanel
	{
	    return  $this->_panels($index ?? $this->getIndex());
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
	 * Get the array of NestedPanel in the collection.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return \NestedPanel[] The array of NestedPanel in the collection
	 */
	public function getPanels()
	{	
		return $this->_panels;	
	}
}
?>