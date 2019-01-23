<?php
	/**
     * \UnexpectedVariableFormatException
     * An Exception returned when a variable with an invalid format is provided
     *
     *
     * @package
     * @subpackage
     * @author     Marc-Olivier Bazin-Maurice
     */
    class UnexpectedVariableFormatException extends \Exception
    {
        private $variableName;
        
        /**
         * \UnexpectedVariableFormatException
         * @param int $code The code of the \Exception
         * @param \Exception $previous A child exception if applicable
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \UnexpectedVariableFormatException
         */
        public function __construct(int $code = 0, \Exception $previous = null)
        {
            $message = "A variable with a type other than \MprVariable was found.";
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