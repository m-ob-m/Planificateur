"use strict";

/**
 * Restores possibly unsaved information from the session's storage (can be used untill the data is erased or overwritten).
 */
async function restoreSessionStorage()
{
	$("#batchName").val(window.sessionStorage.getItem("name"));
	$("#fullDay").val(window.sessionStorage.getItem("fullDay"));
	$("#startDate").val(window.sessionStorage.getItem("startDate"));
	$("#endDate").val(window.sessionStorage.getItem("endDate"));
	$("#status").val(window.sessionStorage.getItem("status"));
	$("#comments").val(window.sessionStorage.getItem("comments"));
	$("#material").val(window.sessionStorage.getItem("material"));
	await updatePannelsList();
	$("#boardSize").val(window.sessionStorage.getItem("boardSize"));
	await fillJobsList(JSON.parse(window.sessionStorage.getItem("jobIds")), false);
}

/**
 * Updates session's data storage.
 */
function updateSessionStorage()
{
	let jobIds = [];
	$("table#orders >tbody >tr >td.jobIdCell").each(function(){
		jobIds.push($(this).text());
	});
	
	window.sessionStorage.setItem("__type", "batch");
	window.sessionStorage.setItem("id", $("#batchId").val());
	window.sessionStorage.setItem("name", $("#batchName").val());
	window.sessionStorage.setItem("startDate", $("#startDate").val());
	window.sessionStorage.setItem("endDate", $("#endDate").val());
	window.sessionStorage.setItem("fullDay", $("#fullDay").val());
	window.sessionStorage.setItem("material", $("#material").val());
	window.sessionStorage.setItem("boardSize", $("#boardSize").val());
	window.sessionStorage.setItem("status", $("#status").val());
	window.sessionStorage.setItem("comments", $("#comments").val());
	window.sessionStorage.setItem("jobIds", JSON.stringify(jobIds));
}

/**
 * Compares data from the page with data in session's data storage.
 * @return {bool} True if information is identical, false otherwise.
 */
function compareWithSessionStorage()
{
	let jobIds = [];
	$("table#orders >tbody >tr >td.jobIdCell").each(function(){
		jobIds.push($(this).text());
	});
	
	let comparedJobs = true;
	let sessionjobIds = JSON.parse(window.sessionStorage.getItem("jobIds"));
	if(jobIds.length === sessionjobIds.length)
	{
		$(jobIds).each(function(index){
			if(this.toString() !== sessionjobIds[index])
			{
				comparedJobs = false;
			}
		});
	}
	else
	{
		comparedJobs = false;
	}
	
	return window.sessionStorage.getItem("id") === $("#batchId").val() && 
		window.sessionStorage.getItem("name") === $("#batchName").val() && 
		window.sessionStorage.getItem("startDate") === $("#startDate").val() && 
		window.sessionStorage.getItem("endDate") === $("#endDate").val() && 
		window.sessionStorage.getItem("fullDay") === $("#fullDay").val() && 
		window.sessionStorage.getItem("material") === $("#material").val() && 
		window.sessionStorage.getItem("boardSize") === $("#boardSize").val() && 
		window.sessionStorage.getItem("status") === $("#status").val() &&
		window.sessionStorage.getItem("comments") === $("#comments").val() && 
		comparedJobs;
}