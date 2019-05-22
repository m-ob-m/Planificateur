"use strict";

/**
 * Tests if str represents a positive integer.
 * @param {string} str a string that may or may not represent a positive integer.
 * 
 * @return true if string represents a positive integer, false otherwise.
 */
function isPositiveInteger(str) 
{
	return (new RegExp("^\\+?\\d+$")).test(str);
}
