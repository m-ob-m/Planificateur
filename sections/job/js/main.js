"use strict";

/**
 * Redirects to the page of the specified batch
 * @param {int} batchId The id of the batch
 */
async function goToBatch(batchId)
{
	if(dataHasChanged() === true)
	{
		if(await askConfirmation("Quitter cette job?", "Les modifications non sauvegardés seront perdues."))
		{
			window.location.assign("/Planificateur/sections/batch/index.php?id=" + batchId);
		}
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