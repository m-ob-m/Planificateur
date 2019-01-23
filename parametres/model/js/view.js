/**
 * Validates user input
 * @param {int} id The id of the model (is "" if new)
 * @param {string} description The description of the Model
 * @param {int} copyParametersFrom The selected model for parameters copy
 * 
 * @return {Promise}
 */
function validateInformation(id, description, copyParametersFrom)
{
	return new Promise(function(resolve, reject){
		let err = "";
		
		if(!isPositiveInteger(id) && id !== "" && id!== null)
		{
			err += "L'identificateur unique doit être un entier positif. ";
		}
		
		if(!description.trim())
		{
			err += "Description manquante. ";
		}
		
		if(!isPositiveInteger(copyParametersFrom) && copyParametersFrom && copyParametersFrom.length !== 0)
		{
			err += "Un modèle invalide a été choisi pour la copie des paramètres. ";
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
 * Prompts user to confirm the saving of the current Model.
 * 
 * @return {Promise}
 */
function saveConfirm()
{
	let args = [$("#id").val(), $("#description").val(), $("#copyParametersFrom").val()];
	
	return validateInformation.apply(null, args)
	.catch(function(error){
		showError("La sauvegarde du modèle a échouée", error);
		return Promise.reject();
	})
	.then(function(){
		return askConfirmation("Sauvegarde de modèle", "Voulez-vous vraiment sauvegarder ce modèle?")
		.then(function(){
			$("#loadingModal").css({"display": "block"});
			return saveModel.apply(null, args)
			.catch(function(error){
				showError("La sauvegarde du modèle a échouée", error);
				return Promise.reject();
			})
			.then(function(id){
				openModel(id);
			})
			.finally(function(){
				$("#loadingModal").css({"display": "none"});
			});
		});
	})
	.catch(function(){/* Do nothing */});
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
		$.ajax({
	    	"url": "/Planificateur/parametres/model/actions/save.php",
	        "type": "POST",
	        "contentType": "application/json;charset=utf-8",
	        "data": JSON.stringify({
	        	"id": (id !== "") ? id : null, 
	        	"description": description, 
	        	"copyParametersFrom": (copyParametersFrom !== "") ? copyParametersFrom : null, 
	        	"exists": $("#id").is(":disabled")
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
 * Display a message to validate the fact that the user wants to delete this Model
 * 
 * @return {Promise}
 */
function deleteConfirm()
{
	return askConfirmation("Suppression de modèle", "Voulez-vous vraiment supprimer ce modèle?")
	.then(function(){
		$("#loadingModal").css({"display": "block"});
		return deleteModel($("#id").val())
		.then(function(){
			goToIndex();
		})
		.catch(function(error){
			showError("La suppression du modèle a échouée", error);
			return Promise.reject();
		})
		.finally(function(){
			$("#loadingModal").css({"display": "none"});
		});
	})
	.catch(function(){/* Do nothing */});
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
		$.ajax({
			"url": "/Planificateur/parametres/model/actions/delete.php",
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"data": JSON.stringify({"id": id}),
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