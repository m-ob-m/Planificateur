/**
 * Open the interface to create a new Type
 */
/**
 * Opens view window for this Type.
 * @param {int} id The id of the Type to view (null means new Type).
 */
function openType(id = null)
{
	let view_URL = ["/Planificateur/parametres/type/view.php"];
	if(id !== null && id !== "")
	{
		view_URL.push("?id=", id);
	}
	window.location.assign(view_URL.join(""));
}