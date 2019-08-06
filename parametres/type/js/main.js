"use strict";

/**
 * Opens view window for this Type.
 * @param {int} id The id of the Type to view (null means new Type).
 */
function openType(id = null)
{
	let view_URL = [ROOT_URL + "/parametres/type/view.php"];
	if(id !== null && id !== "")
	{
		view_URL.push("?id=", id);
	}
	window.location.assign(view_URL.join(""));
}