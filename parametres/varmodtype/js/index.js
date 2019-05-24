"use strict";

$(function(){
	$("input#filesToImport").change(async function(){
		$("#loadingModal").css({"display": "block"});
		try{
			await importParametersFromExcelFile($(this).prop("files")[0]);
			openModelTypeParameters($("select#model").val(), $("select#type").val())
		}
		catch(error){
			showError("L'importation des paramètres a échouée.", error)
		}
		finally{
			$("input#filesToImport").val("");
			$("#loadingModal").css({"display": "none"});
		}
	});
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
	$(parameters).each(function(){
		if(!(new RegExp("^\\S+$")).test(this.key))
		{
			err += "La clé du paramètre de la ligne \"" + (this.index + 1) + "\" est vide ou contient des espaces blancs. ";
		}
	});
			
	if(!isPositiveInteger(modelId) && modelId !== "" && modelId !== null)
	{
		err += "Le modèle choisi présente un problème. ";
	}
	
	if(!isPositiveInteger(typeNo) && typeNo !== "" && typeNo !== null)
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
	let model = $("select#model option:selected");
	let type = $("select#type option:selected");
	let parameters = getModifiedParametersArray();
	let args = [parseInt(model.val()), parseInt(type.val()), parameters];
	let modelType = model.text() + " - " + type.text();
	let message = "Voulez-vous vraiment sauvegarder ces paramètres pour la combinaison modèle-type : \"" + modelType  + "\"?";
	
	if(validateInformation.apply(null, args))
	{
		if(await askConfirmation("Sauvegarde des paramètres de paramètres de combinaison modèle-type", message))
		{
			$("#loadingModal").css({"display": "block"});
			try{
				await saveParameters.apply(null, args);
				openModelTypeParameters(model.val(), type.val());
			}
			catch(error){
				showError("La sauvegarde de la combinaison modèle-type a échouée", error);
			}
			finally{
				$("#loadingModal").css({"display": "none"});
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
	let parameters = [];
	$("table#parametersTable >tbody >tr").each(function(index){
		if($(this).find('td:nth-child(5) >input').val() !== $(this).find('td:nth-child(2) >textarea').val())
		{
			parameters.push({
				"key": $(this).find('td:nth-child(1) >input').val(),
				"value": $(this).find('td:nth-child(2) >textarea').val(),
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
		$.ajax({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/varmodtype/actions/getParameters.php",
			"data": {"modelId": modelId, "typeNo": typeNo},
			"dataType": "json",
			"async": true,
			"cache": false,
		})
		.done(function(response){
			if(response.status === "success")
			{
				resolve(response.success.data);
			}
			else
			{
				reject(response.failure.message);
			}
		})
		.fail(function(error){
			reject(error.responseText);
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
		$.ajax({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/varmodtype/actions/save.php",
			"data": JSON.stringify({"modelId": modelId, "typeNo": typeNo, "parameters": parameters}),
			"dataType": "json",
			"async": true,
			"cache": false,
		})
		.done(function(response){
			if(response.status === "success")
			{
				resolve(response.success.data);
			}
			else
			{
				reject(response.failure.message);
			}
		})
		.fail(function(error){
			reject(error.responseText);
		});
	});
}

/**
 * Creates a new parameter row to add in the parameters list
 * @param {object} parameter An object that respects the following formatting 
 * 		{key: "key", value: value, description: "description", defaultValue: "defaultValue"}.
 * 
 * @return {jquery} A new parameter row
 */
function newParameter(parameter)
{
	let key = ((parameter === null) ? null : parameter.key);
	let value = ((parameter === null) ? null : parameter.value);
	let description = ((parameter === null) ? null : parameter.description);
	let defaultValue = ((parameter === null) ? null : parameter.defaultValue);
	
	let keyInput = $("<input>").css({"height": "100%"}).prop("disabled", true).addClass("spaceEfficientText").val(key);
	let keyCell = $("<td></td>").addClass("firstVisibleColumn").css({"vertical-align": "middle"}).append(keyInput);
	
	let valueInput = $("<textarea></textarea>")
	.css({"overflow-x": "hidden", "overflow-y": "auto", "resize": "none"})
	.addClass("spaceEfficientText")
	.val(value);
	
	let valueCell = $("<td></td>").css({"vertical-align": "middle"}).append(valueInput);
	
	let descriptionInput = $("<textArea></textArea>")
	.css({"overflow-x": "hidden", "overflow-y": "auto", "resize": "none"})
	.prop({"readonly": true})
	.addClass("spaceEfficientText")
	.val(description);
	
	let descriptionCell = $("<td></td>").css({"vertical-align": "middle"}).append(descriptionInput);
	
	let defaultValueInput = $("<textarea></textarea>")
	.css({"overflow-x": "hidden", "overflow-y": "auto", "resize": "none"})
	.prop({"disabled": true})
	.addClass("spaceEfficientText")
	.val(defaultValue);
	
	let defaultValueCell = $("<td></td>")
	.addClass("lastVisibleColumn")
	.css({"vertical-align": "middle"})
	.append(defaultValueInput);
	
	let oldValueInput = $("<input></input>").prop({"disabled": true}).css({"display": "none"}).val(value);
	let oldValueCell = $("<td></td>").prop({"disabled": true}).css({"display": "none"}).append(oldValueInput);
	
	return $("<tr></tr>").css({"height": "50px"}).append(keyCell, valueCell, descriptionCell, defaultValueCell, oldValueCell);
}

/**
 * Refreshes the parameters list.
 */
async function refreshParameters()
{
	$("table#parametersTable >tbody >tr").remove();
	try{
		if($("select#model >option").length > 0 && $("select#type >option").length > 0)
		{
			let modelId = $("select#model option:selected").val();
			let typeNo = $("select#type option:selected").val();
			let parameters = await retrieveParameters(modelId, typeNo);
			if(parameters.length > 0)
			{
				$(parameters).each(function(){
					$("table#parametersTable >tbody").append(newParameter(this));
				});
			}
			else
			{
				$("table#parametersTable >tbody").append(newParameter());
			}
		}
	}
	catch(error){
		showError("La récupération des paramètres de la combinaison modèle-type a échouée", error);
	}
}

/**
 * Exports parameters.
 */
async function exportParameters()
{
	try{
		let downloadLink = await exportParametersToExcelFile($("select#model >option:selected").val());
		downloadFile(downloadLink, downloadLink.substring(downloadLink.lastIndexOf('/') + 1));
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
	$("input#filesToImport").click();
}

/**
 * Exports parameters to an Excel file.
 * 
 * @return {Promise}
 */
function exportParametersToExcelFile(modelId)
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/varmodtype/actions/exportToExcel.php",
			"data": {"modelId": modelId, "generic": null},
			"dataType": "json",
			"async": true,
			"cache": false,
		})
		.done(function(response){
			if(response.status === "success")
			{
				resolve(response.success.data);
			}
			else
			{
				reject(response.failure.message);
			}
		})
		.fail(function(error){
			reject(error.responseText);
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
		$.ajax({
			"type": "POST",
			"contentType": false,
			"processData": false,
			"url": ROOT_URL + "/parametres/varmodtype/actions/importFromExcel.php",
			"data": formData,
			"dataType": "json",
			"async": true,
			"cache": false,
		})
		.done(function(response){
			if(response.status === "success")
			{
				resolve(response.success.data);
			}
			else
			{
				reject(response.failure.message);
			}
		})
		.fail(function(error){
			reject(error.responseText);
		});
	});
}