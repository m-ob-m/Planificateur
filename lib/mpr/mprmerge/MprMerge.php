<?php 
    include_once __DIR__ . "/MprMergeInputFile.php";
    include_once __DIR__ . "/MprVariable.php";
    
    /**
     * \MprMerge
     * Manipulates the mprmerge.exe program
     *
     *
     * @package
     * @subpackage
     * @author     Marc-Olivier Bazin-Maurice
     */
    class MprMerge implements \JsonSerializable
    {
        private const MERGER_FILE_OPTIONS = array(
            "FileReplace" => "0",
            "VariableReplace" => "0",
            "VariableIndex" => "0",
            "VariableAppend" => "0",
            "ContourInsert" => "0", 
            "Offset" => "0",
            "NewCoordWithOffset" => "0",
            "PathToH1" => "C:\MACHINE1\a1\h1", 
            "Optimize" => "0",
            "Wegopti" => "0", 
            "Mirror" => "0", 
            "YMirror" => "0", 
            "SizeAdjust" => "0", 
            "QuickandDirty" => "0", 
            "RemoveNeu" => "0", 
            "FinishOffset" => "0", 
            "RawOffset" => "0", 
            "UseInternalVar" => "0",
            "CountPart" => "0",
            "MakeLinear" => "0", 
            "KeepOrder" => "0", 
            "VfraesenDrehen" => "0", 
            "MessOptimize" => "0"
        );
        
        /**
         * MprMerge constructor
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprMerge This MprMerge
         */ 
        private function __construct(){}
        
        /**
         * Linearization function
         *
         * @param \MprMergeInputFile | \stdClass | string $inputFile An array containing the input file and its 
         *                                                           parameters.
         * @param string $outputFilePath The desired path of the output mpr file.
         * @param array[\MprVariable | \stdClass] $variables An array of variables applied to the the program before 
         *                                                   linearization.
         *
         * @throws \UnexpectedMprMergeInputFileParametersFormatException If the functions fails to generate a valid 
         *                                                               .mrg file.
         * @throws \UnexpectedVariableFormatException If the variables are not provided in a valid format.
         * @author Marc-Olivier Bazin-Maurice
         * @return 
         */ 
        public static function linearize($inputFile, string $outputFilePath, array $variables = array()) : void
        {
            $instance = new self();
            
            foreach($variables as &$variable)
            {
                /* Variables that are set both in the parameters file and in the component will be assigned
                 * the parameters file's instance.
                 */
                if(is_a($variable, "\MprVariable", FALSE))
                {
                    $variableData .= $variable->getMprRepresentation();
                }
                elseif(is_a($variable, "\stdClass", FALSE))
                {
                    $variableData .= (\MprVariable::fromObject($variable))->getMprRepresentation();
                }
                else
                {
                    throw new \UnexpectedVariableFormatException();
                }
            }
            
            $parameterFile = $instance->createTemporaryVariableFile($variables);
            try
            {
                if(is_a($inputFile, "\MprMergeInputFile", FALSE))
                {
                    //$inputFile is already a \MprMergeInputFile.
                }
                elseif(is_a($inputFile, "\stdClass", FALSE))
                {
                    $inputFile = \MprMergeInputFile::fromObject($inputFile);
                }
                elseif(is_string($inputFile))
                {
                    $inputFile = new \MprMergeInputFile($inputFile);
                }
                else
                {
                    throw new \UnexpectedMprMergeInputFileParametersFormatException();
                }
                
                $mergerFile = $instance->createTemporaryMergerFile(
                    $outputFilePath, 
                    $inputFile,
                    array(
                        "FileReplace" => "1",
                        "ContourInsert" => "1", 
                        "NewCoordWithOffset" => "1", 
                        "Wegopti" => "1", 
                        "QuickandDirty" => "1",
                        "MakeLinear" => "1"
                    )
                );
                
                try
                {
                    // Perform linearization
                    $application = "C:\\Program Files (x86)\\Homag Group\\woodWOP6\\mprmerge.exe";
                    $commandStatus = null;
                    $commandOutput = null;
                    exec(
                        "\"{$application}\" \"-f={$mergerFile}\" \"-m={$parameterFile}\"", 
                        $commandOutput, 
                        $commandStatus
                    );
                    if($commandStatus !== 0)
                    {
                        throw new \Exception("Error {$commandStatus}: " . implode("\r\n", $commandOutput));
                    }
                }
                catch(\Exception $e)
                {
                    throw $e;
                }
                finally
                {
                    unlink($mergerFile);
                }
            }
            catch(\Exception $e)
            {
                throw $e;
            }
            finally
            {
                unlink($parameterFile);
            }
        }
        
        /**
         * Merge function
         *
         * @param array[\MprMergeInputFile | \stdClass | string] $inputFiles An array containing the input files and 
         *                                                                   their parameters.
         * @param string $outputFilePath The desired path of the output mpr file.
         *
         * @throws \UnexpectedMprMergeInputFileParametersFormatException If the functions fails to generate a valid 
         *                                                               .mrg file.
         * @throws \UnexpectedVariableFormatException If the variables are not provided in a valid format.
         * @author Marc-Olivier Bazin-Maurice
         * @return
         */ 
        public static function merge(array $inputFiles, string $outputFilePath) : void
        {
            $instance = new self();
            
            foreach ($inputFiles as &$inputFile) 
            {
                if(is_a($inputFile, "\MprMergeInputFile", FALSE))
                {
                    //$inputFile is already a \MprMergeInputFile.
                }
                elseif(is_a($inputFile, "\stdClass", FALSE))
                {
                    $inputFile = \MprMergeInputFile::fromObject($inputFile);
                }
                elseif(is_string($inputFile))
                {
                    $inputFile = new \MprMergeInputFile($inputFile);
                }
                else
                {
                    throw new \UnexpectedMprMergeInputFileParametersFormatException();
                }
            }
            
            $mergerFile = $instance->createTemporaryMergerFile(
                $outputFilePath,
                $inputFiles,
                array(
                    "FileReplace" => "1",
                    "VariableIndex" => "1",
                    "ContourInsert" => "1",
                    "NewCoordWithOffset" => "1",
                    "Optimize" => "1",
                    "Wegopti" => "1",
                    "UseInternalVar" => "1",
                    "CountPart" => "1",
                    "KeepOrder" => "1"
                )
            );
            
            try
            {
                // Perform linearization
                $application = "C:\\Program Files (x86)\\Homag Group\\woodWOP6\\mprmerge.exe";
                $commandStatus = null;
                $commandOutput = null;
                exec(
                    "\"{$application}\" \"-f={$mergerFile}\"", 
                    $commandOutput, 
                    $commandStatus
                );
                if($commandStatus !== 0)
                {
                    throw new \Exception("Error {$commandStatus}: " . implode("\r\n", $commandOutput));
                }
            }
            catch(\Exception $e)
            {
                throw $e;
            }
            finally
            {
                unlink($mergerFile);
            }
        }
        
        /**
         * Creates a temporary variables file used to manipulate variables when using mprmerge.exe for linearization 
         * purposes.
         *
         * @param array[\MprVariable] $variables The variables that must be applied to the component before 
         *                                       linearization.
         *
         * @throws \Exception If the temporary file could not be created.
         * @author Marc-Olivier Bazin-Maurice
         * @return string The path of the temporary variables file.
         */ 
        private function createTemporaryVariableFile(array $variables = array()) : string
        {
            $timestamp = round(microtime(true) * 1000);
            $variableFilePath = sys_get_temp_dir() . "\\ComponentLevelParameters{$timestamp}.VAR";
            $variableData = "[H\r\n" . 
            "VERSION=\"Variablenliste WoodWOP 4.0 Alpha\"\r\n" . 
            "\r\n" . 
            "[001\r\n";
            
            foreach($variables as $variable)
            {
                $variableData .= $variable->getMprRepresentation();
            }
            
            $variableData .= "!\r\n";
            if(file_put_contents($variableFilePath, mb_convert_encoding($variableData, "ISO-8859-1", "UTF-8")))
            {
                return $variableFilePath;
            }
            else
            {
                throw new \Exception("The variable file could not be created.");
            }
        }
        
        /**
         * Creates a temporary merger file used to indicate mprMerge how to merge the files together and / or how to 
         * simplify.
         *
         * @param string $outputFile The path of the output file.
         * @param \MprMergeInputFile[] | \MprMergeInputFile $inputFiles The inputFiles and their parameters.
         * @param string[] $options An associative array of options to apply to mprmerge.exe. By default, the numeric 
         *                         ones are all set to 0.
         *
         * @throws \Exception If the temporary file could not be created.
         * @author Marc-Olivier Bazin-Maurice
         * @return string The path of the temporary merger file.
         */ 
        private function createTemporaryMergerFile(string $outputFile, $inputFiles, array $options = array()) : string
        {
            $timestamp = round(microtime(true) * 1000);
            $mergerFilePath = sys_get_temp_dir() . "\\ComponentLevelMerger{$timestamp}.mrg";
            
            if(!is_array($inputFiles))
            {
                $inputFiles = array($inputFiles);
            }
            
            $mergerData = "[Options]\r\n";
            foreach(self::MERGER_FILE_OPTIONS as $key => $defaultValue)
            {
                if(isset($options[$key]))
                {
                    $mergerData .= "{$key}={$options[$key]}\r\n";
                }
                else
                {
                    $mergerData .= "{$key}={$defaultValue}\r\n";
                }
            }
            
            $mergerData .= "\r\n" . 
            "[Destination]\r\n" . 
            "FileName={$outputFile}\r\n" . 
            "\r\n";
            
            $index = 1;
            foreach($inputFiles as $inputFile)
            {
                $mergerData .= "[FILE{$index}]\r\n" . $inputFile->getMergerFileRepresentation();
                $index++;
            }
            
            $mergerData .= "\r\n";
            if(file_put_contents($mergerFilePath, mb_convert_encoding($mergerData, "ISO-8859-1", "UTF-8")))
            {
                return $mergerFilePath;
            }
            else
            {
                throw new \Exception("The merger file could not be created.");
            }
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