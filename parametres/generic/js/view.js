"use strict";

/**
 * Validates user input
 * @param {int} id The id of the generic (is "" if new)
 * @param {string} filename The name of the file associated with this generic
 * @param {string} description A short description of the generic's role
 * @param {int} copyParametersFrom The generic from which parameters should be fetched when initializing a new generic 
 * 								   (is "" if none)
 * @param {int} heightParameter Identifies wheter LPX is the Height of the part or its width.
 * 
 * @return {Promise}
 */
function validateInformation(id, filename, description, heightParameter, copyParametersFrom)
{
	return new Promise(function(resolve, reject){
		let err = "";
		
		if(!isPositiveInteger(id) && id !== "" && id!== null)
		{
			err += "L'identificateur unique doit être un entier positif. ";
		}
		
		if(!(new RegExp("^[a-z0-9_]+\\.mpr$")).test(filename))
		{
			err += "Le nom de fichier doit être du format \"nomfichier.mpr\". ";
		}
		
		if(heightParameter.length === 0 || !heightParameter.trim())
		{
			err += "Un paramètre valide doit être sélectionné pour identifier la hauteur des pièces. ";
		}
		
		if(!description.trim())
		{
			err += "Description manquante. ";
		}
		
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
 * Prompts user to confirm the saving of the current Generic.
 * 
 * return {Promise}
 */
function saveConfirm()
{
	let args = [
		$("#id").val(), 
		$("#filename").val(), 
		$("#description").val(), 
		$("#heightParameter").val(), 
		$("#copyParametersFrom").val()
	];
	
	return validateInformation.apply(null, args)
	.catch(function(error){
		showError("La sauvegarde du générique a échouée", error);
		return Promise.reject();
	})
	.then(function(){
		return askConfirmation("Sauvegarde de générique", "Voulez-vous vraiment sauvegarder ce générique?")
		.then(function(){
			$("#loadingModal").css({"display": "block"});
			return saveGeneric.apply(null, args)
			.catch(function(error){
				showError("La sauvegarde du générique a échouée", error);
				return Promise.reject();
			})
			.then(function(id){
				openGeneric(id);
			})
			.finally(function(){
				$("#loadingModal").css({"display": "none"});
			});
		});
	})
	.catch(function(){/* Do nothing */});
}

/**
 * Saves a Generic into the database
 * @param {int} id The id of the generic (is "" if new)
 * @param {string} filename The name of the file associated with this generic
 * @param {string} description A short description of the generic's role
 * @param {int} height If 0, then "LPY" is the height of the parts. If 1, then "LPX" is the height of the parts
 * @param {int} copyParametersFrom The generic from which parameters should be fetched when initializing a new generic 
 * 								   (is "" if none)
 * 
 * @return {Promise}
 */
function saveGeneric(id, filename, description, heightParameter, copyParametersFrom)
{
	return new Promise(function(resolve, reject){
		$.ajax({
	    	"url": "/Planificateur/parametres/generic/actions/save.php",
	        "type": "POST",
	        "contentType": "application/json;charset=utf-8",
	        "data": JSON.stringify({
	        	"id": ((id !== "") ? id : null), 
	        	"filename": filename, 
	        	"description": description,
	        	"heightParameter": heightParameter,
	        	"copyParametersFrom": ((copyParametersFrom !== "") ? copyParametersFrom : null)
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
 * Display a message to validate the fact that the user wants to delete this generic
 * 
 * @return {Promise}
 */
function deleteConfirm()
{
	let args = [$("#id").val()];
	return askConfirmation("Suppression de générique", "Voulez-vous vraiment supprimer ce générique?")
	.then(function(){
		$("#loadingModal").css({"display": "block"});
		return deleteGeneric.apply(null, args)
		.catch(function(error){
			showError("La suppression du générique a échouée", error);
			return Promise.reject();
		})
		.then(function(){
			goToIndex();
		})
		.finally(function(){
			$("#loadingModal").css({"display": "none"});
		});
	})
	.catch(function(){/* Do nothing */});
}

/**
 * Deletes this Generic from the database
 * @param {int} id The id of the Generic to delete
 * 
 * @return {Promise}
 */
function deleteGeneric(id)
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"url": "/Planificateur/parametres/generic/actions/delete.php",
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"data": JSON.stringify({"id": id}),
			"dataType": "json",
			"async": true,
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