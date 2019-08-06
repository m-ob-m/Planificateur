"use strict";

/**
 * Opens view window for this Model.
 * @param {int} id The id of the Model to view (null means new Model).
 */
function openModel(id = null)
{
	let view_URL = [ROOT_URL + "/parametres/model/view.php"];
	if(id !== null && id !== "")
	{
		view_URL.push("?id=", id);
	}
	window.location.assign(view_URL.join(""));
}