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
 * @return {bool} If information is valid, returns true. Otherwise, returns false.
 */
function validateInformation(id, description, siaCode, cutRiteCode, thickness, woodType, grain, isMDF){
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

	if(err == "")
	{
		return true;	
	}
	else
	{
		showError("Les informations du matériel ne sont pas valides", err);
		return false;
	}
}

/**
 * Prompts user to confirm deletion of the current material.
 */
async function deleteConfirm()
{
	let args = [];
	
	if(await askConfirmation("Suppression de matériel", "Voulez-vous vraiment supprimer ce matériel?"))
	{
		$("#loadingModal").css({"display": "block"});
		try{
			await deleteMaterial($("#id_materiel").val());
			goToIndex();
		}
		catch(error){
			showError("La suppression du matériel a échouée", error);
		}
		finally{
			$("#loadingModal").css({"display": "none"});
		}
	}
}

/**
 * Prompts user to confirm the saving of the current material.
 */
async function saveConfirm()
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
	
	if(validateInformation.apply(null, args))
	{
		if(await askConfirmation("Sauvegarde de matériel", "Voulez-vous vraiment sauvegarder ce matériel?"))
		{
			$("#loadingModal").css({"display": "block"});
			try{
				let id = await saveMaterial.apply(null, args);
				openMaterial(id);
			}
			catch(error){
				showError("La sauvegarde du matériel a échouée", error);
			}
			finally{
				$("#loadingModal").css({"display": "none"});
			}
		}
	}
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
			"url": ROOT_URL + "/parametres/materiel/actions/delete.php",
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
			"url": ROOT_URL + "/parametres/materiel/actions/save.php",
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