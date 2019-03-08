<?php
    namespace MprGlobalParameters
    {
        include_once __DIR__ . "/../config.php";
        
        /**
         * \MprGlobalParameters\MprGlobalParametersFile
         * An interface for the global parameters file to use with mpr files
         *
         *
         * @package
         * @subpackage
         * @author     Marc-Olivier Bazin-Maurice
         */
        class MprGlobalParametersFile implements \JsonSerializable
        {
            public const SOURCE_PATH = WWGLOB_VAR;
            public const DESTINATION_PATH = __DIR__ . "\\temp\\wwwglob.var";
            private $parameters;
            
            /**
             * MprGlobalParametersFile constructor
             *
             * @throws
             * @author Marc-Olivier Bazin-Maurice
             * @return \MprGlobalParameters\MprGlobalParametersFile This MprGlobalParametersFile
             */ 
            public function __construct()
            {
                $this->parameters = array();
            }
            
            /**
             * Parses the global parameters file.
             *
             * @throws
             * @author Marc-Olivier Bazin-Maurice
             * @return \MprGlobalParameters\MprGlobalParametersFile This MprGlobalParametersFile
             */ 
            public function parse() : \MprGlobalParameters\MprGlobalParametersFile
            {
                $fileContents = $this->obtainWorkingCopy()->read();
                
                $matches = array();
                $fileContents = preg_replace("/(?<!\r)\n|\r(?!\n)/", "\r\n", $fileContents);
                if(preg_match("/\A.*\[001\r\n(?<ParametersSection>.*)![\r\n]*\z/s", $fileContents, $matches))
                {
                    $pattern = "/(?<=\A|\r\n)(?<key>[A-Za-z_][\w]{0,7})=\"(?<value>.*?)\"\r\n" . 
                        "KM=\"(?<description>.*?)\"(?=\r\n|\z)/";
                    preg_match_all($pattern, $matches["ParametersSection"], $matches, PREG_SET_ORDER);
                    
                    $this->parameters = array();
                    foreach($matches as $match)
                    {
                        $parameter = (object) array(
                            "key" => $match["key"], "value" => $match["value"], "description" => $match["description"]
                        );
                        array_push($this->parameters, $parameter);
                    }
                }
                else 
                {
                    throw new \Exception("The global parameters file contains an error.");
                }
                
                return $this;
            }
            
            /**
             * Obtains a working copy of the global parameters file.
             *
             * @throws
             * @author Marc-Olivier Bazin-Maurice
             * @return \MprGlobalParameters\MprGlobalParametersFile This MprGlobalParametersFile
             */ 
            private function obtainWorkingCopy() : \MprGlobalParameters\MprGlobalParametersFile
            {
                /* Create the location for the working copy of the global variables file if it doesn't exist. */
                $sourceDirectoryPath = dirname(self::SOURCE_PATH);
                $destinationDirectoryPath = dirname(self::DESTINATION_PATH);
                if(!file_exists($destinationDirectoryPath))
                {
                    mkdir($destinationDirectoryPath, 0777);
                }
                
                /* Acquire a working copy of the global variables file */
                if(!copy(self::SOURCE_PATH, self::DESTINATION_PATH))
                {
                    if(!file_exists(self::DESTINATION_PATH))
                    {
                        throw new \Exception("The system failed to obtain a copy of the global variables file.");
                    }
                }
                
                return $this;
            }
            
            /**
             * Reads the contents of the global parameters file.
             *
             * @throws
             * @author Marc-Olivier Bazin-Maurice
             * @return string The contents of the file
             */ 
            private function read() : ?string
            {
                $fileHandle = fopen(self::DESTINATION_PATH, "r");
                if(!$fileHandle)
                {
                    throw new \Exception("The system failed to read the global variables file.");
                }
                $fileContents = fread($fileHandle, filesize(self::DESTINATION_PATH));
                fclose($fileHandle);
                
                return $fileContents;
            }
            
            /**
             * Returns the parameters as a key => value pairs array.
             *
             * @throws
             * @author Marc-Olivier Bazin-Maurice
             * @return array A key => value array of global parameters
             */ 
            public function getGlobalParametersAsKeyValuePairs() : array
            {
                $globalParameters = array();
                foreach($this->parameters as $parameter)
                {
                    $globalParameters[$parameter->key] = $parameter->value;
                }
                
                return $globalParameters;
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
    }
?>