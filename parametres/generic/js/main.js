"use strict";

/**
 * Opens view window for this Generic.
 * @param {int} id The id of the generic to view (null means new generic).
 */
function openGeneric(id = null)
{
	let view_URL = ["/Planificateur/parametres/generic/view.php"];
	if(id !== null && id !== "")
	{
		view_URL.push("?id=", id);
	}
	window.location.assign(view_URL.join(""));
}