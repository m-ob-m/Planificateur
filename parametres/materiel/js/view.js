"use strict";

/**
 * Validates information entered by user
 * @param {int} id The id of the material.
 * @param {string} description The description of the description
 * @param {string} siaCode The code of the material in SIA
 * @param {string} cutRiteCode The code of the material in CutRite
 * @param {string} thickness The thickness of the material
 * @param {string} woodType The type of wood the material is made of 
 * @param {boolean} grain True if the material has a grain
 * @param {boolean} isMDF True if the wood type is mdf
 * 
 * @return {Promise}
 */
function validateInformation(id, description, siaCode, cutRiteCode, thickness, woodType, grain, isMDF){
	return new Promise(
		function(resolve, reject)
		{
			let err = "";
			
			if(!isPositiveInteger(id) && id !== "" && id!== null)
			{
				err += "L'identificateur unique doit être un entier positif. ";
			}
			
			if(!description.trim())
			{
				err += "Description manquante. ";
			}
			
			if(!siaCode.trim())
			{
				err += "Code SIA manquant. ";
			}
			
			if(!cutRiteCode.trim())
			{
				err += "Code CutRite manquant. ";
			}
			
			if(!thickness.trim())
			{
				err += "Épaisseur manquante. ";
			}

			if(!woodType.trim())
			{
				err += "Essence manquante. ";
			}
			
			if(grain !== "Y" && grain !== "N")
			{
				err += "Présence de grain non validée. ";
			}
			
			if(isMDF !== "Y" && isMDF !== "N")
			{
				err += "Paramètre est_mdf sans valeur. ";
			}

			if(err != "")
			{
				reject(err);
			}
			else
			{
				resolve();
			}
		}	
	);	
} 

/**
 * Prompts user to confirm deletion of the current material.
 * 
 * @return {Promise}
 */
function deleteConfirm()
{
	let args = [$("#id_materiel").val()];
	
	return askConfirmation("Suppression de matériel", "Voulez-vous vraiment supprimer ce matériel?")
	.then(function(){
		$("#loadingModal").css({"display": "block"});
		return deleteMaterial.apply(null, args)
		.catch(function(error){
			showError("La suppression du matériel a échouée", error);
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
 * Prompts user to confirm the saving of the current material.
 * 
 * @return {Promise}
 */
function saveConfirm()
{
	let args = [
		$("#id_materiel").val(), 
		$("#description").val(), 
		$("#codeSIA").val(), 
		$("#codeCutRite").val(), 
		$("#epaisseur").val(), 
		$("#essence").val(), 
		$("input[name=has_grain]:checked").val(), 
		$("input[name=est_mdf]:checked").val()
	];
	
	return validateInformation.apply(null, args)
	.catch(function(error){
		showError("La sauvegarde du matériel a échouée", error);
		return Promise.reject();
	})
	.then(function(){
		return askConfirmation("Sauvegarde de matériel", "Voulez-vous vraiment sauvegarder ce matériel?")
		.then(function(){
			$("#loadingModal").css({"display": "block"});
			return saveMaterial.apply(null, args)
			.catch(function(error){
				showError("La sauvegarde du matériel a échouée", error);
				return Promise.reject();
			})
			.then(function(id){
				window.location.assign(
					window.location.protocol + '//' + window.location.host + window.location.pathname + "?id=" + id
				);
			})
			.finally(function(){
				$("#loadingModal").css({"display": "none"});
			});
		});
	})
	.catch(function(){/* Do nothing. */});
}

/**
 * Deletes a material.
 * @param {int} id The id of the material to delete.
 * 
 * @return {Promise}
 */
function deleteMaterial(id)
{	
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/parametres/materiel/actions/delete.php",
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
 * Saves a material.
 * @param {int} id The id of the material.
 * @param {string} description The description of the description
 * @param {string} siaCode The code of the material in SIA
 * @param {string} cutRiteCode The code of the material in CutRite
 * @param {string} thickness The thickness of the material
 * @param {string} woodType The type of wood the material is made of 
 * @param {boolean} grain True if the material has a grain
 * @param {boolean} mdf True if the wood type is mdf
 * 
 * @return {Promise}
 */
function saveMaterial(id, description, siaCode, cutRiteCode, thickness, woodType, grain, isMDF)
{	
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/parametres/materiel/actions/save.php",
			"data": JSON.stringify({
				"id": ((id === "") ? null : id), 
				"description": description, 
				"siaCode": siaCode, 
				"cutRiteCode": cutRiteCode, 
				"thickness": thickness, 
				"woodType": woodType, 
				"grain": grain, 
				"isMDF": isMDF
			}),
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
 * Returns to index page.
 */
function goToIndex()
{
	window.location.assign("index.php");
}