<?php
/**
* \name		Carrousel
* \author    	Mathieu Grenier
* \version		1.0
* \date       	2017-02-16
*
* \brief 		Carrousel de la CNC
* \details 		Représente le carrousel de la CNC et sa gestion des outils
*/

class Carrousel implements JsonSerializable
{
	private $_MAX_OUTILS = 14;			// Maximum d'outils du carrousel
	private $_VIDE = "-";			// Valeur d'une case vide
	private $_outils;					// Outils du carrousel
	private $_nospaces;					// Outils qui ne peuvent être mis sur le carrousel
	private $_symbolicToolNames = array(
	    "_T_CUT" => 130,
	    "_T_FIN" => 132,
	    "_PROF_V" => 133,
	    "_PROF_V2" => 133.2,
	    "_PROF_V3" => 133.1,
	    "_T_PCKT" => 134,
	    "_T_AFF" => 136,
	    "_TPERAFF" => 137,
	    "_PROF_A" => 140,
	    "_PROF_B" => 141,
	    "_PROF_C" => 142,
	    "_PROF_D" => 143,
	    "_PROF_F" => 144,
	    "_PROF_G" => 145,
	    "_PROF_Q" => 132,
	    "_PROF_AA" => 154,
	    "_TCCSHAK" => 164,
	    "_T_SPCKT" => 199,
	);
	
	/**
	 * Carrousel constructor
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Carrousel This Carrousel
	 */ 
	public function __construct()
	{
		$this->empty();
	}
		
	
	/**
	 * Carrousel constructor using a csv string as input
	 * @param string $csv A csv string containing a list of tools
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Carrousel This Carrousel
	 */ 
	public static function fromCsv(string $csv) :Carrousel
	{
	    $tools = str_getcsv($csv, ",");
	    
	    $instance = new self();
	    for($i = 0; $i < count($tools); $i++)
	    {
	        $instance->addTool($tools[$i], $i);
	    }
	    
	    return $instance;
	}
	
	/**
	 * Converts the carrrousel to a comma separated value string
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return string The carrousel as a comma separated value string
	 */
	public function toCsv() : ?string
	{
	    $csv = "";
	    for($i = 0; $i < $this->_MAX_OUTILS; $i++)
	    {
	        if($csv === "")
	        {
	            $csv = $this->_outils[$i];
	        }
	        else
	        {
	            $csv .= "," . $this->_outils[$i];
	        }
	    }
	    
	    if(count($this->_nospaces) > 0)
	    {
	        foreach($this->_nospaces as $tool)
	        {
	           $csv .= "," . $tool;
	        }
	    }
	    
	    return $csv;
	}
		
	/**
	 * Adds a tool to the carrousel
	 * @param mixed $tool A tool Number or a symbolic tool name
	 * @param int $position The position where the tool should be inserted 
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Carrousel This Carrousel
	 */ 
	public function addTool($tool, int $position = -1) 
	{
	    $toolNumber = $this->getToolNumber($tool);
	    
	    if($position === -1)
	    {
	        $newPosition = array_search("-", $this->_outils, TRUE);
	        $position = (($newPosition === FALSE) ? $position : $newPosition);
	    }
	    
	    if($position <= ($this->_MAX_OUTILS - 1) && $position >= 0)
	    {
	        //echo $position . "\n";
	        $this->_outils[$position] = (is_numeric($toolNumber) ? strval(floor($toolNumber)) : strval($toolNumber));
	    }
	    else
	    {
	        //echo $position . "\n";
	        array_push($this->_nospaces, (is_numeric($toolNumber) ? strval(floor($toolNumber)) : strval($toolNumber)));
	    }
	}
	
	
	/**
	 * Gets a tool number from a symbolic name. If $tool is not a known symbolic name, it is returned as is.
	 * @param mixed $tool The symbolic name of a tool or the tool number itself
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return mixed The tool number associated to the provided symbolic name
	 */ 
	private function getToolNumber($name)
	{
	    return ((array_key_exists($name, $this->_symbolicToolNames)) ? $this->_symbolicToolNames[$name] : $name);
	}
	
	
	/**
	 * Tests if a tool is already present in the carrousel
	 * @param mixed $toolnumber A tool number
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return boolean True if tool is already present, false otherwise.
	 */ 
	public function toolExists($toolNumber) :bool
	{	
	    return ((array_search(strval($toolNumber), array_merge($this->_outils, $this->_nospaces), true) === false) ? false : true);
	}
	
	/**
	 * Returns true if the carrousel is overloaded
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return boolean True if tool is already present, false otherwise.
	 */ 
	public function isOverloaded() :bool
	{
		return isempty($this->_nospaces) ? true : false;
	}
	
	/**
	 * Returns a list of the tools that don't fit in the Carrousel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array The tools that couldn't fit into the Carrousel
	 */ 
	public function getNoSpaces() :array
	{
		return $this->_nospaces;
	}
	
	/**
	 * Returns the configuration of the Carrousel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array An array that represents the Carrousel
	 */
	public function getTools() :array
	{
	    return $this->_outils;
	}
	
	/**
	 * Get the size of the carrousel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return int The amount of positions in the carrousel
	 */
	public function getSize() :int
	{
	    return $this->_MAX_OUTILS;
	}
	
	/**
	 * Get the array of symbolic tool names
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array The symbolic tool names array
	 */
	public function getSymbolicToolNamesArray() : array
	{
	    return $this->_symbolicToolNames;
	}
	
	/**
	 * Set the size of the carrousel
	 * @param int $size The amount of positions in the Carrousel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Carrousel This Carrousel
	 */
	public function setSize(int $size) : Carrousel
	{
	    $this->_MAX_OUTILS = $size;
	    return $this;
	}
	
	/**
	 * Empties the carrousel
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return Carrousel This Carrousel
	 */ 
	public function empty() : Carrousel
	{
	    $this->_outils = array();
	    $this->_nospaces = array();
	    $this->_case = 0;		// Le carrousel est à la première case
	    
	    // Initialisation du carrousel
	    for($i = 0; $i <= ($this->_MAX_OUTILS -1); $i++)
	    {
	        $this->_outils[$i] = $this->_VIDE;
	    }
	    
	    return $this;
	}
	
	/**
	 * Get a JSON compatible representation of this object.
	 *
	 * @throws
	 * @author Marc-Olivier Bazin-Maurice
	 * @return array This object in a JSON compatible format
	 */
	public function jsonSerialize() : ?array
	{
	    return get_object_vars($this);
	}
}
?>
