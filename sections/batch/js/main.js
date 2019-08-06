"use strict";

/**
 * Clears the session storage and returns to the main window.
 */
function goToIndex()
{
	window.sessionStorage.clear();
	window.location.assign(ROOT_URL + "/index.php");
}

/**
 * Goes to the page of the Batch with the provided id
 */
function goToBatch(id)
{
	window.sessionStorage.clear();
	window.location.assign(window.location.protocol + "//" + window.location.host + window.location.pathname + "?id=" + id);
}