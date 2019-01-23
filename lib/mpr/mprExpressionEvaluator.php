<?php
namespace MprExpression
{
    /**
     * \MprExpression\UndefinedVariableException
     * An Exception returned when a variable is undefined
     *
     *
     * @package
     * @subpackage
     * @author     Marc-Olivier Bazin-Maurice
     */
    class UndefinedVariableException extends \Exception
    {
        private $variableName;
        
        /**
         * \MprExpression\UndefinedVariableException constructor
         * @param string $variableName The name of the variable that triggered the exception
         * @param int $code The code of the \Exception
         * @param \Exception $previous A children exception if applicable
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprExpression\UndefinedVariableException
         */
        public function __construct(string $variableName, int $code = 0, \Exception $previous = null) 
        {
            $message = "Variable \"{$variableName}\" is undefined.";
            parent::__construct($message, $code, $previous);
            $this->variableName = $variableName;
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
        public function getVariableName() : string
        {
            return $this->variableName;
        }
    }
    
    /**
     * \MprExpression\BadExpressionException
     * An Exception returned when the expression to evaluate contains an error
     *
     *
     * @package
     * @subpackage
     * @author     Marc-Olivier Bazin-Maurice
     */
    class BadExpressionException extends \Exception
    {
        private $mprExpression;
        private $phpExpression;
        
        /**
         * \MprExpression\BadExpressionException constructor
         * @param string $variableName The name of tthe variable that triggered the exception
         * @param int $code The code of the \Exception
         * @param \Exception $previous A children exception if applicable
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprExpression\BadExpressionException
         */
        public function __construct(string $mprExpression, string $phpExpression, int $code = 0, \Exception $previous = null)
        {
            $message = "Mpr-style expression \"{$mprExpression}\", converted to PHP-style expression \"{$phpExpression}\", 
                cannot be evaluated.";
            parent::__construct($message, $code, $previous);
            $this->mprExpression = $mprExpression;
            $this->phpExpression = $phpExpression;
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
         * Returns the mpr-style expression that caused the error
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The mpr-style that caused the error
         */
        public function getMprExpression() : string
        {
            return $this->mprExpression;
        }
        
        /**
         * Returns the PHP-style expression that caused the error
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return string The PHP-style that caused the error
         */
        public function getPHPExpression() : string
        {
            return $this->phpExpression;
        }
    }
    
    /**
     * \MprExpression\Evaluator
     * Evaluates mpr-style expressions
     *
     *
     * @package    
     * @subpackage 
     * @author     Marc-Olivier Bazin-Maurice
     */
    class Evaluator
    {
        private const operators_priority = array(
            "constant" => 1,
            "variable" => 1,
            "negate" => 2,
            "function" => 2,
            "()" => 2,
            "NOT" => 3,
            "^" => 4,
            "*" => 5,
            "/" => 5,
            "+" => 6,
            "-" => 6,
            ">" => 7,
            "<" => 7,
            "=" => 7,
            ">=" => 7,
            "<=" => 7,
            "<>" => 7,
            "AND" => 8,
            "OR" => 9,
            "" => 10
        );
        
        /**
         * \MprExpression\Evaluator constructor
         * 
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return \MprExpression\Evaluator
         */ 
        private function __construct()
        {
            
        }
        
        /**
         * Evaluates a .mpr file type mathematical expression
         *
         * @param string $expressionToEvaluate The expression that must get evaluated
         * @param string $name The name of the variable corresponding to the expression
         * @param array[string] $parameters An array containing all the parameters required to evaluate the expression 
         *                                  $expressionToEvaluate
         * @param array[string] $callStack An array containing the names of all the variables that are in the process of
         *                                 being evaluated. This argument must be passed in order to prevent circular references.
         *
         * @throws \Exception If a required variable is missing or has circular reference.
         * @author Marc-Olivier Bazin-Maurice
         * @return string The solution of the expression $expressionToEvaluate (all variables on which this expression depend 
         *                must be provided in $parametersArray, otherwise an exception will occur).
         */ 
        public static function evaluate(?string $mprExpression, string $name = null, array $parametersArray = array(), 
            array $callStack = array()) : string
        {
            try
            {
                $instance = new self;
                $expressionArray = $instance->decode($mprExpression, $parametersArray, $callStack);
                $phpExpression = $instance->buildExpressionString($expressionArray["expression"]);
                
                //DEBUG STATEMENTS
                //echo "<pre>{$phpExpression}\n</pre>";
                
                try
                {
                    $solution = eval("
                        \$val = {$phpExpression}; 
                        if(\$val === false)
                        {
                            return \"0\";
                        }
                        elseif(\$val === true)
                        {
                            return \"1\";
                        }
                        else
                        {
                            return \"{\$val}\";
                        }
                    ");
                }
                catch(\ParseError $e)
                {
                    throw new \MprExpression\BadExpressionException($mprExpression, $phpExpression);
                }
                return $solution;
            }
            catch(\MprExpression\UndefinedVariableException $e)
            {
                throw $e; // This is an identified Exception, let the caller deal with it.
            }
            catch(\Exception $e)
            {
                //This is an unexpected Exception.
                $message = "Cannot evaluate expression \"{$name} = {$expressionToEvaluate}\": {$e->getMessage()}";
                throw new \Exception($message);
            }
        }
        
        /**
         * Builds a PHP-style mathematical expression out of the expression array
         *
         * @param byref array[string] $expression mathematical expression array that must be decoded as a php expression
         *
         * @throws \Exception If an unknown mathematical operation constituent is found in the array
         * @author Marc-Olivier Bazin-Maurice
         * @return string The php-style mathematical expression equivalent to the provided expression array.
         */ 
        private function buildExpressionString(?array &$expression) : ?string
        {
            $value = "";
            if($expression["buffer"] === null)
            {
                $value .= 0;
            }
            elseif(isset($expression["buffer"]["constant"]))
            {
                $value .= "({$expression["buffer"]["constant"]["value"]})";
            }
            elseif(isset($expression["buffer"]["variable"]))
            {
                $value .= "({$expression["buffer"]["variable"]["value"]})";
            }
            elseif(isset($expression["buffer"]["operator"]))
            {
                if($expression["buffer"]["operator"]["identity"] === "()")
                {
                    $value .= "(" . $this->buildExpressionString($expression["buffer"]["operator"]["argument"]) . ")";
                }
                elseif($expression["buffer"]["operator"]["identity"] === "^")
                {
                    $value .= "pow(";
                    $value .= $this->buildExpressionString($expression["buffer"]["operator"]["operand1"]);
                    $value .= ", ";
                    $value .= $this->buildExpressionString($expression["buffer"]["operator"]["operand2"]);
                    $value .= ")";
                }
                elseif($expression["buffer"]["operator"]["identity"] === "NOT")
                {
                    $value .= "!" . $this->buildExpressionString($expression["buffer"]["operator"]["operand1"]);
                }
                elseif($expression["buffer"]["operator"]["identity"] === "negate")
                {
                    $value .= "(-" . $this->buildExpressionString($expression["buffer"]["operator"]["operand1"]) . ")";
                }
                else
                {
                    $symbol = null;
                    switch ($expression["buffer"]["operator"]["identity"])
                    {
                        case "=":
                            $symbol = "==";
                            break;
                        case "AND":
                            $symbol = "&&";
                            break;
                        case "OR":
                            $symbol = "||";
                            break;
                        default:
                            $symbol = $expression["buffer"]["operator"]["identity"];
                            break;
                    }
                    
                    $value .= $this->buildExpressionString($expression["buffer"]["operator"]["operand1"]);
                    $value .= $symbol;
                    $value .= $this->buildExpressionString($expression["buffer"]["operator"]["operand2"]);
                }
            }
            elseif(isset($expression["buffer"]["function"]))
            {
                if($expression["buffer"]["function"]["name"] === "if")
                {
                    $condition = $this->buildExpressionString($expression["buffer"]["function"]["condition"]);
                    $then = $this->buildExpressionString($expression["buffer"]["function"]["then"]);
                    $else = $this->buildExpressionString($expression["buffer"]["function"]["else"]);
                    $value .= "(({$condition})?({$then}):({$else}))";
                }
                else 
                {
                    $functionName = $expression["buffer"]["function"]["name"];
                    $functionArgument = $this->buildExpressionString($expression["buffer"]["function"]["argument"]);
                    $value .= "(new \\MprExpression\\SolverFunctions)->{$functionName}({$functionArgument})";
                }
            }
            else
            {
                throw new \Exception("Unknown operation constituent.");
            }
            
            return $value;
        }
        
        /**
         * Decodes a mpr-style mathematical operation and stores it as a mathematical expression array
         *
         * @param string $expressionToEvaluate The mpr-style mathematical expression to decode
         * @param array $parametersArray A [key => value] array of parameters required to solve the mathematical expression
         * @param array $callStack An array containing a stack of the variables that are currently getting evaluated. If a 
         *                         variable is found twice in this array, there is a circular reference.
         *
         * @throws \Exception If the mpr-style expression is erroneous.
         * @author Marc-Olivier Bazin-Maurice
         * @return array A mathematical expression array that represents the mpr-style expression. The mathematical expression 
         *               array can then be converted to a new mathematical expression of the desired style.
         */ 
        private function decode(?string $expressionToEvaluate, array $parametersArray, array $callStack) : ?array
        {
            $elementsArray = array();
            $mask = "/(?=[0-9]|\.[0-9])[0-9]*\.?[0-9]*|[a-zA-Z_][\w]*|[+\-*\/^()]|<=|>=|<>|(?<!<|>)=|<(?!=|>)|(?<!<)>(?!=)/";
            preg_match_all($mask, $expressionToEvaluate, $elementsArray);
            if(implode("", $elementsArray[0]) !== preg_replace("/\s/", "", $expressionToEvaluate))
            {
                throw new \Exception("Expression contains an error.");
            }
            
            $expression = array(
                "expression" => array(
                    "buffer" => null, 
                    "parent" => null
                )
            );
            $expression["expression"]["parent"] = &$expression;
            $pointer = &$expression["expression"]; //pointer to the current element
            
            $skip = false;
            $functionsFilter = "/^SIN$|^COS$|^TAN$|^ARCSIN$|^ARCCOS$|^ARCTAN$|^EXP$|^LN$|^SQRT$|^MOD$|^PREC$|^ABS$/";
            foreach($elementsArray[0] as $index => $element)
            {
                if($skip === true)
                {
                    $skip = false;
                    continue;
                }
                elseif(preg_match("/^IF$/", $element))
                {
                    $pointer = &$this->openIf($pointer);
                }
                elseif(preg_match("/^THEN$/", $element))
                {
                    $pointer = &$this->openThen($pointer);
                }
                elseif(preg_match("/^ELSE$/", $element))
                {
                    $pointer = &$this->openElse($pointer);
                }
                elseif(preg_match($functionsFilter, $element) && preg_match("/^\($/", $elementsArray[0][$index + 1]))
                {
                    $pointer = &$this->openFunctionCall(strtolower($element), $pointer, $skip);
                }
                elseif(preg_match("/^\($/", $element))
                {
                    $pointer = &$this->openBrackets($pointer);
                }
                elseif(preg_match("/^\)$/", $element))
                {
                    $pointer = &$this->closeBrackets($pointer);
                }
                elseif(preg_match("/^NOT$/", $element))
                {
                    $pointer = &$this->open1OperandOperator($pointer, $element);
                }
                elseif(preg_match("/^<=$|^>=$|^<>$|^=$|^<$|^>$|^\^$|^\*$|^\/$|^\+$|^AND$|^OR$/", $element))
                {
                    $pointer = &$this->open2OperandsOperator($pointer, $element);
                }
                elseif(preg_match("/^-$/", $element))
                {
                    $filter = "/^<=$|^>=$|^<>$|^=$|^<$|^>$|^\^$|^\*$|^\/$|^\+$|^-$|^AND$|^OR$|^NOT$|^\($|^IF$|^THEN$|^ELSE$/";
                    if($index === 0 || preg_match($filter, $elementsArray[0][$index - 1]))
                    {
                        $pointer = &$this->open1OperandOperator($pointer, "negate");
                    }
                    else
                    {
                        $pointer = &$this->open2OperandsOperator($pointer, $element);
                    }
                }
                elseif(is_numeric($element))
                {
                    $pointer = &$this->createConstant($element, $pointer);
                }
                else
                {
                    $pointer = &$this->createVariable($element, $parametersArray, $pointer, $callStack);
                }
                //DEBUG STATEMENTS
                //echo "<pre>"; print_r($expression);echo "______________</pre>";
                //echo "<pre>"; print_r($pointer);echo "_______________________________</pre>";
            }
            
            $pointer = &$this->checkForUnbalancedParenthesis($pointer);        
            return $expression;
        }
        
        /**
         * Performs a final check to make sure every opened bracket was closed. If this is not the case, the expression 
         *  contains a mistake.
         *
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         *
         * @throws \Exception If an unbalanced bracket is found. There is an error with the expression.
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */ 
        private function & checkForUnbalancedParenthesis(array &$pointer) : array
        {
            while(!isset($pointer["expression"]))
            {
                if(isset($pointer["function"]))
                {
                    if($pointer["function"]["name"] !== "if")
                    {
                        throw new \Exception("Unbalanced parenthesis.");
                    }
                    else
                    {
                        $pointer = &$this->getParent($pointer);
                    }
                }
                elseif(isset($pointer["operator"]))
                {
                    if($pointer["operator"]["identity"] === "()")
                    {
                        throw new \Exception("Unbalanced parenthesis.");
                    }
                    else
                    {
                        $pointer = &$this->getParent($pointer);
                    }
                }
                else
                {
                    $pointer = &$this->getParent($pointer);
                }
            }
            
            return $pointer;
        }
        
        /**
         * Goes up the expression array to find an "if" statement (triggered by "then" and "else" keywords).
         *
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         *
         * @throws \Exception If no "if" statement was found. There is an error with the expression.
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */ 
        private function & findIf(array &$pointer) : array
        {
            do{
                if(isset($pointer["function"]))
                {
                    if($pointer["function"]["name"] === "if")
                    {
                        return $pointer;
                    }
                }
                elseif(isset($pointer["expression"]))
                {
                    throw new \Exception("\"If\" not found.");
                }
                else 
                {
                    $pointer = &$this->getParent($pointer);
                }
            } while(1);
        }
        
        /**
         * Adds an "if" statement to the expression array and steps in the condition
         *
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */ 
        private function & openIf(array &$pointer) : array
        {
            $function = array(
                "function" =>array(
                    "name" => "if",
                    "condition" => array("buffer" => null, "parent" => null),
                    "then" => array("buffer" => null, "parent" => null),
                    "else" => array("buffer" => null, "parent" => null),
                    "current" => null,
                    "parent" => &$pointer
                ),
                "parent" => null
            );
            
            $function["function"]["condition"]["parent"] = &$function["function"];
            $function["function"]["then"]["parent"] = &$function["function"];
            $function["function"]["else"]["parent"] = &$function["function"];
            $function["function"]["parent"] = &$function;
            $function["function"]["current"] = "condition";
            $function["parent"] = &$pointer;
            $pointer["buffer"] = &$function;
            
            $pointer = &$function["function"]["condition"];
            return $pointer;
        }
        
        /**
         * Steps into the "then" portion of the parent "if" statement (triggered by "then" keyword).
         *
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         *
         * @throws \Exception If no "if" statement was found. There is an error with the expression.
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */
        private function & openThen(array &$pointer) : array
        {
            try
            {
                $pointer  = &$this->findIf($pointer);
                $pointer["function"]["current"] = "then";
                $pointer = &$pointer["function"]["then"];
            }
            catch(\Exception $e)
            {
                $message = "A \"THEN\" with no associated \"IF\" was found.";
                throw new \Exception($message);
            }
            
            return $pointer;
        }
        
        /**
         * Steps into the "else" portion of the parent "if" statement (triggered by "else" keyword).
         *
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         *
         * @throws \Exception If no "if" statement was found. There is an error with the expression.
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */
        private function & openElse(array &$pointer) : array
        {
            try
            {
                $pointer  = &$this->findIf($pointer);
                $pointer["function"]["current"] = "else";
                $pointer = &$pointer["function"]["else"];
            }
            catch(\Exception $e)
            {
                $message = "A \"ELSE\" with no associated \"IF\" was found.";
                throw new \Exception($message);
            }
            
            return $pointer;
        }
        
        /**
         * Adds a constant to the current position in the mathematical expression array.
         *
         * @param string $value The value of the constant
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */ 
        private function & createConstant(string $value, array &$pointer) : ?array
        {
            $constant = array(
                "constant" => array(
                    "value" => $value,
                    "parent" => null
                ),
                "parent" => null
            );
            
            $constant["constant"]["parent"] = &$constant;
            $constant["parent"] = &$pointer;
            $pointer["buffer"] = &$constant;
            
            return $pointer;
        }
        
        /** Adds a variable to the current position in the mathematical expression array.
        *
        * @param string $name The name of the variable
        * @param array $parametersArray A [key => value] array of parameters required to solve the mathematical expression
        * @param byref array $pointer A reference to the current node in the mathematical expression array
        * @param array $callStack An array containing a stack of the variables that are currently getting evaluated. 
        *                         If a variable is found twice in this array, there is a circular reference.
        *
        * @throws \Exception If the variable cannot be evaluated. There is an error with the expression.
        * @author Marc-Olivier Bazin-Maurice
        * @return array The reference to the new current node in the expression array
        */ 
        private function & createVariable(string $name, array $parametersArray, array &$pointer, array $callStack) : array
        {
            $variable = array(
                "variable" => array(
                    "name" => $name,
                    "value" => null,
                    "parent" => null
                ),
                "parent" => null
            );
            
            
            if(!isset($parametersArray[$name]))
            {
                throw new \MprExpression\UndefinedVariableException($name);
            }
            elseif(array_search($name, $callStack, true) !== false)
            {
                throw new \Exception("Variable \"{$name}\" contains circular reference.");
            }
            else
            {
                array_push($callStack, $name);
                $value = $parametersArray[$name];
                $variable["variable"]["value"] = $this->evaluate($value, $name, $parametersArray, $callStack);
            }
            
            $variable["variable"]["parent"] = &$variable;
            $variable["parent"] = &$pointer["buffer"];
            $pointer["buffer"] = &$variable;
            
            return $pointer;
        }
        
        /** Creates a single-operand operator such as negation minus or the "not" operator.
         *
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         * @param string $element The identity of the operator that must be created
         *
         * @throws 
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */ 
        private function & open1OperandOperator(array &$pointer, string $element) : array
        {            
            $operator = array(
                "operator" => array(
                    "identity" => $element,
                    "operand1" => array(
                        "buffer" => null,
                        "parent" => null
                    ),
                    "parent" => null
                ),
                "parent" =>null
            );
            $operator["operator"]["parent"] = &$operator;
            $operator["operator"]["operand1"]["parent"] = &$operator["operator"];
            
            $pointer = &$this->stepOutOfOperatorsWithPrecedenceOver($operator, $pointer);
            
            if(isset($pointer["expression"]))
            {
                $operator["parent"] = &$pointer["expression"];
                $pointer["expression"]["buffer"] = &$operator;
            }
            elseif(isset($pointer["operator"]))
            {
                if($pointer["operator"]["identity"] === "()")
                {
                    $operator["parent"] = &$pointer["operator"]["argument"];
                    $pointer["operator"]["argument"]["buffer"] = &$operator;
                }
                else
                {
                    $operator["parent"] = &$pointer["operator"]["operand2"];
                    $pointer["operator"]["operand2"]["buffer"] = &$operator;
                }
            }
            elseif(isset($pointer["function"]))
            {
                if($pointer["function"]["name"] === "if")
                {
                    $operator["parent"] = &$pointer["function"][$pointer["function"]["current"]];
                    $pointer["function"][$pointer["function"]["current"]]["buffer"] = &$operator;
                }
                else
                {
                    $operator["parent"] = &$pointer["function"]["argument"];
                    $pointer["function"]["argument"]["buffer"] = &$operator;
                }
            }
            
            $pointer = &$operator["operator"]["operand1"];
            return $pointer;
        }
        
        /** Creates a bi-operand operator such as arithmetic "plus", "minus", "times", "divided by" and "exponent", 
         *  logical "and" and "or", and comparison operators.
         *
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         * @param string $element The identity of the operator that must be created
         *
         * @throws 
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */ 
        private function & open2OperandsOperator(array &$pointer, string $element) : array
        {            
            $operator = array(
                "operator" => array(
                    "identity" => $element,
                    "operand1" => array(
                        "buffer" => null, 
                        "parent" => null
                    ),
                    "operand2" => array(
                        "buffer" => null, 
                        "parent" => null
                    ),
                    "parent" => null
                ),
                "parent" =>null
            );
            $operator["operator"]["parent"] = &$operator;
            $operator["operator"]["operand1"]["parent"] = &$operator["operator"];
            $operator["operator"]["operand2"]["parent"] = &$operator["operator"];
            
            $pointer = &$this->stepOutOfOperatorsWithPrecedenceOver($operator, $pointer);
            
            if(isset($pointer["expression"]))
            {
                $operator["operator"]["operand1"]["buffer"] = &$pointer["expression"]["buffer"];
                $operator["parent"] = &$pointer["expression"];
                $pointer["expression"]["buffer"] = &$operator;
            }
            elseif(isset($pointer["operator"]))
            {
                if($pointer["operator"]["identity"] === "()")
                {
                    $operator["operator"]["operand1"]["buffer"] = &$pointer["operator"]["argument"]["buffer"];
                    $operator["parent"] = &$pointer["operator"]["argument"];
                    $pointer["operator"]["argument"]["buffer"] = &$operator;
                }
                else 
                {
                    $operator["operator"]["operand1"]["buffer"] = &$pointer["operator"]["operand2"]["buffer"];
                    $operator["parent"] = &$pointer["operator"]["operand2"];
                    $pointer["operator"]["operand2"]["buffer"] = &$operator;
                }
            }
            elseif(isset($pointer["function"]))
            {
                if($pointer["function"]["name"] === "if")
                {
                    $currentNode = $pointer["function"]["current"];
                    $operator["operator"]["operand1"]["buffer"] = &$pointer["function"][$currentNode]["buffer"];
                    $operator["parent"] = &$pointer["function"][$pointer["function"]["current"]];
                    $pointer["function"][$pointer["function"]["current"]]["buffer"] = &$operator;
                }
                else 
                {
                    $operator["operator"]["operand1"]["buffer"] = &$pointer["function"]["argument"]["buffer"];
                    $operator["parent"] = &$pointer["function"]["argument"];
                    $pointer["function"]["argument"]["buffer"] = &$operator;
                }
            }
            
            $operator["operator"]["operand1"]["buffer"]["parent"] = &$operator["operator"]["operand1"];
            $pointer = &$operator["operator"]["operand2"];
            return $pointer;
        }
        
        /** Creates a function call and steps into its argument.
         *
         * @param string $name The name of the function
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         * @param byref bool $skip A boolean that determines if the next element (the opening parenthesis of the function in 
         *                         this case) must be skipped by the decoding loop
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */ 
        private function & openFunctionCall(string $name, array &$pointer, bool &$skip) : array
        {
            $function = array(
                "function" => array(
                    "name" => $name, 
                    "argument" => array(
                        "buffer" => null, 
                        "parent" => null
                    ), 
                    "parent" => null
                ),
                "parent" => null
            );
            $function["function"]["parent"] = &$function;
            $function["function"]["argument"]["parent"] = &$function["function"];
            $function["parent"] = &$pointer;
            $pointer["buffer"] = &$function;
            
            $pointer = &$function["function"]["argument"];
            $skip = true;
            return $pointer;
        }
        
        /** Creates bracket operator and steps into its argument.
         *
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */ 
        private function & openBrackets(array &$pointer) : array
        {
            $operator = array(
                "operator" => array(
                    "identity" => "()",
                    "argument" => array(
                        "buffer" => null,
                        "parent" => null
                    ),
                    "parent" => null
                ),
                "parent" => null
            );
            $operator["operator"]["argument"]["parent"] = &$operator["operator"];
            $operator["operator"]["parent"] = &$operator;
            $operator["parent"] = &$pointer;
            $pointer["buffer"] = &$operator;
            
            $pointer = &$operator["operator"]["argument"];
            return $pointer;
        }
        
        /** Steps out of a bracket operator.
         *
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         *
         * @throws \Exception If there is no opened bracket to close. There is an error with the expression.
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */ 
        private function & closeBrackets(array &$pointer) : array
        {
            $exitLoop = false;
            while(!$exitLoop)
            {
                if($pointer["parent"] === null)
                {
                    throw new \Exception("A \")\" with no associated \"(\" was found.");
                }
                
                if(isset($pointer["function"]))
                {
                    $exitLoop = true;
                }
                
                if(isset($pointer["operator"]))
                {
                    if($pointer["operator"]["identity"] === "()")
                    {
                        $exitLoop = true;
                    }
                }
                
                $pointer = &$this->getParent($pointer);
            }
            
            return $pointer;
        }
        
        /** Steps out of immediately previous operators that have a priority higher than the one of current bi-operand operator 
         *  (this determines operand 1 of current operator). Doesn't step out of functions, brackets and the head of the 
         *  expression.
         *
         * @param byref array $currentOperator The current operator in the mathematical expression array format. 
         *                    It should be initialized with at least its identity.
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */ 
        private function & stepOutOfOperatorsWithPrecedenceOver(array $currentOperator, array &$pointer) : array
        {
            $pointer = &$this->gotoParentOperator($pointer, true);
            
            while ($this->canStepOutOfPreviousOperator($currentOperator, $pointer) && !isset($pointer["expression"]))
            {
                $pointer = &$this->gotoParentOperator($pointer, false);
            }
            return $pointer;
        }
        
        /** Steps out to the parent operator of an operand or to the parent operator of another operator. Stops automatically 
         *  if the head of the expression is reached. Stops at function calls and opened brackets.
         *
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         * @param bool $stopAtCurrentIfAlreadyAnOperator A boolean value that determines if the stepping out should be cancelled 
         *                                               if $pointer already points to an operator. Setting this parameter to 
         *                                               true is useful to make sure $pointer points to the current operator and 
         *                                               not to one of its operands.
         *
         * @throws 
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */ 
        private function & goToParentOperator(array &$pointer, bool $stopAtCurrentIfAlreadyAnOperator = true) : array
        {
            $stopAtThisOperator = $stopAtCurrentIfAlreadyAnOperator && isset($pointer["operator"]);
            if($stopAtThisOperator  || isset($pointer["function"]) || isset($pointer["expression"]))
            {
                return $pointer;
            }
            
            do{
                $pointer = &$this->getParent($pointer);
            } while(!isset($pointer["operator"]) && !isset($pointer["expression"]) && !isset($pointer["function"]));
            
            return $pointer;
        }
        
        /** Steps of current element
         *
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         *
         * @throws \Exception If the element doesn't have a parent
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */
        private function & getParent(?array &$pointer) : ?array
        {
            if(isset($pointer["parent"]))
            {
                if($pointer["parent"] !== null)
                {
                    return $pointer["parent"];
                }
                else 
                {
                    throw new \Exception("Element does not have a parent.");
                }
            }
            else
            {
                throw new \Exception("Element does not have a parent.");
            }
        }
        
        /** Determines if it is possible to step out of the immediately previous operator
         *
         * @param byref array $currentOperator The current operator in the mathematical expression array format. 
         *                    It should be initialized with at least its identity.
         * @param byref array $pointer A reference to the current node in the mathematical expression array
         *
         * @throws \Exception If the element doesn't have a parent
         * @author Marc-Olivier Bazin-Maurice
         * @return array The reference to the new current node in the expression array
         */
        private function canStepOutOfPreviousOperator(?array &$currentOperator, ?array &$pointer) : bool
        {   
            while(1)
            {
                if(isset($pointer["function"]))
                {
                    return false;
                }
                elseif(isset($pointer["operator"]))
                {
                    if($pointer["operator"]["identity"] === "()")
                    {
                        return false;
                    }
                    else
                    {
                        $currentOperatorPriority = self::operators_priority[$currentOperator["operator"]["identity"]];
                        $previousOperatorPriority = self::operators_priority[$pointer["operator"]["identity"]];
                        return ($previousOperatorPriority <= $currentOperatorPriority);
                    }
                }
                elseif(isset($pointer["expression"]))
                {
                    return false;
                }
                else
                {
                    try
                    {
                        $pointer = &$this->goToParentOperator($pointer, true);
                    }
                    catch (\Exception $e)
                    {
                        return false;
                    }
                }
            }
        }
    }
    
    /**
     * \MprExpression\SolverFunctions
     * Contains PHP copies of the functions that are available in the mpr format.
     *
     *
     * @package
     * @subpackage
     * @author     Marc-Olivier Bazin-Maurice
     */
    class SolverFunctions
    {
        /** Returns the sine of an angle expressed in degrees.
         *
         * @param float $angle An angle expressed in degrees
         *
         * @throws 
         * @author Marc-Olivier Bazin-Maurice
         * @return float The sine of the angle
         */
        public function sin(float $angle) : float
        {
            return sin($angle * pi() / 180);
        }
        
        /** Returns the cosine of an angle expressed in degrees
         *
         * @param float $angle An angle expressed in degrees
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return float The cosine of the angle
         */
        public function cos(float $angle) : float
        {
            return cos($angle * pi() / 180);
        }
        
        /** Returns the tangent of an angle expressed in degrees
         *
         * @param float $angle An angle expressed in degrees
         *
         * @throws \Exception If the angle is equivalent to -90 or 90 degrees as the result would be respectively 
         *                    -infinite or infinite.
         * @author Marc-Olivier Bazin-Maurice
         * @return float The tangent of the angle
         */
        public function tan(float $angle) : float
        {
            if(abs(fmod($angle, 180)) !== 90)
            {
                return tan($angle * pi() / 180);
            }
            else
            {
                throw new \Exception("Cannot calculate \"tangent({$angle})\" because it leads to a division by 0.");
            }
        }
        
        /** Returns the inverse sine, expressed in degrees, of a value.
         *
         * @param float $value A value between -1 and 1.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return float The inverse sine, expressed in degrees, of the value
         */
        public function arcsin(float $value) : float
        {
            return asin($value) * 180 / pi();
        }
           
        /** Returns the inverse cosine, expressed in degrees, of a value.
         *
         * @param float $value A value between -1 and 1.
         *
         * @throws
         * @author Marc-Olivier Bazin-Maurice
         * @return float The inverse cosine, expressed in degrees, of the value
         */
        public function arccos(float $value) : float
        {
            return acos($value) * 180 / pi();
        }
        
        /** Returns the inverse tangent, expressed in degrees, of a value.
         *
         * @param float $value Any real number.
         *
         * @author Marc-Olivier Bazin-Maurice
         * @return float The inverse tangent, expressed in degrees, of the value
         */
        public function arctan(float $value) : float
        {
            return atan($value) * 180 / pi();
        }
        
        /** Returns the exponential in base e of a value.
         *
         * @param float $value Any real number.
         *
         * @author Marc-Olivier Bazin-Maurice
         * @return float The exponential in base of the value
         */
        public function exp(float $value) : float
        {
            return exp($value);
        }
        
        /** Returns the natural logarithm of a value.
         *
         * @param float $value Any real number.
         *
         * @author Marc-Olivier Bazin-Maurice
         * @return float The natural logarithm of the value
         */
        public function ln(float $value) : float
        {
            return log($value);
        }
        
        /** Returns the square root of a value. This is equivalent to pow(value, 1/2).
         *
         * @param float $value Any real number.
         *
         * @author Marc-Olivier Bazin-Maurice
         * @return float The square root of the value
         */
        public function sqrt(float $value) : float
        {
            return sqrt($value);
        }
        
        /** Returns the whole part of a value.
         *
         * @param float $value Any real number.
         *
         * @author Marc-Olivier Bazin-Maurice
         * @return float The whole part of the value
         */
        public function mod(float $value) : float
        {
            $sign = ($value < 0 ? -1 : 1);
            return $sign * floor(abs($value));
        }
        
        /** Returns the decimal part of a value.
         *
         * @param float $value Any real number.
         *
         * @author Marc-Olivier Bazin-Maurice
         * @return float The decimal part of the value
         */
        public function prec(float $value) : float
        {
            $sign = ($value < 0 ? -1 : 1);
            return $sign * (abs($value) - floor(abs($value)));
        }
        
        /** Returns the absolute value of a value.
         *
         * @param float $value Any real number.
         *
         * @author Marc-Olivier Bazin-Maurice
         * @return float The absolute value of the value
         */
        public function abs(float $value) : float
        {
            return abs($value);
        }
    }
}
?>