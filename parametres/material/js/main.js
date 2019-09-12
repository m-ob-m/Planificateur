"use strict";

/**
 * Opens view window for this Material.
 * @param {int} id The id of the material to view (null means new material).
 */
function openMaterial(id = null)
{
	let view_URL = [ROOT_URL + "/parametres/material/view.php"];
	if(id !== null && id !== "")
	{
		view_URL.push("?id=", id);
	}
	window.location.assign(view_URL.join(""));
}