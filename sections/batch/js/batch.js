"use strict";

/**
 * Retrieves the status of a batch
 * 
 * @param {int} id The id of a Batch
 * 
 * @return {Promise}
 */
function retrieveBatchMprStatus(id)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/batch/actions/getMprStatus.php",
			"data": {"batchId": id},
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
 * Retrieves the pannel choices associated to a material
 * 
 * @param {int} materialId The id of a material
 * 
 * @return {Promise}
 */
function retrievePannels(materialId)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/material/actions/getPannels.php",
			"data": {"materialId": materialId},
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
 * Retrieves the jobs of a batch
 * 
 * @param {int} batchId The id of a Batch
 * 
 * @return {Promise}
 */
function retrieveJobs(batchId)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/batch/actions/getJobs.php",
			"data": {"batchId": batchId},
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
 * Retrieves a summary of a job
 * 
 * @param {mixed} identfier The name or the id of a Job
 * @param {bool} [IsName=false] Set to false if identifier is an ID. If it is a name, this must be set to true.
 * 
 * @return {Promise}
 */
function getJobSummary(identifier, isName = false)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/job/actions/getSummary.php",
			"data": {[isName ? "name" : "id"]: identifier},
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
 * Saves the Batch.
 * @param {int} id The id of the Batch (is "" if new)
 * @param {string} name The name of the Batch
 * @param {moment} startDate The planned starting date of the machining of the Batch
 * @param {moment} endDate The planned ending date of the machining of the Batch
 * @param {string} fullDay A string value representing a boolean indicating that the vatch will take a full day to machine
 * @param {string} material The material of this Batch
 * @param {string} boardSize The size of the boards used to machine this Batch
 * @param {string} status The status of this Batch (E = Entered, X = In execution, P = Urging, A = Waiting, N = Non-delivered, 
 * 							T = Completed)
 * @param {string} comments The comments entered for this Batch (will be overwritten if an error occurs in CutQueue)
 * @param {string} jobIds An array containing the unique identifiers of the Jobs contained in this Batch
 * 
 * @return {Promise}
 */
function saveBatch(id, name, startDate, endDate, fullDay, material, boardSize, status, comments, jobIds)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/batch/actions/save.php",
			"data": {
				"id": id, 
				"name": name, 
				"startDate": startDate.format("YYYY-MM-DD HH:mm:ss"), 
				"endDate": endDate.format("YYYY-MM-DD HH:mm:ss"), 
				"fullDay": fullDay, 
				"material": material,
				"boardSize": boardSize, 
				"status": status, 
				"comments": comments, 
				"jobIds": jobIds
			},
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
 * Downloads a Batch to CutQueue's queue.
 * @param {int} id - The id of the Batch
 * @param {int} [action=1] - The action requested. If action = 1, then the archive is sent to CutRite. 
 * 		Otherwise, the archive is downloaded.
 * 
 * @return {Promise}
 */
function downloadBatch(id, action = 1)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/batch/actions/download.php",
			"data": {"batchId": id, "action": action},
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
 * Deletes a Batch.
 * @param {int} batchId The id of the Batch
 * 
 * @return {Promise}
 */
function deleteBatch(id)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/batch/actions/delete.php",
			"data": {"batchId": id},
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