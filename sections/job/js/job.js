"use strict";

/**
 * Retrieves JobTypes for a specified job id.
 * 
 * @param {int} jobId The job for which JobTypes must be retrieved
 * @return {Promise}
 */
function retrieveJob(jobId)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/job/actions/getJob.php",
			"data": {"jobId": jobId},
			"dataType": "json",
			"async": true,
			"cache": false,
			"onSuccess": function(response){
				if(response.status === "success")
				{
					resolve(response.success.data);
				}
				else
				{
					reject(response.failure.message);
				}
			},
			"onFailure": function(error){
				reject(error);
			}
		});
	});
}

/**
 * Parses a job based on the data on the page
 * @return {object} The job
 */
async function parseJob()
{
	let jobTypes = await Promise.all([...document.getElementsByClassName("blockContainer")].map(async (block) => {
		(await JobTypeBlock.build(block)).toSessionStorage();
		let jobType = JSON.parse(window.sessionStorage.jobType);
		let parametersHashTable = {};
		jobType.parameters.map(function(parameter){
			parametersHashTable[parameter.key] = parameter.value;
		});
		jobType.parameters = parametersHashTable;
		return jobType;
	}));

	return {
		"id" : document.getElementById("job_id").value, 
		"deliveryDate": moment.tz(document.getElementById("date_livraison").value, "YYYY-MM-DD", "America/Montreal"), 
		"jobTypes": jobTypes
	};
}

/**
 * Saves the Job
 * @param {object} job The job to save
 * @return {Promise}
 */
function saveJob(job)
{	
	job.deliveryDate = job.deliveryDate.format("YYYY-MM-DD");
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/job/actions/save.php",
			"data": job,
			"dataType": "json",
			"async": true,
			"cache": false,
			"onSuccess": function(response){
				if(response.status === "success")
				{
					resolve(response.success.data);
				}
				else
				{
					reject(response.failure.message);
				}
			},
			"onFailure": function(error){
				reject(error);
			}
		});
	});
}