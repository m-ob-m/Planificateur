"use strict";

/**
 * Tests if the parameter represents a positive integer.
 * @param {mixed} value A value that may or may not represent a positive integer.
 * @param {bool} allow0 If true, then 0 is considered a valid positive integer.
 * 
 * @return true if the value is a positive integer or a string representing a positive integer.
 */
function isPositiveInteger(value, allow0 = true) 
{
	let n = Math.floor(Number(value));
    return n !== Infinity && String(n) === value && (allow0 ? n >= 0 : n > 0);

}
