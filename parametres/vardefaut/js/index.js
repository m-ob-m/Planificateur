"use strict";

$(async function()
	{
		await refreshParameters()
	}
);


/**
 * Refreshes the parameters list
 */
async function refreshParameters()
{
	let id = parseInt($("select#generic option:selected").val());
	
	try{
		$("table#parametersTable >tbody >tr").remove();
		let parameters = await retrieveParameters(id);
		$(parameters).each(function(){
			$("table#parametersTable >tbody").append(newParameter(this));
		});

		if(parameters.length < 1)
		{
			$("table#parametersTable >tbody").append(newParameter());
		}
	}
	catch(error){
		showError("Le rafraîchissement des paramètres a échoué", error);
	}
}

/**
 * Fetches parameters from the database
 * @param {int} id The id of the desired Generic
 * 
 * @return {Promise}
 */
function retrieveParameters(id)
{	
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/parametres/vardefaut/actions/getParameters.php",
			"data": {"id": id},
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
 * Validates user input before saving
 * @param {int} id The id of the desired Generic
 * @param {array} parameters The parameters as an array
 * 
 * @return {bool} If information is valid, returns true. Otherwise, returns false.
 */
function validateInformation(id, parameters)
{
	let err = "";
	
	// Validation des parametres pour chaque parametre
	$(parameters).each(function(index){
		
		if(!(new RegExp("^\\S+$")).test(this.key))
		{
			err += "La clé du paramètre de la ligne \"" + (this.index + 1) + "\" est vide ou contient des espaces blancs. ";
			return;
		}
		
		if(!this.value.trim())
		{
			err += "La valeur du paramètre ayant la clé \"" + this.key + "\" est vide. ";
		}
		
		if(!this.description.trim())
		{
			err += "La description du paramètre ayant la clé \"" + this.key + "\" est vide. ";
		}
		
		if(this.quickEdit !== 0 && this.quickEdit !== 1)
		{
			err += "Le paramètre de l'édition rapide peut seulement prendre les valeurs \"0\" ou \"1\". ";
		}
	});
			
	if(!isPositiveInteger(id) && id !== "" && id !== null)
	{
		err += "L'identificateur unique doit être un entier positif. ";
	}
	
	// S'il y a erreur, afficher la fenêtre d'erreur
	if(err == "")
	{
		return true;
	}
	else
	{
		showError("Les informations du modèle ne sont pas valides", err);
		return false;
	}
}

/**
 * Prompts user to confirm the saving of the current Generic Parameters.
 */
async function saveConfirm()
{
	let generic = $("select#generic option:selected");
	let args = [generic.val(), getParametersArray()];
	let confirmationMessage = "Voulez-vous vraiment sauvegarder ces paramètres pour le générique : \"" + generic.text()  + "\"?";
	
	if(validateInformation.apply(null, args))
	{
		if(await askConfirmation("Sauvegarde de paramètres de générique", confirmationMessage))
		{
			$('#loadingModal').css({"display": "block"});
			try{
				await saveParameters.apply(null, args);
				openGenericParameters(generic.val());
			}
			catch(error){
				showError("La sauvegarde des paramètres du générique a échouée", error);
			}
			finally{
				$('#loadingModal').css({"display": "none"});
			}
		}
	}
}

/**
 * Gets the parameters from the page and puts them into an array
 * 
 * @return {array} The parameters as an array
 */
function getParametersArray()
{
	let parameters = [];
	$("table#parametersTable >tbody >tr").each(function(index){
		parameters.push({
			"key": $(this).find('td:nth-child(1) >input').val(), 
			"value": $(this).find('td:nth-child(2) >textarea').val(),
			"description": $(this).find('td:nth-child(3) >textarea').val(),
			"quickEdit": parseInt($(this).find('td:nth-child(4) >select').val()),
			"index": index
		});
	});
	return parameters;
}

/**
 * Saves parameters to the database
 * @param {int} id The id of the current Generic
 * @param {array} parameters The parameters as an array
 * 
 * @return {Promise}
 */
function saveParameters(id, parameters)
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/parametres/vardefaut/actions/save.php",
			"data": JSON.stringify({"id": id, "parameters": parameters}),
			"dataType": "json",
			"async": true,
			"cache": false
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
 * Adds a parameter to the parameters list
 * @param {jquery} row The row after which the element must be added.
 * 
 * @return {bool} false To prevent any further behavior (automatic page scroll to top)
 */
function addParameter(row)
{
	row.after(newParameter());
	
	return false;
}

/**
 * Removes a parameter from the parameters list
 * @param {jquery} row The row to remove.
 * 
 * @return {bool} false To prevent any further behavior (automatic page scroll to top)
 */
function removeParameter(row)
{
	if(row.siblings().length > 1)
	{
		row.remove();
	}
	
	return false;
}

/**
 * Creates a new parameter row to add in the parameters list
 * @param {object} parameter An object that respects the following formatting {_key: "key", _value: value, _description: "description"}.
 * 
 * @return {jquery} A new parameter row
 */
function newParameter(parameter = null)
{
	let key = ((parameter === null) ? null : parameter._key);
	let value = ((parameter === null) ? null : parameter._value);
	let description = ((parameter === null) ? null : parameter._description);
	let quickEdit = ((parameter === null) ? 0 : parameter._quick_edit);
	
	let row = $("<tr></tr>")
	.css({"height": "50px"});
	
	let keyInput = $("<input>")
	.addClass("spaceEfficientText")
	.attr({"maxlength": "8"})
	.val(key);
	
	let keyCell = $("<td></td>")
	.addClass("firstVisibleColumn")
	.append(keyInput);
	
	let valueInput = $("<textarea></textarea>")
	.addClass("spaceEfficientText")
	.css({"overflow-x": "hidden", "overflow-y": "auto", "resize": "none"})
	.val(value);
	
	let valueCell = $("<td></td>")
	.css({"vertical-align": "middle"})
	.append(valueInput);
	
	let descriptionInput = $("<textarea></textarea>")
	.addClass("spaceEfficientText")
	.css({"overflow-x": "hidden", "overflow-y": "auto", "resize": "none"})
	.val(description);
	
	let descriptionCell = $("<td></td>")
	.append(descriptionInput);
	
	let quickEditOption1 = $("<option></option>")
	.val("0")
	.text("Désactivé")
	
	let quickEditOption2 = $("<option></option>")
	.val("1")
	.text("Activé")
	
	let quickEditSelect = $("<select></select>")
	.addClass("spaceEfficientText")
	.append(quickEditOption1, quickEditOption2)
	.val(quickEdit);
	
	let quickEditCell = $("<td></td>")
	.append(quickEditSelect);
	
	let addTool = $("<div></div>")
	.css({"width": "100%", "display": "inline-block;"})
	.append(imageButton("/Planificateur/images/add.png", "Ajouter", addParameter, [row]));
	
	let minusTool = $("<div></div>")
	.css({"width": "100%", "display": "inline-block"})
	.append(imageButton("/Planificateur/images/minus.png", "Enlever", removeParameter, [row]));
	
	let toolsContainer = $("<div></div>")
	.css({"height": "min-content"})
	.append(addTool, minusTool);
	
	let toolsCell = $("<td></td>")
	.addClass("lastVisibleColumn")
	.append(toolsContainer);
	
	return row.append(keyCell, valueCell, descriptionCell, quickEditCell, toolsCell);
}