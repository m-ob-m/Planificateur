"use strict";

/**
 * Restores possibly unsaved information from the session's storage (can be used untill the data is erased or overwritten).
 */
async function restoreSessionStorage()
{
	if(typeof window.sessionStorage.batch !== "undefined")
	{
		let batch = JSON.parse(window.sessionStorage.batch);
		document.getElementById("batchName").value = batch.name;
		document.getElementById("fullDay").value = batch.fullDay;
		document.getElementById("startDate").value = batch.startDate;
		document.getElementById("endDate").value = batch.endDate;
		document.getElementById("status").value = batch.status;
		document.getElementById("comments").value = batch.comments;
		document.getElementById("material").value = batch.material;
		document.getElementById("mprStatus").value = batch.mprStatus;
		await updatePannelsList();
		document.getElementById("boardSize").value = batch.boardSize;
		await fillJobsList(batch.jobIds, false);
	}
}

/**
 * Updates session's data storage.
 */
function updateSessionStorage()
{
	let jobIds = [...document.getElementById("orders").getElementsByTagName("tbody")[0].getElementsByTagName("tr")].map(function(row){
		return row.getElementsByClassName("jobIdCell")[0].textContent;
	});
	let boardSizeSelect = document.getElementById("boardSize");

	window.sessionStorage.batch = JSON.stringify({
		"id": document.getElementById("batchId").value, 
		"name": document.getElementById("batchName").value, 
		"startDate": document.getElementById("startDate").value, 
		"endDate": document.getElementById("endDate").value, 
		"fullDay": document.getElementById("fullDay").value, 
		"material": document.getElementById("material").value, 
		"boardSize": boardSizeSelect[boardSizeSelect.selectedIndex].value, 
		"status": document.getElementById("status").value, 
		"mprStatus": document.getElementById("mprStatus").value,
		"comments": document.getElementById("comments").value, 
		"jobIds": jobIds
	});
}

/**
 * Compares data from the page with data in session's data storage.
 * @return {bool} True if information is identical, false otherwise.
 */
function compareWithSessionStorage()
{
	let jobIds = [...document.getElementById("orders").getElementsByTagName("tbody")[0].getElementsByTagName("tr")].map(function(row){
		return row.getElementsByClassName("jobIdCell")[0].textContent;
	});
	
	let sessionBatch = JSON.parse(window.sessionStorage.batch);
	let comparedJobs = true;
	comparedJobs = (jobIds.length === sessionBatch.jobIds.length) ? comparedJobs : false;
	jobIds.map(function(element, index){comparedJobs =  (element.toString() === sessionBatch.jobIds[index]) ? comparedJobs : false;});
	
	return typeof sessionBatch !== "undefined" && 
		sessionBatch.id === document.getElementById("batchId").value && 
		sessionBatch.name === document.getElementById("batchName").value && 
		sessionBatch.startDate === document.getElementById("startDate").value && 
		sessionBatch.endDate === document.getElementById("endDate").value && 
		sessionBatch.fullDay === document.getElementById("fullDay").value && 
		sessionBatch.material === document.getElementById("material").value && 
		sessionBatch.boardSize === document.getElementById("boardSize").value && 
		sessionBatch.status === document.getElementById("status").value &&
		sessionBatch.comments === document.getElementById("comments").value && 
		sessionBatch.mprStatus === document.getElementById("mprStatus").value && 
		comparedJobs;
}