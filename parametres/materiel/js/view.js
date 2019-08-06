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
			
	if(!isPositiveInteger(id, true, true) && id !== "" && id!== null)
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
		document.getElementById("loadingModal").style.display = "block";
		try{
			await deleteMaterial(document.getElementById("id_materiel").value);
			goToIndex();
		}
		catch(error){
			showError("La suppression du matériel a échouée", error);
		}
		finally{
			document.getElementById("loadingModal").style.display = "none";
		}
	}
}

/**
 * Prompts user to confirm the saving of the current material.
 */
async function saveConfirm()
{
	let hasGrain = null;
	for (let item of document.getElementsByName("has_grain"))
	{
		if(item.checked)
		{
			hasGrain = item.value;
			break;
		}
	}

	let isMDF = null;
	for (let item of document.getElementsByName("est_mdf"))
	{
		if(item.checked)
		{
			isMDF = item.value;
			break;
		}
	}

	let args = [
		document.getElementById("id_materiel").value, 
		document.getElementById("description").value, 
		document.getElementById("codeSIA").value, 
		document.getElementById("codeCutRite").value, 
		document.getElementById("epaisseur").value, 
		document.getElementById("essence").value, 
		hasGrain, 
		isMDF
	];
	
	if(validateInformation.apply(null, args))
	{
		if(await askConfirmation("Sauvegarde de matériel", "Voulez-vous vraiment sauvegarder ce matériel?"))
		{
			document.getElementById("loadingModal").style.display = "block";
			try{
				let id = await saveMaterial.apply(null, args);
				openMaterial(id);
			}
			catch(error){
				showError("La sauvegarde du matériel a échouée", error);
			}
			finally{
				document.getElementById("loadingModal").style.display = "none";
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
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/materiel/actions/delete.php",
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
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/materiel/actions/save.php",
			"data": {
				"id": ((id === "") ? null : id), 
				"description": description, 
				"siaCode": siaCode, 
				"cutRiteCode": cutRiteCode, 
				"thickness": thickness, 
				"woodType": woodType, 
				"grain": grain, 
				"isMDF": isMDF
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
 * Returns to index page.
 */
function goToIndex()
{
	window.location.assign("index.php");
}