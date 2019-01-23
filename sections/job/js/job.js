/**
 * Retrieves JobTypes for a specified job id.
 * 
 * @param {int} jobId The job for which JobTypes must be retrieved
 * @return {Promise}
 */
function retrieveJobTypes(jobId)
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/sections/job/actions/getJobTypes.php",
			"data": {"jobId": jobId},
			"dataType": "json",
			"async": true,
			"cache": false,
		})
		.done(function(response){
			if(response.status === "success")
			{
				resolve(response.success.data);
			}
			else
			{
				reject(response.failure.message);
			}
		})
		.fail(function(error){
			reject(error.responseText);
		});
	});
}

/**
 * Parses a job based on the data on the page
 * @return {object} The job
 */
function parseJobFromMetaData()
{
	let job = {
		"id" : $("input#job_id").val(), 
		"deliveryDate": moment.tz($("input#date_livraison").val(), "YYYY-MM-DD", "America/Montreal"), 
		"jobTypes": []
	};
	$("div.blockContainer").each(function(){
		job.jobTypes.push(updateJobTypePartsMetaData.apply($(this)).data());
	});
	return job;
}

/**
 * Saves the Job
 * @param {object} job The job to save
 */
function saveJob(job)
{	
	job.deliveryDate = job.deliveryDate.format("YYYY-MM-DD");
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/sections/job/actions/save.php",
			"data": JSON.stringify(job),
			"dataType": "json",
			"async": true,
			"cache": false,
		})
		.done(function(response){
			if(response.status === "success")
			{
				resolve(response.success.data);
			}
			else
			{
				reject(response.failure.message);
			}
		})
		.fail(function(error){
			reject(error.responseText);
		});
	});
}