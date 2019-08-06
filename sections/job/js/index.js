"use strict";

docReady(async function(){
	let job = await retrieveJob(document.getElementById("job_id").value)
	.catch(function(error){
		showError("La récupération des données de la job a échouée.", error);
	})
	await layoutJob(job)
	.catch(function(error){
		showError("L'affichage' des données de la job a échoué.", error);
	});
	hasChanged(false);
});

/**
 * If status is a boolean, sets the status of hasChanged. Otherwise, returns the status of hasChanged.
 * @param {Boolean|null} [status=null] The new status of hasChanged when setting the status, null when getting the status of hasChanged.
 * @return {Boolean|null} Null when setting the status of hasChanged, the staus of hasChanged when getting the status
 */
function hasChanged(status = null)
{
	if ([true, false].includes(status))
	{
		hasChanged.status = status;
		return null;
	}
	else
	{
		hasChanged.status = typeof hasChanged.status === "undefined" ? false : hasChanged.status;
		return hasChanged.status;
	}
}

/**
 * Builds up the display for a job.
 * 
 * @this {object} job The job to display
 */
async function layoutJob(job){
	if(job._jobTypes.length > 0)
	{
		await Promise.all(
			job._jobTypes.map(
				async function(jobType){
					let jobTypeBlock = await JobTypeBlock.build(jobType, () => {hasChanged(true);});
					document.getElementById("blocksContainer").appendChild(jobTypeBlock.getLayout());
				}
			)
		);
	}
	else
	{
		let jobTypeBlock = await JobTypeBlock.build(null, hasChanged);
		document.getElementById("blocksContainer").appendChild(jobTypeBlock.getLayout());
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
	
	if(!isPositiveInteger(job.id, true, true))
	{
		err += "L'identificateur unique doit être un entier positif.\n";
	}
	
	if (!job.deliveryDate.isValid())
	{
		err += "La date de livraison doit être une date valide au format \"YYYY-MM-DD\".\n";
	}
	
	job.jobTypes.forEach(
		function(jobType, jobTypeIndex){
			jobType.parts.forEach(
				function(part, partIndex){
					if(!isNumber(part.length, true) || Number(part.length) < 0)
					{
						err += "La longueur de la pièce " + partIndex + " du bloc " + jobTypeIndex + " doit être un nombre positif.";
					}
					
					if(!isNumber(part.width, true) || Number(part.width) < 0)
					{
						err += "La largeur de la pièce " + partIndex + " du bloc " + jobTypeIndex + " doit être un nombre positif.";
					}
					
					if(!isPositiveInteger(part.quantity, true, false))
					{
						err += "La quantité de la pièce " + partIndex + " du bloc " + jobTypeIndex + " doit être un entier positif.";
					}
					
					if(!(new RegExp("^N$|^X$|^Y$").test(part.grain)))
					{
						err += "Le grain de la pièce " + partIndex + " du bloc " + jobTypeIndex + " est invalide.";
					}
				}
			);
			Object.keys(jobType.parameters).forEach(
				function(key){
					if(jobType.parameters[key] === "" || jobType.parameters[key] === null)
					{
						err += "La valeur du paramètre \"" + key + "\" du bloc " + jobTypeIndex + " a été laissée vide.";
					}
				}
			);
		}
	);
	
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
	let job = await parseJob();
	if(validateInformation(job))
	{
		
		if(await askConfirmation("Sauvegarde de job", "Voulez-vous vraiment sauvegarder cette job?"))
		{
			document.getElementById("loadingModal").style.display = "block";
			try{
				await saveJob(job);
				hasChanged(false);
				if(sessionStorage.hasOwnProperty("batch"))
				{
					JSON.parse(sessionStorage.batch).hasOwnProperty("id") ? goToBatch(JSON.parse(sessionStorage.batch).id) : null;
				}
			}
			catch(error)
			{
				showError("La sauvegarde de la job a échouée", error);
			}
			finally{
				document.getElementById("loadingModal").style.display = "none";
			}
		}
	}
}