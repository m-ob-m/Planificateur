"use strict";

docReady(async function(){
	if(document.getElementById("generic").childElementCount > 0)
	{
		await refreshParameters();
	}
});


/**
 * Refreshes the parameters list
 */
async function refreshParameters()
{
	let genericSelect = document.getElementById("generic");
	let id = genericSelect.options[genericSelect.selectedIndex].value;
	let table = document.getElementById("parametersTable");
	let tableBody = table.getElementsByTagName("tbody")[0];
	
	while(tableBody.childElementCount > 0)
	{
		tableBody.firstElementChild.remove();
	}

	try
	{
		(await retrieveParameters(id)).map(function(parameter){tableBody.appendChild(newParameter(parameter));});

		if(tableBody.childElementCount < 1)
		{
			tableBody.appendChild(newParameter());
		}
	}
	catch(error)
	{
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
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/vardefaut/actions/getParameters.php",
			"data": {"id": id},
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
	parameters.forEach(function(element){
		
		if(!(new RegExp("^\\S+$")).test(element.key))
		{
			err += "La clé du paramètre de la ligne \"" + (element.index + 1) + "\" est vide ou contient des espaces blancs. ";
			return;
		}
		
		if(!element.value.trim())
		{
			err += "La valeur du paramètre ayant la clé \"" + element.key + "\" est vide. ";
		}
		
		if(!element.description.trim())
		{
			err += "La description du paramètre ayant la clé \"" + element.key + "\" est vide. ";
		}
		
		if(element.quickEdit !== 0 && element.quickEdit !== 1)
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
	let genericSelect = document.getElementById("generic");
	let genericId = genericSelect.options[genericSelect.selectedIndex].value;
	let genericName = genericSelect.options[genericSelect.selectedIndex].text;
	let args = [genericId, getParametersArray()];
	let confirmationMessage = "Voulez-vous vraiment sauvegarder ces paramètres pour le générique : \"" + genericName  + "\"?";
	
	if(validateInformation.apply(null, args))
	{
		if(await askConfirmation("Sauvegarde de paramètres de générique", confirmationMessage))
		{
			document.getElementById("loadingModal").style.display = "block";
			try{
				await saveParameters.apply(null, args);
				openGenericParameters(genericId);
			}
			catch(error){
				showError("La sauvegarde des paramètres du générique a échouée", error);
			}
			finally{
				document.getElementById("loadingModal").style.display = "none";
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
	let parametersTable = document.getElementById("parametersTable");
	return [...parametersTable.getElementsByTagName("tbody")[0].getElementsByTagName("tr")].map(function(element, index){
		let quickEditSelect = element.getElementsByTagName("td")[3].getElementsByTagName("select")[0];
		return {
			"key": element.getElementsByTagName("td")[0].getElementsByTagName("input")[0].value, 
			"value": element.getElementsByTagName("td")[1].getElementsByTagName("textarea")[0].value, 
			"description": element.getElementsByTagName("td")[2].getElementsByTagName("textarea")[0].value,
			"quickEdit": parseInt(quickEditSelect.options[quickEditSelect.selectedIndex].value),
			"index": index
		};
	});
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
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/vardefaut/actions/save.php",
			"data": {"id": id, "parameters": parameters},
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
 * Adds a parameter to the parameters list
 * @this {Node} row The row after which the element must be added.
 * 
 * @return {bool} false To prevent any further behavior (automatic page scroll to top)
 */
function addParameter()
{
	this.after(newParameter());
	
	return false;
}

/**
 * Removes a parameter from the parameters list
 * @this {Node} row The row to remove.
 * 
 * @return {bool} false To prevent any further behavior (automatic page scroll to top)
 */
function removeParameter()
{
	if(this.parentNode.childElementCount > 1)
	{
		this.remove();
	}
	
	return false;
}

/**
 * Creates a new parameter row to add in the parameters list
 * @param {object} parameter An object that respects the following formatting {_key: "key", _value: value, _description: "description"}.
 * 
 * @return {Node} A new parameter row
 */
function newParameter(parameter = null)
{
	let key = ((parameter === null) ? null : parameter._key);
	let value = ((parameter === null) ? null : parameter._value);
	let description = ((parameter === null) ? null : parameter._description);
	let quickEdit = ((parameter === null) ? 0 : parameter._quick_edit);
	
	let row = document.createElement("tr");
	row.style.height = "50px";
	
	let keyInput = document.createElement("input");
	keyInput.className = "spaceEfficientText";
	keyInput.maxLength = 8;
	keyInput.value = key;
	
	let keyCell = document.createElement("td");
	keyCell.className = "firstVisibleColumn";
	keyCell.appendChild(keyInput);

	let valueTextArea = document.createElement("textarea");
	valueTextArea.className = "spaceEfficientText";
	valueTextArea.style.overflowX = "hidden"; 
	valueTextArea.style.resize = "none";
	valueTextArea.value = value;

	let valueCell = document.createElement("td");
	valueCell.style.verticalAlign = "middle";
	valueCell.appendChild(valueTextArea);
	
	let descriptionTextArea = document.createElement("textarea");
	descriptionTextArea.className = "spaceEfficientText";
	descriptionTextArea.style.overflowX = "hidden";
	descriptionTextArea.style.resize = "none";
	descriptionTextArea.value = description;
	
	let descriptionCell = document.createElement("td");;
	descriptionCell.appendChild(descriptionTextArea);

	let quickEditSelectOption0 = document.createElement("option");
	quickEditSelectOption0.value = "0";
	quickEditSelectOption0.text = "Désactivé";

	let quickEditSelectOption1 = document.createElement("option");
	quickEditSelectOption1.value = "1";
	quickEditSelectOption1.text = "Activé";

	let quickEditSelect = document.createElement("select");
	quickEditSelect.className = "spaceEfficientText";
	quickEditSelect.appendChild(quickEditSelectOption0);
	quickEditSelect.appendChild(quickEditSelectOption1);
	quickEditSelect.value = String(quickEdit);

	let quickEditCell = document.createElement("td");
	quickEditCell.appendChild(quickEditSelect);

	let addTool = document.createElement("div");
	addTool.appendChild(imageButton(ROOT_URL + "/images/add.png", "Ajouter", addParameter, [], row));

	let deleteTool = document.createElement("div");
	deleteTool.appendChild(imageButton(ROOT_URL + "/images/minus.png", "Enlever", removeParameter, [], row));
	
	let toolsDiv = document.createElement("div");
	toolsDiv.appendChild(addTool);
	toolsDiv.appendChild(deleteTool);
	
	let toolsCell = document.createElement("td");
	toolsCell.className = "lastVisibleColumn"
	toolsCell.appendChild(toolsDiv);

	row.appendChild(keyCell);
	row.appendChild(valueCell);
	row.appendChild(descriptionCell);
	row.appendChild(quickEditCell);
	row.appendChild(toolsCell);
	return row;
}