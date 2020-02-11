"use strict";

/**
 * Validates user input
 * @param {int} id The id of the model (is "" if new)
 * @param {string} description The description of the Model
 * @param {int} copyParametersFrom The selected model for parameters copy
 * 
 * @return {bool} If information is valid, returns true. Otherwise, returns false.
 */
function validateInformation(id, description, copyParametersFrom)
{
		let err = "";
		
		if(!isPositiveInteger(id, true, true) && id !== "" && id!== null)
		{
			err += "L'identificateur unique doit être un entier positif. ";
		}
		
		if(!description.trim())
		{
			err += "Description manquante. ";
		}
		
		if(!isPositiveInteger(copyParametersFrom, true, true) && copyParametersFrom && copyParametersFrom.length !== 0)
		{
			err += "Un modèle invalide a été choisi pour la copie des paramètres. ";
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
 * Prompts user to confirm the saving of the current Model.
 */
async function saveConfirm()
{
	let copyParametersFromSelect = document.getElementById("copyParametersFrom");
	let selectedIndex = (copyParametersFromSelect !== null) ? copyParametersFromSelect.selectedIndex : null;
	let args = [
		document.getElementById("id").value, 
		document.getElementById("description").value, 
		(selectedIndex !== null) ? copyParametersFromSelect.options[selectedIndex].value : null
	];
	
	if(validateInformation.apply(null, args))
	{
		if(await askConfirmation("Sauvegarde de modèle", "Voulez-vous vraiment sauvegarder ce modèle?"))
		{
			document.getElementById("loadingModal").style.display = "block";
			try{
				let id = await saveModel.apply(null, args);
				openModel(id);
			}
			catch(error)
			{
				showError("La sauvegarde du modèle a échouée", error);
			}
			finally{
				document.getElementById("loadingModal").style.display = "none";
			}
		}
	}
}

/**
 * Saves a Model into the database
 * @param {int} id The id of the Model (is "" if new)
 * @param {string} description A short description of the Model
 * @param {int} copyParametersFrom The model from which parameters should be fetched when initializing a new model (is "" if none)
 * 
 * @return {Promise}
 */
function saveModel(id, description, copyParametersFrom)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"url": ROOT_URL + "/parametres/model/actions/save.php",
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"data": {
				"id": (id !== "") ? id : null, 
				"description": description, 
				"copyParametersFrom": (copyParametersFrom !== "") ? copyParametersFrom : null
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
 * Display a message to validate the fact that the user wants to delete this Model
 */
async function deleteConfirm()
{
	if(await askConfirmation("Suppression de modèle", "Voulez-vous vraiment supprimer ce modèle?"))
	{
		document.getElementById("loadingModal").style.display = "block";
		try{
			await deleteModel(document.getElementById("id").value);
			goToIndex();
		}
		catch(error)
		{
			showError("La suppression du modèle a échouée", error);
		}
		finally{
			document.getElementById("loadingModal").style.display = "none";
		}
	}
}

/**
 * Deletes a Model from the database
 * @param {int} id The id of the Model
 * 
 * @return {Promise}
 */
function deleteModel(id)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"url": ROOT_URL + "/parametres/model/actions/delete.php",
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
 * Returns to index page.
 */
function goToIndex()
{
	window.location.assign("index.php");
}