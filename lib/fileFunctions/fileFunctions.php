<?php 
namespace FileFunctions{
    
    class InvalidFileNameException extends \Exception
    {
        private $fileName;
        
        /**
         * \MprExpression\UndefinedVariableException constructor
         * @param string $fileName The name of the file that triggered the \Exception
         * @param int $code The code of the \Exception
         * @param \Exception $previous A child exception if applicable
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \FileFunctions\InvalidFileNameException
         */
        public function __construct(string $fileName, int $code = 0, \Exception $previous = null)
        {
            $message = "Filename \"{$variableName}\" is not valid.";
            parent::__construct($message, $code, $previous);
            $this->fileName = $fileName;
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
        
        /**
         * Returns the name of the undefined variable
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The name of the undefined variable
         */
        public function getFileName() : string
        {
            return $this->fileName;
        }
    }
    
    class PathSanitizer implements \JsonSerializable
    {
        private $filename;
        private $extension;
        private $directory;
        private $type;
        private $drive;
        private $server;
        
        public const PathDelimiters = array(
            "UNIX" => "/", 
            "WINDOWS" => "\\"
        );
        
        /**
         * Creates a new PathSanitizer
         *
         * @return \FileFunctions\PathSanitizer This PathSanitizer
         */
        private function __construct()
        {
        }
        
        /**
         * Parses a path
         *
         * @param string $path The raw path
         * @param ?string $pathDelimiter The path delimiter to use. If empty, the string is treates as a filename
         * @param bool $filenameOnly (optional) If true, the entire string is treated as a filename
         *
         * @return \FileFunctions\PathSanitizer This PathSanitizer (for method chaining)
         */
        private function parse(string $path, ?string $pathDelimiter = null, 
            ?bool $allowSlashesInFilename = false) : \FileFunctions\PathSanitizer
        {
            $matches = array();
            
            $regExpPathDelimiters = array();
            if($pathDelimiter === "")
            {
                if(!$allowSlashesInFilename)
                {
                   $path = preg_replace("/[" . preg_quote("/\\", "/") . "]/", "", $path);
                }
                
                preg_match("/\A(?<filename>.*?)(?=(?:\.(?<extension>[^\.]+))?\z)/", $path, $matches);
                $this->type = "filename";
                $this->directory = array();
                $this->filename = $matches["filename"] ?? null;
                $this->extension = $matches["extension"] ?? null;
            }
            else 
            {
                // Determine what path delimiters to use.
                if($pathDelimiter === null)
                {
                    foreach(self::PathDelimiters as $delimiter)
                    {
                        array_push($regExpPathDelimiters, $delimiter);
                    }
                }
                elseif(is_array($pathDelimiter))
                {
                    $regExpPathDelimiters = $pathDelimiter;
                }
                else
                {
                    array_push($regExpPathDelimiters, $pathDelimiter);
                }
                $del = preg_quote(implode("", $regExpPathDelimiters), "/");
                
                
                preg_match("/\A(?<remains>.*?)(?=(?:\.(?<extension>[^{$del}\.]+))?\z)/", $path, $matches);
                $this->extension = $matches["extension"] ?? null;
                
                $matches["remains"] = $matches["remains"] ?? null;
                preg_match("/\A(?<remains>.*?)(?=(?:(?<filename>[^{$del}]+))?\z)/", $matches["remains"], $matches);
                $this->filename = $matches["filename"] ?? null;
                
                $matches["remains"] = $matches["remains"] ?? null;
                preg_match("/\A(?<pathStart>\/|\\\\{2}.+?\\\\|[A-Za-z]:\\\\)(?<remains>.*)\z/", $matches["remains"], $matches);
                if(!isset($matches["pathStart"]))
                {
                    /* Cannot assume path type. */
                }
                elseif($matches["pathStart"] === "/")
                {
                    $this->type = "UNIX";
                }
                elseif(preg_match("/\\\\{2}.+?\\\\/", $matches["pathStart"]))
                {
                    $this->type = "UNC";
                    $this->server = mb_substr($matches["pathStart"], 2, mb_strlen($matches["pathStart"]) - 3, "utf-8");
                }
                elseif(preg_match("/[A-Za-z]:\\\\/", $matches["pathStart"]))
                {
                    $this->type = "WINDOWS";
                    $this->drive = mb_substr($matches["pathStart"], 0, 1, "utf-8");
                }
                
                $matches["remains"] = $matches["remains"] ?? null;
                preg_match_all("/(?<directory>[^{$del}]+)(?=[{$del}]|\z)/", $matches["remains"], $matches);
                $this->directory = $matches["directory"] ?? null;
            }
            
            return $this;
        }
        
        
        /**
         * Removes any character that is not alphanumeric, an underscore or an hyphen. Replaces spaces with underscores
         *
         * @return \FileFunctions\PathSanitizer This PathSanitizer (for method chaining)
         */
        private function toFullyPortable() : \FileFunctions\PathSanitizer
        {
            for($i = 0; $i < count($this->directory); $i++)
            {
                $this->directory[$i] = preg_replace("/[^\w\-]/", "", str_replace(" ", "_", $this->directory[$i]));
            }
            
            $this->filename = preg_replace("/[^\w\-]/", "", str_replace(" ", "_", $this->filename));
            $this->extension = preg_replace("/[^\w\-]/", "", str_replace(" ", "_", $this->extension));
            
            return $this;
        }
        
        
        /**
         * Transliterates a path (removes diacritics)
         *
         * @return \FileFunctions\PathSanitizer This PathSanitizer (for method chaining)
         */
        private function transliterate() : \FileFunctions\PathSanitizer
        {
            for($i = 0; $i < count($this->directory); $i++)
            {
                $this->directory[$i] = \Transliterator::create('Any-Latin; Latin-ASCII', \Transliterator::FORWARD)
                    ->transliterate($this->directory[$i]);
            }
            $this->filename = \Transliterator::create('Any-Latin; Latin-ASCII', \Transliterator::FORWARD)
                ->transliterate($this->filename);
            $this->extension = \Transliterator::create('Any-Latin; Latin-ASCII', \Transliterator::FORWARD)
                ->transliterate($this->extension);
            
            return $this;
        }
        
        /**
         * Simplifies a path
         *
         * @return \FileFunctions\PathSanitizer This PathSanitizer (for method chaining)
         */
        private function simplify() : \FileFunctions\PathSanitizer
        {
            $i = 0;
            while($i < count($this->directory))
            {
                if($this->directory[$i] === "." || $this->directory[$i] === "")
                {
                    unset($this->directory[$i]);
                    $this->directory = array_values($this->directory);
                }
                elseif($this->directory[$i] === ".." && $i > 0)
                {
                    unset($this->directory[$i - 1]);
                    unset($this->directory[$i]);
                    $this->directory = array_values($this->directory);
                    $i--;
                }
                else 
                {
                    $i++;
                }
            }
            
            return $this;
        }
        
        /**
         * Sanitizes a path
         * 
         * @param string $path A path
         * @param ?array $options (optional) An array of options. If null, all simplification options are enabled (default)
         * 
         * @return string A sanitized version of the path
         */
        public static function sanitize(string $path, ?array $options = null) : string
        {
            if($options === null)
            {
                $options = array(
                    "allowSlashesInFilename" => false,
                    "transliterate" => true,
                    "fullyPortable" => true,
                    "simplify" => true,
                    "inputPathDelimiter" => null,
                    "outputPathDelimiter" => null,
                    "outputPathType" => null
                );
            }
            
            $pathSanitizer = (new self())
                ->parse($path, $options["inputPathDelimiter"] ?? null, $options["allowSlashesInFilename"] ?? false);
            
            if($options["transliterate"] ?? false)
            {
                $pathSanitizer->transliterate();
            }
            
            if($options["fullyPortable"] ?? false)
            {
                $pathSanitizer->toFullyPortable();
            }
            
            if($options["simplify"] ?? false)
            {
                $pathSanitizer->simplify();
            }
            
            return $pathSanitizer->encode($options["outputPathDelimiter"] ?? null, $options["outputPathType"] ?? null);
        }
        
        /**
         * Return the encoded path
         * 
         * @param string $pathDelimiter The delimiter to use in the path (if empty, it is determined automatically)
         * @param string $pathType The path can either be UNIX, WINDOWS or UNC style.
         *
         * @return string The path
         */
        public function encode(?string $pathDelimiter = null, ?string $pathType = null) : string
        {
            $path = "";
            
            if($pathDelimiter !== null && $pathDelimiter !== "")
            {    
                /* Do nothing. */
            }
            elseif($this->type !== null)
            {
                switch ($this->type)
                {
                    case "UNIX":
                        $pathDelimiter = "/";
                        break;
                    case "WINDOWS":
                        $pathDelimiter = "\\";
                        break;
                    case "UNC":
                        $pathDelimiter = "\\";
                        break;
                    default:
                        $pathDelimiter = "/";
                        break;
                }
            }
            else
            {
                $pathDelimiter = "/";
            }
            
            if($pathType !== null && $pathType !== "")
            {
                /* Do nothing. */
            }
            elseif($this->type !== null)
            {
                $pathType = $this->type;
            }
            else
            {
                $pathType = "UNIX";
            }
            
            switch ($pathType)
            {
                case "UNIX":
                    $path .= $pathDelimiter;
                    break;
                case "WINDOWS":
                    $path .= ($this->drive === null ? "" : "{$this->drive}:\\");
                    break;
                case "UNC":
                    $path = ($this->server === null ? "" : "\\\\{$this->server}\\");
                    break;
                case "filename":
                    $path .= "";
                    break;
                default:
                    $path .= $pathDelimiter;
                    break;
            }
            
            foreach($this->directory as $pathPart)
            {
                $path .= $pathPart . $pathDelimiter;
            }
            
            if($this->filename !== null)
            {
                $path .= $this->filename;
            }
            
            if($this->extension !== null && $this->extension !== "")
            {
                $path .= "." . $this->extension;
            }
            
            return $path;
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
    };
    
    class DownloadLinkGenerator
    {
        /**
         * Creates a new DownloadLinkGenerator
         *
         * @return \FileFunctions\DownloadLinkGenerator This DownloadLinkGenerator
         */
        private function __construct(){}
        
        /**
         * Creates a download link from a specific filepath
         *
         * @param string $filepath The path to the file to download
         *
         * @throws \Exception If the file doesn't exist or is located outside of the server's root. 
         * @author Marc-Olivier Bazin-Maurice
         * @return string A download link
         */
        public static function fromFilePath(string $filepath) :string
        {
            if(file_exists($filepath))
            {
                $pattern = "/\A" . preg_quote($_SERVER["DOCUMENT_ROOT"], "/") . "(.*)\z/";
                $count = null;
                $forwardSlashesDelimitedFilepath = str_replace(DIRECTORY_SEPARATOR, "/", realpath($filepath));
                $downloadLinkFromRoot = preg_replace($pattern, "$1", $forwardSlashesDelimitedFilepath, 1, $count);
                if($count > 0)
                {
                    return $downloadLinkFromRoot;
                }
                else
                {
                    throw new \Exception("The downloadable file was created outside of the server's root.");
                }
            }
            else 
            {
                throw new \Exception("File \"" . $filepath . "\" doesn't exist.");
            }
        }
    };
    
    class TemporaryFolder
    {
        private $path;
        
        /**
         * Creates a new TemporaryFolder
         * @param string $path The path of the temporary folder
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \FileFunctions\TemporaryFolder This TemporaryFolder
         */
        public function __construct(string $path)
        {
            $this->setPath($path);
        }
        
        /**
         * Returns the path of the temporary folder.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The path of the temporary folder.
         */
        public function getPath() : string
        {
            return $this->path;
        }
        
        /**
         * Sets the path of the temporary folder.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \FileFunctions\TemporaryFolder This \FileFunctions\TemporaryFolder.
         */
        public function setPath(string $path) : \FileFunctions\TemporaryFolder
        {
            $this->path = $path;
            return $this;
        }
        
        /**
         * Cleans the temporary folder.
         * @param int $lifeExpectancy The number of seconds after which a file should be eligible for deletion.
         * @param bool $deleteFolders (optional) If false, only files will be deleted. 
         *                                       If true, directories won't avoid deletion.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return
         */
        public function clean(int $lifeExpectancy, bool $deleteFolders = false) : void
        {
            if(!file_exists($this->getPath()) || !is_dir($this->getPath()))
            {
                mkdir($this->getPath());
            }
            else
            {
                /* @var $fileInfo \DirectoryIterator */
                foreach(new \DirectoryIterator($this->getPath()) as $fileInfo)
                {
                    if(!$fileInfo->isDot())
                    {
                        if (time() - $fileInfo->getCTime() >= $lifeExpectancy)
                        {
                            if($fileInfo->isFile())
                            {
                                unlink($fileInfo->getRealPath());
                            }
                            elseif($deleteFolders)
                            {
                                rmdir($fileInfo->getRealPath());
                            }
                        }
                    }
                }
            }
        }
    }
};
?>