"use strict";

docReady(
	function()
	{
		updateCopyParametersFrom();
	}
);

/**
 * Validates user input
 * @param {int} id The id of the Type (is "" if new)
 * @param {int} siaNumber The SIA # associated to this Type
 * @param {string} description The description of the Type
 * @param {int} genericId The id of the Generic associated to this Type
 * @param {int} copyParametersFrom The selected type for parameters copy
 * 
 * @return {bool} If information is valid, returns true. Otherwise, returns false.
 */
function validateInformation(id, siaNumber, description, genericId, copyParametersFrom)
{
	let err = "";
	
	if(!isPositiveInteger(id, true, true) && id !== "" && id !== null)
	{
		err += "L'identificateur unique doit être un entier positif. ";
	}
	
	if(!isPositiveInteger(siaNumber, true, false))
	{
		err += "Le numéro d'importation SIA doit être un entier positif. ";
	}
	
	if(!description.trim())
	{
		err += "Description manquante. ";
	}
	
	if(!isPositiveInteger(genericId, true, true))
	{
		err += "Le générique choisi présente un problème. ";
	}
	
	if(!isPositiveInteger(copyParametersFrom, true, true) && copyParametersFrom && copyParametersFrom.length !== 0)
	{
		err += "Un type invalide a été choisi pour la copie des paramètres. ";
	}
	
	// S'il y a erreur, afficher la fenêtre d'erreur
	if(err == "")
	{
		return true;
	}
	else
	{
		showError("Les informations du type ne sont pas valides", err);
		return false;
	}
}

/**
 * Prompts user to confirm the saving of the current Type.
 */
async function saveConfirm()
{
	let copyParametersFromSelect = document.getElementById("copyParametersFrom");
	let selectedIndex = (copyParametersFromSelect !== null) ? copyParametersFromSelect.selectedIndex : null;
	let args = [
		document.getElementById("id").value, 
		document.getElementById("importNo").value, 
		document.getElementById("description").value, 
		document.getElementById("generic").value, 
		(selectedIndex !== null) ? copyParametersFromSelect.options[selectedIndex].value : null
	];
	
	if(validateInformation.apply(null, args))
	{
		if(await askConfirmation("Sauvegarde de type", "Voulez-vous vraiment sauvegarder ce type?"))
		{
			document.getElementById("loadingModal").style.display = "block";
			try{
				let id = await saveType.apply(null, args);
				openType(id);
			}
			catch(error)
			{
				showError("La sauvegarde du type a échouée", error);
			}
			finally{
				document.getElementById("loadingModal").style.display = "none";
			}
		}
	}
}

/**
 * Saves a Type into the database
 * @param {int} id The id of the Type (is "" if new)
 * @param {int} importNo The SIA # associated to this Type
 * @param {string} description The description of the Type
 * @param {int} genericId The id of the Generic associated to this Type
 * @param {int} copyParametersFrom The type from which parameters should be fetched when initializing a new type (is "" if none)
 * 
 * @return {Promise}
 */
function saveType(id, importNo, description, genericId, copyParametersFrom)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"url": ROOT_URL + "/parametres/type/actions/save.php",
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"data": {
			"id": (id !== "") ? id : null, 
			"importNo": (importNo !== "") ? importNo : null, 
			"description": (description !== "") ? description : null, 
			"genericId": (genericId !== "") ? genericId : null, 
			"copyParametersFrom": copyParametersFrom
			},
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
 * Display a message to validate the fact that the user wants to delete this Type
 */
async function deleteConfirm()
{
	if(await askConfirmation("Suppression de type", "Voulez-vous vraiment supprimer ce type?"))
	{
		document.getElementById("loadingModal").style.display = "block";
		try{
			await deleteType(document.getElementById("id").value);
			goToIndex();
		}
		catch(error){
			showError("La suppression du type a échouée", error);
		}
		finally{
			document.getElementById("loadingModal").style.display = "none";
		}
	}
}

/**
 * Deletes this Type from the database
 * @param {int} id The id of the Type to delete
 * 
 * @return {Promise}
 */
function deleteType(id)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"url": ROOT_URL + "/parametres/type/actions/delete.php",
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
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
 * Updates the parameters copy select box available choices according to the provided Generic unique identifier.
 */
async function updateCopyParametersFrom()
{
	let copyParametersFromSelect = document.getElementById("copyParametersFrom"); 
	if(copyParametersFromSelect !== null)
	{
		while(copyParametersFromSelect.childElementCount > 0)
		{
			copyParametersFromSelect.firstElementChild.remove();
		}

		let noneOption = document.createElement("option");
		noneOption.value = "";
		noneOption.text = "Aucun";
		copyParametersFromSelect.appendChild(noneOption);

		try{
			let generic = document.getElementById("generic").value;
			(await getTypesWithGeneric(generic)).forEach(function(element){
				let option = document.createElement("option");
				option.value = element._id;
				option.text = element._description;
				copyParametersFromSelect.appendChild(option);
			});
		}
		catch(error){
			showError("L'obtention des types concernés par ce générique a échouée", error);
		}
	}
}

/**
 * Retrieves the Types that have the specified Generic
 * 
 * @param {int} id The id of the specified Generic
 * 
 * @return {Promise}
 */
function getTypesWithGeneric(id)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"url": ROOT_URL + "/parametres/type/actions/getTypesByGeneric.php",
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"data": {"genericId": id},
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
 * Returns to index page.
 */
function goToIndex()
{
	window.location.assign("index.php");
}