"use strict";

$(document).ready(async function()
{	
	$("input#fullDay").change(function(){
		let startDate = moment.tz($("input#startDate").val(), getExpectedMomentFormat(), "America/Montreal");
		let endDate = moment.tz($("input#startDate").val(), getExpectedMomentFormat(), "America/Montreal");
		toogleCheckBox.apply(this);
		let type = ($(this).val() === "Y") ? "date" : "datetime-local";
		$("input#startDate").attr({"type": type}).val(startDate.format(getExpectedMomentFormat()));
		$("input#endDate").attr({"type": type}).val(endDate.format(getExpectedMomentFormat()));
	});
	
	await initializeFields();
	
	$('input#batchName').keyup(
		function(key){(key.keyCode === 13) ? $('input#jobNumber').focus() : false;
	});

	$('input#jobNumber').keyup(
		function(key){(key.keyCode === 13) ? $("button#addJobButton").click() : false;
	});
	
	// When the status of the Batch changes, the page must reload.
	window.setInterval(
		async function(){
			let id = $("input#batchId").val();
			if(id !== null && id !== "")
			{
				await verifyStatus(id);
			}
		}, 
		10000
	);
});

/**
 * Initializes some fields on the page.
 */
async function initializeFields()
{
	let sessionData = window.sessionStorage;
	if(sessionData.getItem("__type") === "batch" && $("input#batchId").val() === sessionData.getItem("id"))
	{
		try{
			await restoreSessionStorage();
			initializeDates();
			updateSessionStorage();
		}
		catch(error){
			showError("La restauration des données de session a échouée", error);
		};
	}
	else
	{
		// Fill the list of jobs.
		try{
			/* IMPORTANT!!! Pour pouvoir insérer les jobs dans le tableau sans message d'erreur pour job déjà liée à une batch, 
			on met à jour les données de session afin qu'elles contiennent le nom de la batch au minimum. Par la suite, on les 
			remet à jour à la fin de l'initialisation de la page.*/
			updateSessionStorage();
			await getJobs($("input#batchId").val());
			updatePannelsList();
			initializeDates();
			updateSessionStorage();
		}
		catch(error){
			showError("La restauration des données pour cett batch a échouée", error);
		}
	}
}

/**
 * Inserts a default value in the start date and/or end date fields if their respective value is not specified.
 */
function initializeDates()
{
	let maximumEndDate = extrapolateMaxEndDate();
	let maximumStartDate = maximumEndDate.isValid() ? extrapolateMaxEndDate().subtract(1, "days") : extrapolateMaxEndDate();
	let currentStartDate = moment.tz($("input#startDate").val(), getExpectedMomentFormat(), "America/Montreal");
	let currentEndDate = moment.tz($("input#endDate").val(), getExpectedMomentFormat(), "America/Montreal");
	
	let chronologicalCheck = currentEndDate.isValid() && maximumStartDate.isBefore(currentEndDate);
	if(!currentStartDate.isValid() && maximumStartDate.isValid() && (chronologicalCheck || !currentEndDate.isValid()))
	{
		$("input#startDate").val(maximumStartDate.format(getExpectedMomentFormat()));
	}
	
	chronologicalCheck = currentStartDate.isValid() && maximumEndDate.isAfter(currentStartDate);
	if(!currentEndDate.isValid() && maximumEndDate.isValid() && (chronologicalCheck || !currentStartDate.isValid()))
	{
		$("input#endDate").val(maximumEndDate.format(getExpectedMomentFormat()));
	}
}

/**
 * Returns the maximum end date of this batch (based upon the delivery dates of its underlying jobs).
 * @return Moment The maximum end date of this batch (or an empty string).
 */
function extrapolateMaxEndDate()
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
 */
async function verifyStatus(id)
{
	try{
		let status = await retrieveBatchStatus(id);
		if(status !== null && status !== window.sessionStorage.getItem("status"))
		{
			window.location.reload();
		}
	}
	catch(error){
		showError("Failed to retrieve the status of the batch.", error);
	}
}

/**
 * Retrieve the list of Jobs of this Batch
 * @param {int} batchId The unique identifier of this Batch
 */
async function getJobs(batchId)
{
	try{
		let jobs = await retrieveJobs(batchId);
		await fillJobsList(jobs, false);
	}
	catch(error){
		showError("La récupération des jobs de la batch a échouée", error);
	};
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
 * 
 * @return {bool} If information is valid, returns true. Otherwise, returns false.
 */
function validateInformation(id, name, startDate, endDate, fullDay, material, boardSize, status, comments, jobIds)
{
	let err = "";
	
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
 * Prompts user to confirm how the batch should be downloaded.
 */
function downloadConfirm()
{
	$("#downloadMsgModal").show();
}

/**
 * Prompts user to confirm the saving of the current Type.
 */
async function saveConfirm()
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
	
	if(validateInformation.apply(null, args)){
		if(await askConfirmation("Sauvegarde de batch", "Voulez-vous vraiment sauvegarder cette batch?"))
		{
			document.getElementById("loadingModal").style.display = "block";
			try{
				let id = await saveBatch.apply(null, args);
				goToBatch(id);
			}
			catch(error){
				showError("La sauvegarde de la batch a échouée", error);
			}
			finally{
				document.getElementById("loadingModal").style.display = "none";
			}
		}
	}
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
async function generateConfirm(action = 1)
{
	await generatePrograms($("#batchId").val(), action);
}

/**
 * Downloads a Batch to CutQueue's queue.
 * @param {int} id The id of the Batch
 * @param {int} [action=1] The action requested. If 1, then the project is nested. Otherwise, an archive is downloaded.
 */
async function generatePrograms(id, action = 1)
{
	if(compareWithSessionStorage())
	{
		document.getElementById("loadingModal").style.display = "block";
		try{
			let downloadableFile = await downloadBatch(id, action);
			if (action === 1)
			{
				window.location.reload(); //Batch was sent to CutRite for nesting.
			}
			else
			{
				downloadFile(downloadableFile.url, downloadableFile.name); // Local copy of the individual programs.
			}
		}
		catch(error)
		{
			showError("La génération du programme d'usinage a échouée", error);
		}
		finally{
			document.getElementById("loadingModal").style.display = "none";
		}
	}
	else
	{
		let message = "Des différences ont été trouvées entre les dernières données sauvegardées et les données " +
		"actuelles. Veuillez sauvegarder ou recharger la page, puis réessayer."
		showError("La génération des programmes a échouée", message);
	}
}

/**
 * Prompts user to confirm the deletion of the current Type.
 */
async function deleteConfirm()
{
	if(await askConfirmation("Suppression de batch", "Voulez-vous vraiment supprimer cette batch?"))
	{
		document.getElementById("loadingModal").style.display = "block";
		try{
			await deleteBatch($("#batchId").val());
			goToIndex();
		}
		catch(error){
			showError("La suppression de la batch a échouée", error);
		}
		finally{
			document.getElementById("loadingModal").style.display = "none";
		}
	}
}

/**
 * Retrieves the list of possible pannels for a given material id
 */
async function updatePannelsList()
{
	let materialId = parseInt($("select#material >option:selected").val());
	if(materialId !== 0)
	{
		try{
			let pannelCodes = await retrievePannels($("select#material").val());
			let value = $("select#boardSize").val();
				
			$("select#boardSize").empty().append($("<option></option>").text("").val(""));
			pannelCodes.map(function(pannelCode){
				$("select#boardSize").append($("<option></option>").text(pannelCode).val(pannelCode));
			});
			$("select#boardSize").val(($("select#boardSize >option[value='" + value + "']").length > 0) ? value : "");
		}
		catch(error){
			showError("Échec de la récupération de la liste des panneaux disponibles", error);
		}
	}
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
	window.open(ROOT_URL + "/sections/visualiseur/index.php?id=" + batchId, "_blank");
}

/**
 * Opens a job
 * @param {object} event An event generated by a click on the job's summary row.
 */
function openJobEvent(event)
{
	updateSessionStorage();
	let batchId = (event.data.batchId !== null) ? event.data.batchId : -1;
	window.location.assign(ROOT_URL + "/sections/job/index.php?jobId=" + event.data.jobId + "&batchId=" + batchId);
}