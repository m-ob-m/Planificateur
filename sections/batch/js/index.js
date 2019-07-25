"use strict";

docReady(async function(){
	document.getElementById("fullDay").addEventListener("change", function(){
		let checkBox = new CheckBox(this);
		let type = checkBox.getState() ? "date" : "datetime-local";
		let startDate = moment.tz(document.getElementById("startDate").value, getExpectedMomentFormat(), "America/Montreal");
		let endDate = moment.tz(document.getElementById("endDate").value, getExpectedMomentFormat(), "America/Montreal");

		let startDateInput = document.getElementById("startDate");
		startDateInput.value = "";
		startDateInput.type = type;
		startDateInput.value = startDate.format(getExpectedMomentFormat());

		let endDateInput = document.getElementById("endDate");
		endDateInput.value = "";
		endDateInput.type = type;
		endDateInput.value = endDate.format(getExpectedMomentFormat());
	});
	
	await initializeFields();
	
	document.getElementById("batchName").addEventListener("keyup", function(key){
		if(key.keyCode === 13){
			document.getElementById("jobNumber").focus();
		}
	});

	document.getElementById("jobNumber").addEventListener("keyup", function(key){
		if(key.keyCode === 13){
			document.getElementById("addJobButton").click();
		}
	});

	document.getElementById("batchName").addEventListener("change", () => {hasChanged(true);});
	document.getElementById("startDate").addEventListener("change", () => {hasChanged(true);});
	document.getElementById("endDate").addEventListener("change", () => {hasChanged(true);});
	document.getElementById("fullDay").addEventListener("change", () => {hasChanged(true);});
	document.getElementById("material").addEventListener("change", () => {hasChanged(true);});
	document.getElementById("boardSize").addEventListener("change", () => {hasChanged(true);});
	document.getElementById("status").addEventListener("change", () => {hasChanged(true);});

	// When the status of the Batch changes, the page must reload.
	window.setInterval(
		async function(){
			let noError = ["", "none"].includes(document.getElementById("errMsgModal").style.display);
			let noValidation = ["", "none"].includes(document.getElementById("validationMsgModal").style.display);
			let noDownload = ["", "none"].includes(document.getElementById("downloadMsgModal").style.display);
			if(!hasChanged() && noError && noValidation && noDownload)
			{
				let id = document.getElementById("batchId").value;
				if(id !== null && id !== ""){
					await verifyMprStatus(id);
				}
			}
		}, 
		10000
	);

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
 * Initializes some fields on the page.
 */
async function initializeFields()
{
	/*	
		Pour pouvoir ajouter une job dans le tableau des jobs d'une batch, la job doit être liée à cette batch ou 
		n'être liée à aucune batch. Lors de l'ouverture de la page d'édition des batchs avec un identifiant numérique unique entier 
		positif de batch spécifié en paramètre, la page est chargée avec les données de la batch spécifiée directement incluses dans 
		le html. Cependant, lors de l'ouverture de la page avec l'identifiant unique numérique -1, on indique à la page qu'on désire  
		créer une nouvelle batch. Il arrive qu'on ait à modifier les données d'une job incluse dans une batch, ce qui requiert 
		d'ouvrir la page d'édition des jobs. Normalement, si on ne sauvegarde pas la batch dans la base de données avant de changer de 
		page, on perd toutes les modifications apportées à la batch, y compris le fait d'y avoir ajouté une job. Afin de permettre 
		une meilleure fluiditié, on permet d'effectuer la sauvegarde de la batch une seule fois après avoir apporté toutes les 
		modifications désirées à la batch et à ses jobs associées. Afin d'y parvenir, à chaque fois qu'on entre dans une job, on 
		sauvegarde l'état actuel de la batch dans les données de session du navigateur. Lors du retour à la page de la batch en question, 
		si les données de session n'ont pas été écrasées par une autre batch entre temps, elles sont restaurées et on peut continuer à 
		modifier la batch là où on était rendu.
	*/

	let sessionData = window.sessionStorage;
	if(typeof sessionData.batch !== "undefined" && document.getElementById("batchId").value === JSON.parse(sessionData.batch).id)
	{
		try{
			let mprStatus = document.getElementById("mprStatus").value;
			await restoreSessionStorage();
			document.getElementById("mprStatus").value = mprStatus;
			initializeDates();
			updateSessionStorage();
		}
		catch(error){
			showError("La restauration des données de session a échouée", error);
		};
	}
	else
	{
		try{
			await getJobs(document.getElementById("batchId").value);
			await updatePannelsList();
			initializeDates();
			updateSessionStorage();
		}
		catch(error){
			showError("La restauration des données pour cette batch a échouée", error);
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
	let currentStartDate = moment.tz(document.getElementById("startDate").value, getExpectedMomentFormat(), "America/Montreal");
	let currentEndDate = moment.tz(document.getElementById("endDate").value, getExpectedMomentFormat(), "America/Montreal");
	
	let chronologicalCheck = currentEndDate.isValid() && maximumStartDate.isBefore(currentEndDate);
	if(!currentStartDate.isValid() && maximumStartDate.isValid() && (chronologicalCheck || !currentEndDate.isValid()))
	{
		document.getElementById("startDate").value = maximumStartDate.format(getExpectedMomentFormat());
	}
	
	chronologicalCheck = currentStartDate.isValid() && maximumEndDate.isAfter(currentStartDate);
	if(!currentEndDate.isValid() && maximumEndDate.isValid() && (chronologicalCheck || !currentStartDate.isValid()))
	{
		document.getElementById("endDate").value = maximumEndDate.format(getExpectedMomentFormat());
	}
}

/**
 * Returns the maximum end date of this batch (based upon the delivery dates of its underlying jobs).
 * @return {Moment} The maximum end date of this batch (or an empty string).
 */
function extrapolateMaxEndDate()
{
	let dates = [...document.getElementById("orders").getElementsByTagName("tbody")[0].getElementsByTagName("tr")].map(function(row){
		return moment.tz(row.getElementsByTagName("td")[3].textContent, getExpectedMomentFormat(), "America/Montreal");
	});
	return (dates.length > 0) ? moment.max(dates) : moment.tz("", getExpectedMomentFormat(), "America/Montreal");
}

/**
 * If the status of the Batch has changed, reloads the page.
 * @param {int} id The id of the current Batch
 */
async function verifyMprStatus(id)
{
	try{
		let mprStatus = await retrieveBatchMprStatus(id);
		let sessionBatch = window.sessionStorage.batch;
		if(typeof sessionBatch !== "undefined" && ![JSON.parse(sessionBatch).mprStatus, null, ""].includes(mprStatus))
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
		await fillJobsList(await retrieveJobs(batchId), false);
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
	
	if(!isPositiveInteger(id, true, true) && id !== "" && id !== null)
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
	
	jobIds.forEach(function(jobId){
		if(!isPositiveInteger(jobId, true, true) || !jobId)
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
		showError("Les informations de la Batch ne sont pas valides", err);
		return false;
	}
}

/**
 * Prompts user to confirm how the batch should be downloaded.
 */
function downloadConfirm()
{
	document.getElementById("downloadMsgModal").style.display = "block";
}

/**
 * Prompts user to confirm the saving of the current Type.
 */
async function saveConfirm()
{
	let materialSelect = document.getElementById("material");
	let boardSizeSelect = document.getElementById("boardSize");
	let statusSelect = document.getElementById("status");
	let jobIds = [...document.getElementById("orders").getElementsByTagName("tbody")[0].getElementsByTagName("tr")].map(function(row){
		return row.getElementsByClassName("jobIdCell")[0].textContent;
	});
	let args = [
		document.getElementById("batchId").value, 
		document.getElementById("batchName").value, 
		moment(document.getElementById("startDate").value, "YYYY-MM-DDTHH:mm:ss").tz("America/Montreal"), 
		moment(document.getElementById("endDate").value, "YYYY-MM-DDTHH:mm:ss").tz("America/Montreal"), 
		new CheckBox(document.getElementById("fullDay")).getState() ? "Y" : "N", 
		materialSelect.options[materialSelect.selectedIndex].value, 
		boardSizeSelect.options[boardSizeSelect.selectedIndex].value, 
		statusSelect.options[statusSelect.selectedIndex].value, 
		document.getElementById("comments").value, 
		jobIds
	];
	
	if(validateInformation.apply(null, args)){
		if(await askConfirmation("Sauvegarde de batch", "Voulez-vous vraiment sauvegarder cette batch?"))
		{
			document.getElementById("loadingModal").style.display = "block";
			try{
				goToBatch(await saveBatch.apply(null, args));
				hasChanged(false);
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
 * Generates nested machining programs.
 * @param {int} [action=1] The action requested. If 1, then the project is nested. Otherwise, an archive is downloaded.
 */
async function generateConfirm(action = 1)
{
	await generatePrograms(document.getElementById("batchId").value, action);
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
		showError(
			"La génération des programmes a échouée", 
			"Des différences ont été trouvées entre les dernières données sauvegardées et les données actuelles. " + 
			"Veuillez sauvegarder ou recharger la page, puis réessayer."
		);
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
			await deleteBatch(document.getElementById("batchId").value);
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
	let materialSelect = document.getElementById("material");
	let materialId = parseInt(materialSelect.options[materialSelect.selectedIndex].value);
	if(materialId !== 0)
	{
		try{
			let boardSizeSelect = document.getElementById("boardSize");
			let boardSizeValue = boardSizeSelect.options[boardSizeSelect.selectedIndex].value;
			
			while(boardSizeSelect.childElementCount > 0)
			{
				boardSizeSelect.firstElementChild.remove();
			}

			let emptyOption = document.createElement("option")
			emptyOption.textContent = "";
			emptyOption.value = "";

			boardSizeSelect.appendChild(emptyOption);
			boardSizeSelect.value = "";
			
			let pannels = await retrievePannels(materialId);
			pannels.map(function(pannelCode){
				let option = document.createElement("option")
				option.textContent = pannelCode;
				option.value = pannelCode;
				
				boardSizeSelect.appendChild(option);
				if(boardSizeSelect.options[boardSizeSelect.selectedIndex].value === "" && boardSizeValue === pannelCode){
					boardSizeSelect.value = pannelCode;
				}
			});
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
	return new CheckBox(document.getElementById("fullDay")).getState() ? "YYYY-MM-DD" : "YYYY-MM-DDTHH:mm:ss";
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
 * @param {int} jobId The unique identifier of the job to reach.
 * @param {int} batchId The unique identifier of the current batch.
 */
async function openJob(jobId, batchId)
{
	await updateSessionStorage();
	window.location.assign(ROOT_URL + "/sections/job/index.php?jobId=" + jobId + "&batchId=" + batchId);
}