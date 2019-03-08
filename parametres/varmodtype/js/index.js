"use strict";

$(function(){
	refreshParameters();
});

/**
 * Validates user input before saving
 * @param {int} modelId The id of the selected model
 * @param {int} typeNo The import number of the selected type
 * @param {array} parmeters The parameters as an array
 * 
 * @return {Promise}
 */
function validateInformation(modelId, typeNo, parameters)
{
	return new Promise(function(resolve, reject){
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
		
		if(err !== "")
		{
			reject(err);
		}
		else
		{
			resolve();
		}
	});
} 

/**
 * Display a message to validate the fact that the user wants to save these parameters
 * 
 * @return {Promise}
 */
function saveConfirm()
{
	let model = $("select#model option:selected");
	let type = $("select#type option:selected");
	let parameters = getModifiedParametersArray();
	let args = [parseInt(model.val()), parseInt(type.val()), parameters];
	let modelType = model.text() + " - " + type.text();
	let message = "Voulez-vous vraiment sauvegarder ces paramètres pour la combinaison modèle-type : \"" + modelType  + "\"?";
	
	return validateInformation.apply(null, args)
	.catch(function(error){
		showError("La sauvegarde de la combinaison modèle-type a échouée", error);
		return Promise.reject();
	})
	.then(function(){
		return askConfirmation("Sauvegarde des paramètres de paramètres de combinaison modèle-type", message)
		.then(function(){
			$("#loadingModal").css({"display": "block"});
			return saveParameters.apply(null, args)
			.catch(function(error){
				showError("La sauvegarde de la combinaison modèle-type a échouée", error);
				return Promise.reject();
			})
			.then(function(){
				openModelTypeParameters(model.val(), type.val());
			})
			.finally(function(){
				$("#loadingModal").css({"display": "none"});
			});
		});
	})
	.catch(function(error){/* Do nothing. */});
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
			"url": "/Planificateur/parametres/varmodtype/actions/getParameters.php",
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

function exportParameters()
{
	return exportParametersToXlsx($("select#model option:selected").val())
	.catch(function(error){
		showError("La sauvegarde de la combinaison modèle-type a échouée", error);
		return Promise.reject();
	})
	.then(function(downloadLink){
		downloadFile(downloadLink);
	})
	.catch(function(error){/* Do nothing. */});
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
			"url": "/Planificateur/parametres/varmodtype/actions/save.php",
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
 * Exports the parameters of the selected model to an xlsx file.
 * @param {int} modelId The id of the current Model
 * 
 * @return {Promise}
 */
function exportParametersToXlsx(modelId)
{	
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/parametres/varmodtypegen/actions/exportToExcel.php",
			"data": {"modelId": modelId},
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
 * Refreshes the parameters list
 * 
 * 
 */
function refreshParameters()
{
	$("table#parametersTable >tbody >tr").remove();
	retrieveParameters($("select#model option:selected").val(), $("select#type option:selected").val())
	.then(function(parameters){
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
	})
	.catch(function(error){
		showError("La récupération des paramètres de la combinaison modèle-type a échouée", error);
		return Promise.reject();
	});
}