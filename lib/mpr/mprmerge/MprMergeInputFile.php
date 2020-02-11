<?php 
	require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/mpr/mprmerge/UnexpectedVariableFormatException.php";
	require_once $_SERVER["DOCUMENT_ROOT"] . "/Planificateur/lib/mpr/mprmerge/UnexpectedMprMergeInputfileParametersFormatException.php";
	
	class MprMergeInputFile implements \JsonSerializable
    {
        private $filename;
        private $XOffset;
        private $YOffset;
        private $ZOffset;
        private $XScale;
        private $YScale;
        private $ZScale;
        private $XAngle;
        private $YAngle;
        private $ZAngle;
        
        /**
         * Creates a new MprMergeInputFile.
         *
         * @param string $fn The filename of the input file
         * @param string $XO The offset to apply in X
         * @param string $YO The offset to apply in Y
         * @param string $ZO The offset to apply in Z
         * @param string $XO The scale to apply in X
         * @param string $YO The scale to apply in Y
         * @param string $ZO The scale to apply in Z
         * @param string $XA The angle to apply in X
         * @param string $YA The angle to apply in Y
         * @param string $ZA The angle to apply in Z
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprVariable
         */ 
        public function __construct(string $fn, string $XO = "0", string $YO = "0", string $ZO = "0", string $XS = "1", 
            string $YS = "1", string $ZS = "1", string $XA = "0", string $YA = "0", string $ZA = "0")
        {
            $this->setFilename($fn)
            ->setXOffset($XO)
            ->setYOffset($YO)
            ->setZOffset($ZO)
            ->setXScale($XS)
            ->setYScale($YS)
            ->setZScale($ZS)
            ->setXAngle($XA)
            ->setYAngle($YA)
            ->setZAngle($ZA);
        }
        
        /**
         * Creates a new MprMergeInputFile from an object.
         *
         * @param \stdClass $object An object with the following property: filename, and possibly the following proprties: 
         *                          XOffset, YOffset, ZOffset, XScale, YScale, ZScale, XAngle, YAngle and ZAngle.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprVariable
         */ 
        public static function fromObject(\stdClass $object) : \MprMergeInputFile
        {
            return new self(
                $object->filename, 
                $object->XOffset ?? "0", 
                $object->YOffset ?? "0", 
                $object->ZOffset ?? "0", 
                $object->XScale ?? "1", 
                $object->YScale ?? "1", 
                $object->ZScale ?? "1", 
                $object->XAngle ?? "0", 
                $object->YAngle ?? "0", 
                $object->ZAngle ?? "0"
            );
        }
        
        /**
         * Returns the filename of this MprMergeInputFile
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The filename of the MprMergeInputFile.
         */
        public function getFilename() : string
        {
            return $this->filename;
        }
        
        /**
         * Returns the offset in X of this MprMergeInputFile
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The offset in X of the MprMergeInputFile.
         */
        public function getXOffset() : string
        {
            return $this->XOffset;
        }
        
        /**
         * Returns the offset in Y of this MprMergeInputFile
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The offset in Y of the MprMergeInputFile.
         */
        public function getYOffset() : string
        {
            return $this->YOffset;
        }
        
        /**
         * Returns the offset in Z of this MprMergeInputFile
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The offset in Z of the MprMergeInputFile.
         */
        public function getZOffset() : string
        {
            return $this->ZOffset;
        }
        
        /**
         * Returns the scale in X of this MprMergeInputFile
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The scale in X of the MprMergeInputFile.
         */
        public function getXScale() : string
        {
            return $this->XScale;
        }
        
        /**
         * Returns the scale in Y of this MprMergeInputFile
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The scale in Y of the MprMergeInputFile.
         */
        public function getYScale() : string
        {
            return $this->YScale;
        }
        
        /**
         * Returns the scale in Z of this MprMergeInputFile
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The scale in Z of the MprMergeInputFile.
         */
        public function getZScale() : string
        {
            return $this->ZScale;
        }
        
        /**
         * Returns the angle in X of this MprMergeInputFile
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The angle in X of the MprMergeInputFile.
         */
        public function getXAngle() : string
        {
            return $this->XAngle;
        }
        
        /**
         * Returns the angle in Y of this MprMergeInputFile
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The angle in Y of the MprMergeInputFile.
         */
        public function getYAngle() : string
        {
            return $this->YAngle;
        }
        
        /**
         * Returns the angle in Z of this MprMergeInputFile
         *
         * @param
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The angle in Z of the MprMergeInputFile.
         */
        public function getZAngle() : string
        {
            return $this->ZAngle;
        }
        
        /**
         * Sets the filename of this MprMergeInputFile
         *
         * @param string $filename The new filename
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprMergeInputFile This MprMergeInputFile.
         */
        public function setFilename($filename) : \MprMergeInputFile
        {
            $this->filename = $filename;
            return $this;
        }
        
        /**
         * Sets the offset in X of this MprMergeInputFile
         *
         * @param string $XOffset The new offset in X
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprMergeInputFile This MprMergeInputFile.
         */
        public function setXOffset($XOffset) : \MprMergeInputFile
        {
            $this->XOffset = $XOffset;
            return $this;
        }
        
        /**
         * Sets the offset in Y of this MprMergeInputFile
         *
         * @param string $YOffset The new offset in Y
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprMergeInputFile This MprMergeInputFile.
         */
        public function setYOffset($YOffset) : \MprMergeInputFile
        {
            $this->YOffset = $YOffset;
            return $this;
        }
        
        /**
         * Sets the offset in Z of this MprMergeInputFile
         *
         * @param string $ZOffset The new offset in Z
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprMergeInputFile This MprMergeInputFile.
         */
        public function setZOffset($ZOffset) : \MprMergeInputFile
        {
            $this->ZOffset = $ZOffset;
            return $this;
        }
        
        /**
         * Sets the scale in X of this MprMergeInputFile
         *
         * @param string $XScale The new scale in X
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprMergeInputFile This MprMergeInputFile.
         */
        public function setXScale($XScale) : \MprMergeInputFile
        {
            $this->XScale = $XScale;
            return $this;
        }
        
        /**
         * Sets the scale in Y of this MprMergeInputFile
         *
         * @param string $YScale The new scale in Y
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprMergeInputFile This MprMergeInputFile.
         */
        public function setYScale($YScale) : \MprMergeInputFile
        {
            $this->YScale = $YScale;
            return $this;
        }
        
        /**
         * Sets the scale in Z of this MprMergeInputFile
         *
         * @param string $ZScale The new scale in Z
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprMergeInputFile This MprMergeInputFile.
         */
        public function setZScale($ZScale) : \MprMergeInputFile
        {
            $this->ZScale = $ZScale;
            return $this;
        }
        
        /**
         * Sets the angle in X of this MprMergeInputFile
         *
         * @param string $XAngle The new angle in X
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprMergeInputFile This MprMergeInputFile.
         */
        public function setXAngle($XAngle) : \MprMergeInputFile
        {
            $this->XAngle = $XAngle;
            return $this;
        }
        
        /**
         * Sets the angle in Y of this MprMergeInputFile
         *
         * @param string $YAngle The new angle in Y
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprMergeInputFile This MprMergeInputFile.
         */
        public function setYAngle($YAngle) : \MprMergeInputFile
        {
            $this->YAngle = $YAngle;
            return $this;
        }
        
        /**
         * Sets the angle in Z of this MprMergeInputFile
         *
         * @param string $ZAngle The new angle in Z
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprMergeInputFile This MprMergeInputFile.
         */
        public function setZAngle($ZAngle) : \MprMergeInputFile
        {
            $this->ZAngle = $ZAngle;
            return $this;
        }
        
        public function getMergerFileRepresentation() : string
        {
            return "FileName={$this->getFilename()}\r\n" . 
            "XOffset={$this->getXOffset()}\r\n" . 
            "YOffset={$this->getYOffset()}\r\n" . 
            "ZOffset={$this->getZOffset()}\r\n" . 
            "XScale={$this->getXScale()}\r\n" . 
            "YScale={$this->getYScale()}\r\n" . 
            "ZScale={$this->getZScale()}\r\n" . 
            "XAngle={$this->getXAngle()}\r\n" . 
            "YAngle={$this->getYAngle()}\r\n" . 
            "ZAngle={$this->getZAngle()}\r\n";
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