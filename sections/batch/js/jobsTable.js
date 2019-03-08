"use strict";

/**
 * Fills the list of Jobs of this Batch
 * @param {job[]} jobs An array of job identidfiers
 * @param {bool} [IsName=false] Set to false if identifiers are IDs. If they are names, this must be set to true.
 * @return {Promise}
 */
function fillJobsList(jobs, isName = false)
{
	return new Promise(function(resolve, reject){
		let promises = jobs.map(function(job){
			return addJob(job, false);
		});
		Promise.all(promises)
		.then(function(){
			resolve();
		})
		.catch(function(){
			reject();
		});
	});
}

/**
 * Adds a job to the Batch's orders
 * @param {mixed} identfier The name or the id of a Job
 * @param {bool} [IsName=false] Set to false if identifier is an ID. If it is a name, this must be set to true.
 * @return {Promise}
 */
function addJob(identifier, isName = false)
{
	return new Promise(function(resolve, reject){
		if(identifier !== null && identifier !== "")
		{
			getJobSummary(identifier, isName)
			.catch(function(error){
				showError("La récupération d'un résumé de la job a échouée", error);
				return Promise.reject();
			})
			.then(function(job){
				if(job.belongsToBatch === null || job.belongsToBatch === window.sessionStorage.getItem("name"))
				{
					$("table#orders > tbody").append(newJob(job));
					$("input#jobNumber").val("");
					return Promise.resolve(job);
				}
				else
				{
					let title = "L'ajout de la job a échoué";
					showError(title, "Cette job appartient déjà à la batch nommée \"" + job.belongsToBatch + "\".");
					return Promise.reject();
				}
				
			})
			.then(function(job){
				resolve(job);
			})
			.catch(function(){
				reject();
			});
			
		}
		else
		{
			showError("L'ajout de la job a échoué", "Le nom de la job ne peut pas être vide.");
			reject();
		}
	});
}

/**
 * Callback for the onclick event of the "Add Job" button.
 */
function addJobButtonPressed()
{
	addJob($('input#jobNumber').val(), true)
	.then(function(job){
		if(!$('input#startDate').val() || !$('input#batchId').val())
		{
			let deliveryDate = job.hasOwnProperty("deliveryDate") ? job.deliveryDate : "";
			let deliveryMoment = moment.tz(deliveryDate, getExpectedMomentFormat(), "America/Montreal");
			$('input#startDate').val(deliveryMoment.subtract(3, "days").add(8, "hours").format(getExpectedMomentFormat()));
		}
		
		if(!$('input#endDate').val() || !$('input#batchId').val())
		{
			let deliveryDate = job.hasOwnProperty("deliveryDate") ? job.deliveryDate : "";
			let deliveryMoment = moment.tz(deliveryDate, getExpectedMomentFormat(), "America/Montreal");
			$('input#endDate').val(deliveryMoment.subtract(3, "days").add(17, "hours").format(getExpectedMomentFormat()));
		}
		updateSessionStorage();
	})
	.catch(function(error){
		// Do nothing.
	});
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