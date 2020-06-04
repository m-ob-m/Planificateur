<?php
abstract class Parameter implements \JsonSerializable
{
    protected $_key;
    protected $_value; /* Accessors to this property should be defined in child classes. */
    
    /**
     * Parameter constructor
     *
     * @param string $key The key of the parameter
     * @param mixed $value The value of the parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Parameter
     */
    public function __construct(string $key)
    {
        $this->setKey($key);
    }
    
    /**
     * Get the key of the Parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The parameter's key
     */
    public function getKey() : string
    {
        return $this->_key;
    }
    
    /**
     * Set the key of the Parameter
     *
     * @param string $key The new key of the Parameter
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return \Parameter This Parameter (for method chaining)
     */
    public function setKey(string $key) : Parameter
    {
        $this->_key = $key;
        return $this;
    }
    
    /**
     * Get a JSON compatible representation of this object.
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return [] This object in a JSON compatible format
     */
    public function jsonSerialize() : ?array
    {
        return get_object_vars($this);
    }
};
?>