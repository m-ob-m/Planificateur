"use strict";

/**
 * Redirects to the view page for the specified test or the test creation page when no testId is specified.
 * @param {int} testId The id of the specified test
 */
function openTest(testId = null)
{
	let view_URL = ["/Planificateur/parametres/test/view.php"];
	if(testId !== null && testId !== "")
	{
		view_URL.push("?id=", testId);
	}
	window.location.assign(view_URL.join(""));
}