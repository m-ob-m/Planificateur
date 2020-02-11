"use strict";

docReady(function(){
	refreshParameters();
});

/**
 * Validates user input before saving
 * @param {int} modelId The id of the selected model
 * @param {int} typeNo The import number of the selected type
 * @param {array} parmeters The parameters as an array
 * 
 * @return {bool} If information is valid, returns true. Otherwise, returns false.
 */
function validateInformation(modelId, typeNo, parameters)
{
	let err = "";
	
	// Validation des parametres pour chaque parametre
	parameters.forEach(function(parameter){
		if(!(new RegExp("^\\S+$")).test(parameter.key))
		{
			err += "La clé du paramètre de la ligne \"" + (parameter.index + 1) + "\" est vide ou contient des espaces blancs. ";
		}
	});
			
	if(!isPositiveInteger(modelId, true, true) && modelId !== "" && modelId !== null)
	{
		err += "Le modèle choisi présente un problème. ";
	}
	
	if(!isPositiveInteger(typeNo, true, false) && typeNo !== "" && typeNo !== null)
	{
		err += "Le type choisi présente un problème. ";
	}
	
	// S'il y a erreur, afficher la fenêtre d'erreur
	if(err == "")
	{
		return true;
	}
	else
	{
		showError("Les informations du modèle-type ne sont pas valides", err);
		return false;
	}
} 

/**
 * Display a message to validate the fact that the user wants to save these parameters
 * 
 * @return {Promise}
 */
async function saveConfirm()
{
	let modelSelect = document.getElementById("model"); 
	let typeSelect = document.getElementById("type"); 
	let selectedModelId = modelSelect.options[modelSelect.selectedIndex].value;
	let selectedTypeNo = typeSelect.options[typeSelect.selectedIndex].value;
	let selectedModelText = modelSelect.options[modelSelect.selectedIndex].text;
	let selectedTypeText = typeSelect.options[typeSelect.selectedIndex].text;
	let parameters = getModifiedParametersArray();
	let args = [parseInt(selectedModelId), parseInt(selectedTypeNo), parameters];
	let modelType = selectedModelText + " - " + selectedTypeText;
	let message = "Voulez-vous vraiment sauvegarder ces paramètres pour la combinaison modèle-type : \"" + modelType  + "\"?";
	
	if(validateInformation.apply(null, args))
	{
		if(await askConfirmation("Sauvegarde des paramètres de paramètres de combinaison modèle-type", message))
		{
			document.getElementById("loadingModal").style.display = "block";
			try{
				await saveParameters.apply(null, args);
				openModelTypeParameters(selectedModelId, selectedTypeNo);
			}
			catch(error){
				showError("La sauvegarde de la combinaison modèle-type a échouée", error);
			}
			finally{
				document.getElementById("loadingModal").style.display = "none";
			}
		}
	}
}

/**
 * Gets the modified parameters from the page and puts them into an array
 * 
 * @return {array} The modified parameters as an array
 */
function getModifiedParametersArray()
{
	let tableBody = document.getElementById("parametersTable").getElementsByTagName("tbody")[0];
	let parameters = [];
	[...tableBody.getElementsByTagName("tr")].forEach(function(parameterRow, index){
		let key = parameterRow.getElementsByTagName("td")[0].getElementsByTagName("input")[0].value;
		let previousValue = parameterRow.getElementsByTagName("td")[4].getElementsByTagName("input")[0].value;
		let newValue = parameterRow.getElementsByTagName("td")[1].getElementsByTagName("textarea")[0].value;
		if(previousValue !== newValue)
		{
			parameters.push({
				"key": key,
				"value": newValue,
				"index": index
			});
		}
	});
	return parameters;
}

/**
 * Retrieves parameters from the database
 * @param {int} modelId The id of the current Model
 * @param {int} typeNo The import number of the current Type
 * 
 * @return {Promise}
 */
function retrieveParameters(modelId, typeNo)
{	
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/varmodtype/actions/getParameters.php",
			"data": {"modelId": modelId, "typeNo": typeNo},
			"dataType": "json",
			"async": true,
			"cache": false,
			"onSuccess": function(response){
				if(response.status === "success")
				{
					resolve(response.success.data);
				}
				else
				{
					reject(response.failure.message);
				}
			},
			"onFailure": function(error){
				reject(error);
			}
		});
	});
}

/**
 * Saves parameters to the database
 * @param {jquery} table The parameters table
 * @param {int} modelId The id of the current Model
 * @param {int} typeNo The import number of the current Type
 * @param {array} parameters The array of parameters
 * 
 * @return {Promise}
 */
function saveParameters(modelId, typeNo, parameters)
{	
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/varmodtype/actions/save.php",
			"data": {"modelId": modelId, "typeNo": typeNo, "parameters": parameters},
			"dataType": "json",
			"async": true,
			"cache": false,
			"onSuccess": function(response){
				if(response.status === "success")
				{
					resolve(response.success.data);
				}
				else
				{
					reject(response.failure.message);
				}
			},
			"onFailure": function(error){
				reject(error);
			}
		});
	});
}

/**
 * Creates a new parameter row to add in the parameters list
 * @param {object} parameter An object that respects the following formatting 
 * 		{key: "key", value: value, description: "description", defaultValue: "defaultValue"}.
 * 
 * @return {Element} A new parameter row
 */
function newParameter(parameter)
{
	let key = ((parameter === null) ? null : parameter.key);
	let value = ((parameter === null) ? null : parameter.specificValue);
	let description = ((parameter === null) ? null : parameter.description);
	let defaultValue = ((parameter === null) ? null : parameter.defaultValue);
	
	let keyInput = document.createElement("input");
	keyInput.style.height = "100%";
	keyInput.disabled = true;
	keyInput.classList.add("spaceEfficientText");
	keyInput.value = key;
	
	let keyCell = document.createElement("td");
	keyCell.style.verticalAlign = "middle";
	keyCell.classList.add("firstVisibleColumn");
	keyCell.appendChild(keyInput);
	
	let valueInput = document.createElement("textarea");
	valueInput.style.overflowX = "hidden"; 
	valueInput.style.resize = "none";
	valueInput.classList.add("spaceEfficientText");
	valueInput.value = value;
	
	let valueCell = document.createElement("td");
	valueCell.style.verticalAlign = "middle";
	valueCell.appendChild(valueInput);
	
	let descriptionInput = document.createElement("textarea");
	descriptionInput.style.overflowX = "hidden"; 
	descriptionInput.style.resize = "none";
	descriptionInput.readOnly = true;
	descriptionInput.classList.add("spaceEfficientText");
	descriptionInput.value = description;
	
	let descriptionCell = document.createElement("td");
	descriptionCell.style.verticalAlign = "middle";
	descriptionCell.appendChild(descriptionInput);
	
	let defaultValueInput = document.createElement("textarea");
	defaultValueInput.style.overflowX = "hidden"; 
	defaultValueInput.style.resize = "none";
	defaultValueInput.disabled = true;
	defaultValueInput.classList.add("spaceEfficientText");
	defaultValueInput.value = defaultValue;
	
	let defaultValueCell = document.createElement("td");
	defaultValueCell.style.verticalAlign = "middle";
	defaultValueCell.classList.add("lastVisibleColumn");
	defaultValueCell.appendChild(defaultValueInput);
	
	let oldValueInput = document.createElement("input");
	oldValueInput.style.display = "none";
	oldValueInput.disabled = true;
	oldValueInput.value = value;

	let oldValueCell = document.createElement("td");
	oldValueCell.style.display = "none";
	oldValueCell.disabled = true;
	oldValueCell.appendChild(oldValueInput);
	
	let row = document.createElement("tr");
	row.style.height = "50px";
	row.appendChild(keyCell);
	row.appendChild(valueCell);
	row.appendChild(descriptionCell);
	row.appendChild(defaultValueCell);
	row.appendChild(oldValueCell);
	return row;
}

/**
 * Refreshes the parameters list.
 */
async function refreshParameters()
{
	let tableBody = document.getElementById("parametersTable").getElementsByTagName("tbody")[0];
	let modelSelect = document.getElementById("model");
	let typeSelect = document.getElementById("type");
	let modelId = modelSelect.options[modelSelect.selectedIndex].value;
	let typeNo = typeSelect.options[typeSelect.selectedIndex].value;
	
	while(tableBody.childElementCount > 0)
	{
		tableBody.firstElementChild.remove();
	}

	try
	{
		if(modelId !== null && modelId !== "" && typeNo !== null && typeNo !== "")
		{
			(await retrieveParameters(modelId, typeNo)).map(function(parameter){
				tableBody.appendChild(newParameter(parameter));
			});
			
			if(tableBody.childElementCount < 1)
			{
				tableBody.appendChild(newParameter());
			}
		}
	}
	catch(error)
	{
		showError("La récupération des paramètres de la combinaison modèle-type a échouée", error);
	}
}

/**
 * Exports parameters.
 */
async function exportParameters()
{
	let modelSelect = document.getElementById("model"); 
	let selectedModel = modelSelect.options[modelSelect.selectedIndex].value;

	try{
		let downloadLink = await exportParametersToExcelFile(selectedModel);
		downloadFile(downloadLink, downloadLink.substring(downloadLink.lastIndexOf("/") + 1));
	}
	catch(error){
		showError("L'exportation des paramètres du modèle sélectionné a échouée", error);
	}
}

/**
 * Imports parameters.
 * 
 * @return {Promise}
 */
function importParameters()
{
	document.getElementById("filesToImport").click();
}

/**
 * Exports parameters to an Excel file.
 * 
 * @return {Promise}
 */
function exportParametersToExcelFile(modelId)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/varmodtype/actions/exportToExcel.php",
			"data": {"modelId": modelId, "generic": null},
			"dataType": "json",
			"async": true,
			"cache": false,
			"onSuccess": function(response){
				if(response.status === "success")
				{
					resolve(response.success.data);
				}
				else
				{
					reject(response.failure.message);
				}
			},
			"onFailure": function(error){
				reject(error);
			}
		});
	});
}

/**
 * Imports parameters from an Excel file.
 * @param {object} file The file to import.
 * 
 * @return {Promise}
 */
function importParametersFromExcelFile(file)
{
	let formData = new FormData();
	formData.append("files[]", file);
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "POST",
			"contentType": null,
			"url": ROOT_URL + "/parametres/varmodtype/actions/importFromExcel.php",
			"data": formData,
			"dataType": "json",
			"async": true,
			"cache": false,
			"onSuccess": function(response){
				if(response.status === "success")
				{
					resolve(response.success.data);
				}
				else
				{
					reject(response.failure.message);
				}
			},
			"onFailure": function(error){
				reject(error);
			}
		});
	});
}

async function importParameterFiles()
{
	let modelSelect = document.getElementById("model"); 
	let typeSelect = document.getElementById("type"); 
	let selectedModel = modelSelect.options[modelSelect.selectedIndex].value;
	let selectedType = typeSelect.options[typeSelect.selectedIndex].value;

	document.getElementById("loadingModal").style.display = "block";
	try{
		await importParametersFromExcelFile(document.getElementById("filesToImport").files[0]);
		openModelTypeParameters(selectedModel, selectedType);
	}
	catch(error){
		showError("L'importation des paramètres a échouée.", error)
	}
	finally{
		document.getElementById("filesToImport").value = "";
		document.getElementById("loadingModal").style.display = "none";
	}
}