<?php 
    /**
     * Converts a number to a mixed number (1.5 => 1 1/2)
     *
     * @param mixed $arg The number to convert
     * @param int $denom The maximum value of the denominator (sets the precision)
     * @param bool $reduce When true, the fractional part is simplified
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return string The mixed number
     */
    function toMixedNumber($arg, int $denom, bool $reduce = true) : string
    {
        $num = round($arg * $denom);
        $int = (int)($num / $denom);
        $num %= $denom;
        
        if (!$num)
        {
            return "{$int}";
        }
        
        if ($reduce)
        {
            // Use Euclid's algorithm to find the GCD.
            $a = $num < 0 ? -$num : $num;
            $b = $denom;
            while ($b)
            {
                $t = $b;
                $b = $a % $t;
                $a = $t;
            }
            
            $num /= $a;
            $denom /= $a;
        }
        
        if ($int)
        {
            // Suppress minus sign in numerator; keep it only in the integer part.
            if ($num < 0)
            {
                $num *= -1;
            }
            return "{$int} {$num}/{$denom}";
        }
        
        return "{$num}/{$denom}";
    }

    /**
     * Verifies if a primitive value is a positive integer or a string representing a positive integer
     *
     * @param mixed $value The value to test
     *
     * @throws
     * @author Marc-Olivier Bazin-Maurice
     * @return bool True if $value is a positive integer or a string representing a positive integer, false otherwise.
     */
    function is_positive_integer_or_equivalent_string($value) : bool
    {
        if(is_scalar($value))
        {
            if(is_numeric($value) && !is_nan($value))
            {
                if(strval($value) === trim(strval($value)))
                {
                    if($value == round($value))
                    {
                        if($value >= 0)
                        {
                            /* echo "Value is a positive integer or a string representing a positive integer.\n"; */
                            return true;
                        }
                        else
                        {
                            /* echo "Value represents a negative number.\n"; */
                            return false;
                        }
                    }
                    else
                    {
                        /* echo "Value represents a decimal number.\n"; */
                        return false;
                    }
                }
                else
                {
                    /* echo "Value starts or ends with blank characters.\n"; */
                    return false;
                }
            }
            else 
            {
                /* echo "Value is not a number or a number represented as a string.\n"; */
                return false;
            }
        }
        else 
        {
            /* echo "Value is not scalar.\n"; */
            return false;
        }
    }
?>