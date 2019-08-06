"use strict";

/**
 * Retrieves the list of parameters for a given test or model-type combination and display it
 * @param {int} testId The id of the test to retrieve parameters from (if "", model-type combination method will be used)
 * @param {int} modelId The id of the model associated with this test
 * @param {int} typeNo The import number of the type associated with this test
 * 
 * @return {Promise}
 */
function retrieveParameters(testId, modelId, typeNo)
{	
	let url = ROOT_URL + "/parametres/" + ((testId === null) ? "varmodtype" : "test") + "/actions/getParameters.php";
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": url,
			"data": (testId === null) ? {"modelId": modelId, "typeNo": typeNo} : {"testId": testId},
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
 * Retrieves the text of the mpr file for a given test or model-type combination
 * @param {int} testId The id of the test to retrieve custom .mpr file from.
 * 
 * @return {Promise}
 */
function retrieveCustomMpr(testId)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/test/actions/getCustomMpr.php",
			"data": {"id": testId},
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
 * Deletes a Test from the database
 * @param {int} id The id of the Test to delete
 * 
 * @return {Promise}
 */
function deleteTest(id)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"url": ROOT_URL + "/parametres/test/actions/delete.php",
			"contentType": "application/json;charset=utf-8",
			"type": "POST",
			"data": {"id": id},
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
 * Saves a Test to the database
 * @param {int} testId The id of the test to retrieve parameters from (if null, a new test will be created)
 * @param {string} testName The name of this test
 * @param {int} modelId The id of the model associated with this test
 * @param {int} typeNo The import number of the type associated with this test
 * @param {string} mpr The contents of the mpr file associated with this test
 * @param {object array} parameters An array of parameters
 * 
 * @return {Promise}
 */
function saveTest(testId, testName, modelId, typeNo, mpr, parameters)
{	
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/test/actions/save.php",
			"data": {
				"testId": testId, 
				"testName": testName, 
				"modelId": modelId, 
				"typeNo": typeNo, 
				"mpr": mpr, 
				"parameters": parameters
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
 * Creates the machining program for  a test
 * @param {int} testId The id of the test to retrieve parameters from (if null, a new test will be created)
 * 
 * @return {Promise}
 */
function createMachiningProgram(testId)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/test/actions/download.php",
			"data": {"testId": testId},
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
 * Retrieves all the tests between the two specified dates
 * @param {moment} start The lower bound of the interval
 * @param {moment} end The upper bound of the interval
 * 
 * @return {Promise}
 */
function retrieveTestsBetweenDates(startDate, endDate)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/parametres/test/actions/retrieveBetweenTwoDates.php",
			"data": {
				"startDate": startDate.format("YYYY-MM-DD HH:mm:ss"), 
				"endDate": endDate.format("YYYY-MM-DD HH:mm:ss")
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