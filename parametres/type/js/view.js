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
 * @return {Promise}
 */
function validateInformation(id, siaNumber, description, genericId, copyParametersFrom)
{
	return new Promise(function(resolve, reject){
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
		if(err != "")
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
 * Prompts user to confirm the saving of the current Type.
 * 
 * @return {Promise}
 */
function saveConfirm()
{
	let args = [
		$("#id").text(), 
		$("#importNo").val(), 
		$("#description").val(), 
		$("#generic").val(), 
		$("#copyParametersFrom").val()
	];
	
	return validateInformation.apply(null, args)
	.catch(function(error){
		showError("La sauvegarde du type a échouée", error);
		return Promise.reject();
	})
	.then(function(){
		return askConfirmation("Sauvegarde de type", "Voulez-vous vraiment sauvegarder ce type?")
		.then(function(){
			$("#loadingModal").css({"display": "block"});
			return saveType.apply(null, args)
			.catch(function(error){
				showError("La sauvegarde du type a échouée", error);
				return Promise.reject();
			})
			.then(function(id){
				openType(id);
			})
			.finally(function(){
				$("#loadingModal").css({"display": "none"});
			});
		});
	})
	.catch(function(){/* Do nothing. */});
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
 * 
 * @return {Promise}
 */
function deleteConfirm()
{
	return askConfirmation("Suppression de type", "Voulez-vous vraiment supprimer ce type?")
	.then(function(){
		$("#loadingModal").css({"display": "block"});
		return deleteType($("#id").text())
		.catch(function(error){
			showError("La suppression du type a échouée", error);
			return Promise.reject();
		})
		.then(function(){
			goToIndex();
		})
		.finally(function(){
			$("#loadingModal").css({"display": "none"});
		});
	})
	.catch(function(){/* Do nothing. */});
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
 * 
 * @return {Promise}
 * 
 */
function updateCopyParametersFrom()
{
	if($("#copyParametersFrom").length)
	{
		$("#copyParametersFrom").empty();
		return getTypesWithGeneric($("#generic").val())
		.then(function(types){
			$("#copyParametersFrom").append($("<option></option>").val("").text("Aucun")).val("");
			$(types).each(function(){
				$("#copyParametersFrom").append($("<option></option>").val(this._id).text(this._description));
			});
		})
		.catch(function(error){
			showError("L'obtention des types concernés par ce générique a échouée", error);
		});
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