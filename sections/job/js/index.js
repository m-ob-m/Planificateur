"use strict";

$(document).ready(async function(){
	await getJobTypesForJob($("input#job_id").val());
	dataHasChanged(false);
});

/**
 * Retrieves JobTypes for a specified job id and builds up the interface for these JobTypes.
 * 
 * @param {int} jobId The job for which JobTypes must be retrieved
 */
async function getJobTypesForJob(jobId)
{
	try{
		let jobTypes = await retrieveJobTypes(jobId);
		if(jobTypes.length > 0)
		{
			jobTypes.map(function(jobType){
				$("div#blocksContainer").append(newJobType(jobType));
			});
		}
		else
		{
			let jobType = {
				"id": null, 
				"model": null, 
				"type": null, 
				"genericParameters": [], 
				"jobTypeParameters": [], 
				"parts": []
			};
			$("div#blocksContainer").append(newJobType(jobType));
		}
	}
	catch(error){
		showError("La récupération des JobTypes de la job a échouée", error);
	}
}

/**
 * Validates the job's information
 * @param {object} job The job
 * @return {bool} If information is valid, returns true. Otherwise, returns false.
 */
function validateInformation(job)
{
	let err = "";
	
	if(!isPositiveInteger(job.id) && job.id !== "" && job.id !== null)
	{
		err += "L'identificateur unique doit être un entier positif.\n";
	}
	
	if (!job.deliveryDate.isValid())
	{
		err += "La date de livraison doit être une date valide au format \"YYYY-MM-DD\".\n";
	}
	
	job.jobTypes.forEach(function(jobType, jobTypeIndex){
		jobType.parts.forEach(function(part, partIndex){
			if(isNaN(part.length) || part.length < 0)
			{
				err += "La longueur de la pièce " + partIndex + " du bloc " + jobTypeIndex + " doit être un nombre positif.";
			}
			
			if(isNaN(part.width) || part.width < 0)
			{
				err += "La largeur de la pièce " + partIndex + " du bloc " + jobTypeIndex + " doit être un nombre positif.";
			}
			
			if(!isPositiveInteger(part.quantityToProduce))
			{
				err += "La quantité de la pièce " + partIndex + " du bloc " + jobTypeIndex + " doit être un entier positif.";
			}
			
			if(!(new RegExp("^N$|^X$|^Y$").test(part.grain)))
			{
				err += "Le grain de la pièce " + partIndex + " du bloc " + jobTypeIndex + " est invalide.";
			}
		});
		
		Object.keys(jobType.jobTypeParameters).forEach(function(key){
			if(jobType.jobTypeParameters[key] === "" || jobType.jobTypeParameters[key] === null)
			{
				err += "La valeur du paramètre \"" + key + "\" du bloc " + jobTypeIndex + " a été laissée vide.";
			}
		});
	});
	
	// S'il y a erreur, afficher la fenêtre d'erreur
	if(err == "")
	{
		return true;
	}
	else
	{
		showError("Les informations du modèle-type ne sont pas valides", err);
		return false;
	}
}

/**
 * Proceeds with saving the job
 */
async function saveConfirm()
{
	let job = parseJobFromMetaData();
	if(validateInformation(job))
	{
		if(await askConfirmation("Sauvegarde de job", "Voulez-vous vraiment sauvegarder cette job?"))
		{
			$("#loadingModal").css({"display": "block"});
			try{
				await saveJob(job);
				dataHasChanged(false);
				if(sessionStorage.hasOwnProperty("__type") && sessionStorage.__type == "batch")
				{
					sessionStorage.hasOwnProperty("id") ? goToBatch(sessionStorage.id) : null;
				}
			}
			catch(error)
			{
				showError("La sauvegarde de la job a échouée", error);
			}
			finally{
				$("#loadingModal").css({"display": "none"});
			}
		}
	}
}