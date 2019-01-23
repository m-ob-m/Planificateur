<?php
    /**
     * \UnexpectedMprMergeInputFileParametersFormatException
     * An Exception returned when a variable with an invalid format is provided
     *
     *
     * @package
     * @subpackage
     * @author     Marc-Olivier Bazin-Maurice
     */
    class UnexpectedMprMergeInputFileParametersFormatException extends \Exception
    {
        private $variableName;
        
        /**
         * \UnexpectedMprMergeInputFileParametersFormatException
         * @param int $code The code of the \Exception
         * @param \Exception $previous A child exception if applicable
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \UnexpectedMprMergeInputFileParametersFormatException
         */
        public function __construct(int $code = 0, \Exception $previous = null)
        {
            $message = "The provided input file parameters for mprmerge.exe are of an unexpected format.";
            parent::__construct($message, $code, $previous);
        }
        
        /**
         * Returns a string representing the exception
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string A string representing the exception
         */
        public function __toString() : string
        {
            return __CLASS__ . ": [{$this->getCode()}]: {$this->getMessage()}\n";
        }
    }
?>