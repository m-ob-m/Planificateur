"use strict";

/**
 * Fills the list of Jobs of this Batch
 * @param {job[]} jobs An array of job identidfiers
 * @param {bool} [IsName=false] Set to false if identifiers are IDs. If they are names, this must be set to true.
 */
async function fillJobsList(jobs, isName = false)
{
	return await Promise.all(jobs.map(async function(job){
		return addJob(job, false);
	}));
}

/**
 * Adds a job to the Batch's orders
 * @param {mixed} identfier The name or the id of a Job
 * @param {bool} [IsName=false] Set to false if identifier is an ID. If it is a name, this must be set to true.
 * @return {bool} True if the job was added to the table, false otherwise. 
 */
async function addJob(identifier, isName = false)
{
	if(identifier !== null && identifier !== "")
	{
		try{
			let job = await getJobSummary(identifier, isName);
			if(job.belongsToBatch === null || job.belongsToBatch === window.sessionStorage.getItem("name"))
			{
				$("table#orders > tbody").append(newJob(job));
				$("input#jobNumber").val("");
			}
			else
			{
				let title = "L'ajout de la job a échoué";
				showError(title, "Cette job appartient déjà à la batch nommée \"" + job.belongsToBatch + "\".");
				return false;
			}
		}
		catch(error){
			showError("La récupération d'un résumé de la job a échouée", error);
			return false;
		}
	}
	else
	{
		showError("L'ajout de la job a échoué", "Le nom de la job ne peut pas être vide.");
		return false;
	}
	return true;
}

/**
 * Callback for the onclick event of the "Add Job" button.
 */
async function addJobButtonPressed()
{
	let jobName = $('#jobNumber').val();
	if(await addJob(jobName, true))
	{
		initializeDates();
		if($("input#batchName").val() === null || $("input#batchName").val() === "")
		{
			$("input#batchName").val(jobName);
		}
		updateSessionStorage();
	}
}

/**
 * Creates a new row for the Batch's orders table
 * @param {object} job An object containing the information to enter in the table
 */
function newJob(job)
{
	let row = $("<tr></tr>").css({"cursor": "pointer"})
	.on("click", ":not(>td:first-child)", {"jobId": job.id, "batchId": window.sessionStorage.getItem("id")}, openJobEvent);
	let deleteTool = $("<div></div>").css({"display": "inline-block", "width": "100%"})
	.append(imageButton("/Planificateur/images/cancel16.png", "", removeJob, [row]));
	let toolsContainer = $("<div></div>").css({"height": "min-content"}).append(deleteTool);	
	let toolsCell = $("<td></td>").addClass("firstVisibleColumn").append(toolsContainer);
	let idCell = $("<td></td>").addClass("jobIdCell").prop("hidden", true).text(job.id);
	let nameCell = $("<td></td>").text(job.name);
	let deliveryDateCell = $("<td></td>").text((job.deliveryDate !== null) ? job.deliveryDate : "");
	let partsAmountCell = $("<td></td>").addClass("lastVisibleColumn").text(job.partsAmount);
	row.append(toolsCell).append(idCell).append(nameCell).append(deliveryDateCell).append(partsAmountCell);
	return row;
}

/**
 * Removes a job from job list
 * @param {jquery} row A row ro remove
 */
function removeJob(row)
{
	row.remove();
	updateSessionStorage();
}