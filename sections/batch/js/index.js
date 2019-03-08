"use strict";

$(document).ready(function()
{	
	$("input#fullDay").change(function(){
		let startDate = moment.tz($("input#startDate").val(), getExpectedMomentFormat(), "America/Montreal");
		let endDate = moment.tz($("input#startDate").val(), getExpectedMomentFormat(), "America/Montreal");
		toogleCheckBox.apply(this);
		let type = ($(this).val() === "Y") ? "date" : "datetime-local";
		$("input#startDate").attr({"type": type}).val(startDate.format(getExpectedMomentFormat()));
		$("input#endDate").attr({"type": type}).val(endDate.format(getExpectedMomentFormat()));
	});
	
	initializeFields()
	.catch(function(error){/* Do nothing. */});
	
	$('input#jobNumber').keypress(
		function(key){(key.keyCode === 13) ? $("button#addJobButton").click() : false;
	});
	
	// When the status of the Batch changes, the page must reload.
	window.setInterval(
		function(){
			verifyStatus(window.sessionStorage.getItem("id"))
			.catch(function(){/* Do nothing. */});
		}
		, 10000
	);
});

/**
 * Initializes some fields on the page.
 * @return {Promise}
 */
function initializeFields()
{
	return new Promise(function(resolve, reject){
		let sessionData = window.sessionStorage;
		if(sessionData.getItem("__type") === "batch" && $("input#batchId").val() === sessionData.getItem("id"))
		{
			restoreSessionStorage()
			.then(function(){
				initializeDates();
				updateSessionStorage();
				resolve();
			})
			.catch(function(error){
				reject(error);
			});
		}
		else
		{
			// Fill the list of jobs.
			updateSessionStorage();
			getJobs($("input#batchId").val())
			.then(function(){
				return updatePannelsList();
			})
			.then(function(){
				initializeDates();
				updateSessionStorage();
				resolve();
			})
			.catch(function(error){
				reject(error);
			});
		}
	});
}

/**
 * Inserts a default value in the start date and/or end date fields if their respective value is not specified.
 */
function initializeDates()
{
	let maximumEndDate = extrapolateMaximumEndDate();
	let maximumStartDate = maximumEndDate.isValid() ? extrapolateMaximumEndDate().subtract(1, "days") : extrapolateMaximumEndDate();
	let currentStartDate = moment.tz($("input#startDate").val(), getExpectedMomentFormat(), "America/Montreal");
	let currentEndDate = moment.tz($("input#endDate").val(), getExpectedMomentFormat(), "America/Montreal");
	
	let chron = currentEndDate.isValid() && maximumStartDate.isBefore(currentEndDate) ? true : false;
	if(!currentStartDate.isValid() && maximumStartDate.isValid() && (chron || !currentEndDate.isValid()))
	{
		$("input#startDate").val(maximumStartDate.format(getExpectedMomentFormat()));
	}
	
	chron = currentStartDate.isValid() && maximumEndDate.isAfter(currentStartDate) ? true : false;
	if(!currentEndDate.isValid() && maximumEndDate.isValid() && (chron || !currentStartDate.isValid()))
	{
		$("input#endDate").val(maximumEndDate.format(getExpectedMomentFormat()));
	}
}

/**
 * Returns the maximum end date of this batch (based upon the delivery dates of its underlying jobs).
 * @return Moment The maximum end date of this batch (or an empty string).
 */
function extrapolateMaximumEndDate()
{
	let dates = [];
	$("table#orders >tbody >tr >td:nth-child(4)").each(function(){
		dates.push(moment.tz($(this).text(), getExpectedMomentFormat(), "America/Montreal"));
	});
	return (dates.length > 0) ? moment.max(dates) : moment.tz("", getExpectedMomentFormat(), "America/Montreal");
}

/**
 * If the status of the Batch has changed, reloads the page.
 * @param {int} id The id of the current Batch
 * @return {Promise}
 */
function verifyStatus(id)
{
	return new Promise(function(resolve, reject){
		if(id !== null && id !== "")
		{
			return retrieveBatchStatus(id)
			.then(function(status){
				if(status !== null && status !== window.sessionStorage.getItem("status"))
				{
					window.location.reload();
				}
				resolve();
			});
		}
		else
		{
			reject("This is a new Batch.");
		}
	});
}

/**
 * Retrieve the list of Jobs of this Batch
 * @param {int} batchId The unique identifier of this Batch
 * @return {Promise}
 */
function getJobs(batchId)
{
	return new Promise(function(resolve, reject){
		retrieveJobs(batchId)
		.then(function(jobs){
			return fillJobsList(jobs, false);
		})
		.then(function(){
			resolve();
		})
		.catch(function(error){
			showError("La récupération des jobs de la batch a échouée", error);
			reject(error);
		});
	});
}

/**
 * Validates user input
 * @param {int} id The id of the Batch (is "" if new)
 * @param {string} name The name of the Batch
 * @param {moment} startDate The planned starting date of the machining of the Batch
 * @param {moment} endDate The planned ending date of the machining of the Batch
 * @param {string} fullDay A string value representing a boolean indicating that the vatch will take a full day to machine
 * @param {string} material The material of this Batch
 * @param {string} boardSize The size of the boards used to machine this Batch
 * @param {string} status The status (E = Entered, X = In execution, P = Urging, A = Waiting, N = Non-delivered, T = Completed)
 * @param {string} comments The comments entered for this Batch (will be overwritten if an error occurs in CutQueue)
 * @param {string} jobIds An array containing the unique identifiers of the Jobs contained in this Batch
 * @return {Promise}
 */
function validateInformation(id, name, startDate, endDate, fullDay, material, boardSize, status, comments, jobIds)
{
	return new Promise(function(resolve, reject){
		let err = "";
		let tempDate;
		
		if(!isPositiveInteger(id) && id !== "" && id !== null)
		{
			err += "L'identificateur unique doit être un entier positif.\n";
		}
		
		if (!(typeof name === 'string' || name instanceof String) || (name === ""))
		{
			err += "Le nom de la batch ne peut pas être vide.\n";
		}
		
		if(!startDate.isValid())
		{
			err += "Veuillez entrer une date de début valide.\n";
		}
		
		if(!endDate.isValid())
		{
			err += "Veuillez entrer une date de fin valide.\n";
		}
		
		if(endDate.isValid() && !endDate.isAfter(startDate))
		{
			err += "La date de fin est avant la date de début.\n"
		}
		
		if(fullDay !== "Y" && fullDay !== "N")
		{
			err += "Erreur interne : L'état de la boîte à cocher \"Toute la journée\" n'est pas valide. " +
					"Modifiez l'état de la boîte et réessayez.\n";
		}
		
		if(!(typeof material === 'string' || material instanceof String) || material === "" || material === "0")
		{
			err += "Veuillez sélectionner un matériel.\n";
		}
		
		if(!(typeof boardSize === 'string' || boardSize instanceof String) || (boardSize === ""))
		{
			err += "Veuillez entrer une taille de panneau.\n";
		}
		
		if(!status.match(/^[EXPANT]{1}$/))
		{
			err += "Le statut choisi est invalide.\n";
		}
		
		if(!(typeof comments === 'string' || comments instanceof String))
		{
			err += "Les commentaires doivent être une donnée de type \"chaîne de caractère\".\n";
		}
		
		jobIds.forEach(function(element){
			if(!isPositiveInteger(element) || !element)
			{
				err += "Erreur interne : les identifiants uniques des jobs doivent être des entiers positifs.\n";
			}
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
 * Prompts user to confirm how the batch should be downloaded.
 */
function downloadConfirm()
{
	$("#downloadMsgModal").show();
}

/**
 * Prompts user to confirm the saving of the current Type.
 */
function saveConfirm()
{
	let jobIds = [];
	$("table#orders >tbody >tr >td.jobIdCell").each(function(){
		jobIds.push($(this).text());
	});
	let startDate = moment($("#startDate").val(), "YYYY-MM-DDTHH:mm:ss").tz("America/Montreal");
	let endDate = moment($("#endDate").val(), "YYYY-MM-DDTHH:mm:ss").tz("America/Montreal");
	let args = [
		$("#batchId").val(), $("#batchName").val(), startDate, endDate, $("#fullDay").val(), 
		$("#material").val(), $("#boardSize").val(), $("#status").val(), $("#comments").val(), jobIds
	];
	
	validateInformation.apply(null, args)
	.catch(function(error){
		showError("La sauvegarde de la batch a échouée", error);
		return Promise.reject();
	})
	.then(function(){
		return askConfirmation("Sauvegarde de batch", "Voulez-vous vraiment sauvegarder cette batch?")
		.then(function(){
			$("#loadingModal").css({"display": "block"});
			return saveBatch.apply(null, args)
			.catch(function(error){
				showError("La sauvegarde de la batch a échouée", error);
				return Promise.reject();
			})
			.then(function(id){
				goToBatch(id);
			})
			.finally(function(){
				$("#loadingModal").css({"display": "none"});
			});
		});
	})
	.catch(function(){/* Do nothing. */});
}

/**
 * Toogles the value of a checkbox between "Y" and "N" ("Y" ideally meaning the checkbox is checked). It is then possible to use 
 * $(this).val() to get the value of the checkbox (whereas when submitting a form, you only get a value for a checked checkbox).
 * @this {jquery} The checkbox
 */
function toogleCheckBox()
{
	$(this).val(($(this).val() === 'Y') ? 'N' : 'Y');
}

/**
 * Generates nested machining programs.
 * @param {int} [action=1] The action requested. If 1, then the project is nested. Otherwise, an archive is downloaded.
 */
function generateConfirm(action = 1)
{
	generatePrograms($("#batchId").val(), action)
	.catch(function(error){
		showError("La génération des programmes a échouée", error);
	});
}

/**
 * Downloads a Batch to CutQueue's queue.
 * @param {int} id The id of the Batch
 * @param {int} [action=1] The action requested. If 1, then the project is nested. Otherwise, an archive is downloaded.
 * @return {Promise}
 */
function generatePrograms(id, action = 1)
{
	return new Promise(function(resolve, reject){
		if(!compareWithSessionStorage())
		{
			let message = "Des différences ont été trouvées entre les dernières données sauvegardées et les données " +
			"actuelles. Veuillez sauvegarder ou recharger la page, puis réessayer."
			reject(message);
		}
		else if(!isPositiveInteger(id))
		{
			reject("Veuillez sauvegarder les données avant de télécharger le projet.");
		}
		else
		{
			$("#loadingModal").css({"display": "block"});
			return downloadBatch(id, action)
			.finally(function(){
				$("#loadingModal").css({"display": "none"});
			})
			.then(function(downloadableFile){
				if (action === 1)
				{
					window.location.reload(); //Batch was sent to CutRite for nesting.
					
				}
				else
				{
					downloadFile(downloadableFile.url, downloadableFile.name); // Local copy of the individual programs.
				}
			})
			.catch(function(error){
				reject(error);
			});
		}
	});
}

/**
 * Prompts user to confirm the deletion of the current Type.
 * @return {Promise}
 */
function deleteConfirm()
{
	return askConfirmation("Suppression de batch", "Voulez-vous vraiment supprimer cette batch?")
	.then(function(){
		$("#loadingModal").css({"display": "block"});
		return deleteBatch($("#batchId").val())
		.catch(function(error){
			showError("La suppression de la batch a échouée", error);
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
 * Retrieves the list of possible pannels for a given material id
 * @return {Promise}
 */
function updatePannelsList()
{
	return new Promise(function(resolve, reject){
		let materialId = $("select#material").val();
		if(isPositiveInteger(materialId, false))
		{
			retrievePannels($("select#material").val())
			.then(function(pannelCodes){
				let value = $("select#boardSize").val();
				
				$("select#boardSize").empty().append($("<option></option>").text("").val(""));
				
				pannelCodes.map(function(pannelCode){
					$("select#boardSize").append($("<option></option>").text(pannelCode).val(pannelCode));
				});
				
				$("select#boardSize").val(($("select#boardSize >option[value='" + value + "']").length > 0) ? value : "");
			})
			.then(function(){
				resolve();
			})
			.catch(function(error){
				showError("Échec de la récupération de la liste des panneaux disponibles", error);
				reject();
			})
		}
		else
		{
			resolve();
		}
	});
}

/**
 * Get the expected format for moments start date and end date.
 * @return {string} The format string
 */
function getExpectedMomentFormat()
{
	return ($("input#fullDay").val() === "Y") ? "YYYY-MM-DD" : "YYYY-MM-DDTHH:mm:ss";
}

/**
 * Opens the machining programs viewer for a Batch.
 * @param {int} id The id of the Batch
 */
function viewPrograms(batchId)
{
	window.open("/Planificateur/sections/visualiseur/index.php?id=" + batchId, "_blank");
}

/**
 * Opens a job
 * @param {object} event An event generated by a click on the job's summary row.
 */
function openJobEvent(event)
{
	updateSessionStorage();
	let batchId = (event.data.batchId !== null) ? event.data.batchId : -1;
	window.location.assign("/Planificateur/sections/job/index.php?jobId=" + event.data.jobId + "&batchId=" + batchId);
}