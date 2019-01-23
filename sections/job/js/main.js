/**
 * Redirects to the page of the specified batch
 * @param {int} batchId The id of the batch
 */
function goToBatch(batchId)
{
	if(dataHasChanged() === true)
	{
		askConfirmation("Quitter cette job?", "Les modifications non sauvegardés seront perdues.")
		.then(function(){
			window.location.assign("/Planificateur/sections/batch/index.php?id=" + batchId);
		})
		.catch(function(){/* Do nothing. */});
	}
	else
	{
		window.location.assign("/Planificateur/sections/batch/index.php?id=" + batchId);
	}
}

/**
 * Redirects to the page of the specified job
 * @param {int} jobId The id of the job
 * @param {int} [batchId] The id of the parent batch
 */
function goToJob(jobId, batchId)
{
	window.location.assign("/Planificateur/sections/job/index.php?jobId=" + jobId  + "&batchId=" + batchId);
}