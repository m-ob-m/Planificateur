<?php
    /**
     * \MprVariable
     * Represents a variable in the variables block of a mpr file
     *
     *
     * @package
     * @subpackage
     * @author     Marc-Olivier Bazin-Maurice
     */
    class MprVariable implements \JsonSerializable
    {
        private $key;
        private $value;
        private $description;
        
        /**
         * Creates a new MprVariable.
         *
         * @param string $key The key of the MprVariable
         * @param ?string $value The value of the MprVariable
         * @param ?string $description The description of the MprVariable
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprVariable
         */
        public function __construct(string $key = null, ?string $value = null, ?string $description = null)
        {
            $this->setKey($key)->setValue($value)->setDescription($description);
        }
        
        /**
         * Creates a new MprVariable from an object.
         *
         * @param \stdClass $object An object with the following property: key, and possibly the following properties:
         *                          value and description.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprVariable
         */
        public static function fromObject(\stdClass $object)
        {
            return new self($object->key, $object->value ?? "", $object->description ?? "");
        }
        
        /**
         * Returns the key of this MprVariable.
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The key of the MprVariable.
         */
        public function getKey() : string
        {
            return $this->key;
        }
        
        /**
         * Returns the value of this MprVariable.
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return ?string The value of the MprVariable.
         */
        public function getValue() : ?string
        {
            return $this->value;
        }
        
        /**
         * Returns the description of this MprVariable.
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return ?string The key of the MprVariable.
         */
        public function getDescription() : ?string
        {
            return $this->description;
        }
        
        /**
         * Sets the key of the MprVariable.
         *
         * @param string $key The new key of this MprVariable
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprVariable This MprVariable
         */
        public function setKey(string $key) : \MprVariable
        {
            $this->key = $key;
            return $this;
        }
        
        /**
         * Sets the value of the MprVariable.
         *
         * @param ?string $value The new value of this MprVariable
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprVariable This MprVariable
         */
        public function setValue(?string $value) : \MprVariable
        {
            $this->value = $value;
            return $this;
        }
        
        /**
         * Sets the description of the MprVariable.
         *
         * @param ?string $description The new description of this MprVariable
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprVariable This MprVariable
         */
        public function setDescription(?string $description) : \MprVariable
        {
            $this->description = $description;
            return $this;
        }
        
        /**
         * Returns the mpr representation of the variable
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The mpr representation of the variable
         */
        public function getMprRepresentation() : string
        {
            return "{$this->getKey()}=\"{$this->getValue()}\"\r\n" . 
                "KM=\"{$this->getDescription()}\"\r\n";
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