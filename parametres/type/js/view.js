"use strict";

$(
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
	
	if(!isPositiveInteger(id) && id !== "" && id !== null)
	{
		err += "L'identificateur unique doit être un entier positif. ";
	}
	
	if(!isPositiveInteger(siaNumber))
	{
		err += "Le numéro d'importation SIA doit être un entier positif. ";
	}
	
	if(!description.trim())
	{
		err += "Description manquante. ";
	}
	
	if(!isPositiveInteger(genericId))
	{
		err += "Le générique choisi présente un problème. ";
	}
	
	if(!isPositiveInteger(copyParametersFrom) && copyParametersFrom && copyParametersFrom.length !== 0)
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
	let args = [
		$("#id").text(), 
		$("#importNo").val(), 
		$("#description").val(), 
		$("#generic").val(), 
		$("#copyParametersFrom").val()
	];
	
	if(validateInformation.apply(null, args))
	{
		if(await askConfirmation("Sauvegarde de type", "Voulez-vous vraiment sauvegarder ce type?"))
		{
			$("#loadingModal").css({"display": "block"});
			try{
				let id = await saveType.apply(null, args);
				openType(id);
			}
			catch(error)
			{
				showError("La sauvegarde du type a échouée", error);
			}
			finally{
				$("#loadingModal").css({"display": "none"});
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
		$.ajax({
	    	"url": "/Planificateur/parametres/type/actions/save.php",
	        "type": "POST",
	        "contentType": "application/json;charset=utf-8",
	        "data": JSON.stringify({
	    		"id": (id !== "") ? id : null, 
	    		"importNo": (importNo !== "") ? importNo : null, 
	    		"description": (description !== "") ? description : null, 
	    		"genericId": (genericId !== "") ? genericId : null, 
	    		"copyParametersFrom": copyParametersFrom
	        }),
	        "dataType": 'json',
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
 * Display a message to validate the fact that the user wants to delete this Type
 */
async function deleteConfirm()
{
	if(await askConfirmation("Suppression de type", "Voulez-vous vraiment supprimer ce type?"))
	{
		$("#loadingModal").css({"display": "block"});
		try{
			await deleteType($("#id").text());
			goToIndex();
		}
		catch(error){
			showError("La suppression du type a échouée", error);
		}
		finally{
			$("#loadingModal").css({"display": "none"});
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
		$.ajax({
			"url": "/Planificateur/parametres/type/actions/delete.php",
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"data": JSON.stringify({"id": id}),
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
 * Updates the parameters copy select box available choices according to the provided Generic unique identifier.
 */
async function updateCopyParametersFrom()
{
	if($("#copyParametersFrom").length)
	{
		$("#copyParametersFrom").empty();
		try{
			$("#copyParametersFrom").append($("<option></option>").val("").text("Aucun")).val("");
			let types = await getTypesWithGeneric($("#generic").val());
			$(types).each(function(){
				$("#copyParametersFrom").append($("<option></option>").val(this._id).text(this._description));
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
		$.ajax({
			"url": "/Planificateur/parametres/type/actions/getTypesByGeneric.php",
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"data": {"genericId": id},
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
 * Returns to index page.
 */
function goToIndex()
{
	window.location.assign("index.php");
}