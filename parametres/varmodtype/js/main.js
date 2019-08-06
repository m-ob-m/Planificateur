"use strict";

function openModelTypeParameters(modelId = null, typeNo = null)
{
	let next_page = [ROOT_URL + "/parametres/varmodtype/index.php"];
	let parameters = [];
	if(modelId !== null && modelId !== "")
	{
		let delimiter = (parameters.length === 0) ? "?" : "&";
		parameters.push(delimiter, "modelId", "=", modelId);
	}
	if(typeNo !== null && typeNo !== "")
	{
		let delimiter = (parameters.length === 0) ? "?" : "&";
		parameters.push(delimiter, "typeNo", "=", typeNo);
	}
	window.location.assign(next_page.concat(parameters).join(""));
}