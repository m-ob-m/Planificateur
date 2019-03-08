"use strict";

$(document).ready(function(){
	getJobTypesForJob($("input#job_id").val())
	.then(function(){
		dataHasChanged(false);
	})
	.catch(function(){/* Do nothing. */});
});

/**
 * Retrieves JobTypes for a specified job id and builds up the interface for these JobTypes.
 * 
 * @param {int} jobId The job for which JobTypes must be retrieved
 * @return {Promise}
 */
function getJobTypesForJob(jobId)
{
	return retrieveJobTypes(jobId)
	.then(function(jobTypes){
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
	})
	.catch(function(error){
		showError("La récupération des JobTypes de la job a échouée", error);
		reject(error);
	});
}

/**
 * Validates the job's information
 * @param {object} job The job
 * @return {Promise}
 */
function validateInformation(job)
{
	return new Promise(function(resolve, reject){
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
		if(err !== "")
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
 * Proceeds with saving the job
 */
function saveConfirm()
{
	let job = parseJobFromMetaData();
	validateInformation(job)
	.catch(function(error){
		showError("La sauvegarde de la batch a échouée", error);
		return Promise.reject();
	})
	.then(function(){
		return askConfirmation("Sauvegarde de job", "Voulez-vous vraiment sauvegarder cette job?")
		.then(function(){
			$("#loadingModal").css({"display": "block"});
			return saveJob(job)
			.catch(function(error){
				showError("La sauvegarde de la job a échouée", error);
				return Promise.reject();
			})
			.then(function(id){
				goToJob(id, $("input#batch_id").val());
			})
			.finally(function(){
				dataHasChanged(false);
				$("#loadingModal").css({"display": "none"});
			});
		});
	})
	.catch(function(){/* Do nothing. */});
}