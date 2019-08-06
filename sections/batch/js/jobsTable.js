"use strict";

/**
 * Fills the list of Jobs of this Batch
 * @param {job[]} jobs An array of job identidfiers
 * @param {bool} [IsName=false] Set to false if identifiers are IDs. If they are names, this must be set to true.
 * 
 * @return {Promise}
 */
async function fillJobsList(jobs, isName = false)
{
	return Promise.all(jobs.map(async function(job){
		return await addJob(job, isName);
	}));
}

/**
 * Adds a job to the Batch's orders
 * @param {mixed} identfier The name or the id of a Job
 * @param {bool} [IsName=false] Set to false if identifier is an ID. If it is a name, this must be set to true.
 * @return {Promise}
 */
async function addJob(identifier, isName = false)
{
	return new Promise(async function(resolve, reject){
		let ordersTable = document.getElementById("orders");
		let currentJobIdentifiers = [...ordersTable.getElementsByClassName(isName ? "jobNameCell" : "jobIdCell")].map(function(cell){
			return cell.textContent;
		});
		if(identifier === null || identifier === "")
		{
			reject("Le nom de la job ne peut pas être vide.");
		}
		else if(currentJobIdentifiers.includes(identifier))
		{
			let identifierString = (isName ? "le nom " : "l'identifiant numérique unique ") + identifier;
			reject("La job identifiée par " + identifierString + " se trouve déjà dans le tableau des jobs de la batch en cours.");
		}
		else
		{
			try{
				let job = await getJobSummary(identifier, isName);
				if(job.belongsToBatch === null || job.belongsToBatch === document.getElementById("batchName").value)
				{
					document.getElementById("orders").getElementsByTagName("tbody")[0].appendChild(newJob(job));
					document.getElementById("jobNumber").value = "";
					resolve();
				}
				else
				{
					let identifierString = (isName ? "le nom " : "l'identifiant numérique unique ") + identifier;
					let batch = job.belongsToBatch;
					reject("La job identifiée par " + identifierString + " appartient déjà à la batch nommée \"" + batch + "\".");
				}
			}
			catch(error){
				reject(error);
			}
		}
	});
}

/**
 * Callback for the onclick event of the "Add Job" button.
 */
async function addJobButtonPressed()
{
	let jobName = document.getElementById("jobNumber").value;
	try{
		await addJob(jobName, true)
		initializeDates();
		let batchName = document.getElementById("batchName").value;
		if(batchName === null || batchName === "")
		{
			document.getElementById("batchName").value = jobName;
		}
		updateSessionStorage();
		hasChanged(true);
	}
	catch(error){
		showError("L'ajout de la job a échoué.", error);
	};
}

/**
 * Creates a new row for the Batch's orders table
 * @param {object} job An object containing the information to enter in the table
 */
function newJob(job)
{
	let row = document.createElement("tr");

	let deleteTool = document.createElement("div");
	deleteTool.style.display = "inline-block";
	deleteTool.style.width = "100%";
	deleteTool.appendChild(imageButton(ROOT_URL + "/images/cancel16.png", "", removeJob, [row]));

	let toolsContainer = document.createElement("div");
	toolsContainer.style.height = "min-content";
	toolsContainer.appendChild(deleteTool);

	let toolsCell = document.createElement("td");
	toolsCell.classList.add("firstVisibleColumn");
	toolsCell.appendChild(toolsContainer);

	let idCell = document.createElement("td");
	idCell.classList.add("jobIdCell");
	idCell.style.display = "none";
	idCell.textContent = job.id;

	let nameCell = document.createElement("td");
	nameCell.classList.add("jobNameCell");
	nameCell.textContent = job.name;
	nameCell.addEventListener("click", async function(){
		await openJob.apply(this.parentElement, [job.id, document.getElementById("batchId").value]);
	});

	let deliveryDateCell = document.createElement("td");
	deliveryDateCell.textContent = (job.deliveryDate !== null) ? job.deliveryDate : "";
	deliveryDateCell.addEventListener("click", async function(){
		await openJob.apply(this.parentElement, [job.id, document.getElementById("batchId").value]);
	});

	let partsAmountCell = document.createElement("td");
	partsAmountCell.classList.add("lastVisibleColumn");
	partsAmountCell.textContent = job.partsAmount;
	partsAmountCell.addEventListener("click", async function(){
		await openJob.apply(this.parentElement, [job.id, document.getElementById("batchId").value]);
	});

	row.style.cursor = "pointer";
	row.appendChild(toolsCell);
	row.appendChild(idCell);
	row.appendChild(nameCell);
	row.appendChild(deliveryDateCell);
	row.appendChild(partsAmountCell);
	return row;
}

/**
 * Removes a job from job list
 * @param {Element} row A row ro remove
 */
function removeJob(row)
{
	row.remove();
	updateSessionStorage();
}