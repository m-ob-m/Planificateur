"use strict";

/**
 * Tests if something is considered as a positive integer.
 * @param {any} value Something that may or may not be considered as a positive integer or an integer.
 * @param {boolean} [allowStrings=false] If true, strings that represent positive integers are allowed.
 * @param {boolean} [strictlyPositive=false] If true, 0 is disallowed.
 * 
 * @return True if value is considered as a positive integer, false otherwise.
 */
function isPositiveInteger(value, allowStrings = false, strictlyPositive = false) 
{
	return isInteger(value, allowStrings) && ((strictlyPositive) ? Number(value) > 0 : Number(value) >= 0);
}

/**
 * Tests if something is considered as a number.
 * @param {any} value Something that may or may not be considered as a number.
 * @param {boolean} [allowStrings=false] If true, strings that represent numbers are allowed.
 * 
 * @return True if value is considered as a positive integer, false otherwise.
 */
function isNumber(value, allowStrings = false)
{
	if(typeof value == 'number')
	{
		return true;
	}
	else if(allowStrings && isString(value, false))
	{
		return new RegExp("^-?\\d*(?:\\.\\d*$|$)").test(value);
	}
	else
	{
		return false;
	}
}

/**
 * Tests if something is considered as an integer.
 * @param {any} value Something that may or may not be considered as an integer.
 * @param {boolean} [allowStrings=false] If true, strings that represent integers are allowed.
 * 
 * @return {boolean} True if value is considered as an integer.
 */
function isInteger(value, allowStrings = false)
{
	if(typeof value == 'number' && Number.isInteger(value))
	{
		return true;
	}
	else if(allowStrings && isString(value, false))
	{
		return new RegExp("^-?\\d*(?:\\.0*$|$)").test(value);
	}
	else
	{
		return false;
	}
}

/**
 * Tests if something is considered as a string.
 * @param {any} value Something that may or may not be considered as a string.
 * @param {boolean} [allowNumericalTypes=false] If true, numerical types are allowed.
 * 
 * @return {boolean} True if value is considered as a string.
 */
function isString(value, allowNumericalTypes = false)
{
	return typeof value === 'string' || value instanceof String || (allowNumericalTypes && isNumber(value, false));
}

/**
 * Tests if something is a function.
 * @param {any} value Something that may or may not be a function.
 * 
 * @return {boolean} True if value is a function.
 */
function isFunction(value) {
	return typeof value === "function";
}

/**
 * Tests if something is an object.
 * @param {any} value Something that may or may not be an object.
 * 
 * @return {boolean} True if value is an object.
 */
function isObject(value) {
	return typeof value === 'object' && value !== null;
}

/**
 * Tests if something is an array.
 * @param {any} value Something that may or may not be an array.
 * 
 * @return {boolean} True if value is an array.
 */
function isArray(value) {
	return Array.isArray(value);
}