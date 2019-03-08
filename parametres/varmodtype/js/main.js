"use strict";

function openModelTypeParameters(modelId = null, typeNo = null)
{
	let next_page = ["/Planificateur/parametres/varmodtype/index.php"];
	let parameters = [];
	if(modelId !== null && modelId !== "")
	{
		delimiter = (parameters.length === 0) ? "?" : "&";
		parameters.push(delimiter, "modelId", "=", modelId);
	}
	if(typeNo !== null && typeNo !== "")
	{
		delimiter = (parameters.length === 0) ? "?" : "&";
		parameters.push(delimiter, "typeNo", "=", typeNo);
	}
	window.location.assign(next_page.concat(parameters).join(""));
}