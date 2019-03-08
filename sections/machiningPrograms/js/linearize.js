"use strict";

/**
 * Initiates the simplification process.
 */
function simplifyProgram()
{
	let args = null;
	
	$("#loadingModal").css({"display": "block"});
	readFile($("input#inputFile")[0].files[0], "iso88591")
	.then(function(inputFile){
		args = [inputFile, $("input#outputFileName").val()];
	})
	.then(function(){
		return validateInformation.apply(null, args);
	})
	.then(function(){
		return linearize.apply(null, args);
	})
	.catch(function(error){
		showError("La simplification du programme a échouée", error);
	})
	.then(function(){
		$("#loadingModal").css({"display": "none"});
	});
}

/**
 * Retrieves the pannel choices associated to a material
 * 
 * @param {string} inputFile The contents of the input file
 * @param {string} outputFileName The desired name for the output file
 * 
 * @return {Promise}
 */
function linearize(inputFile, outputFileName)
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/sections/machiningPrograms/actions/linearize.php",
			"data": JSON.stringify({"inputFile": inputFile, "outputFileName": outputFileName}),
			"dataType": "json",
			"async": true,
			"cache": false
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
 * Validates if the provided information is proper before processing
 * 
 * @param {string} inputFile The contents of the input file
 * @param {string} outputFileName The desired name for the output file
 * 
 * @return {Promise}
 */
function validateInformation(inputFile, outputFileName)
{
	return new Promise(function(resolve, reject){
		let error = "";
		if(inputFile === "" || inputFile === null || inputFile === undefined)
		{
			error += "Le programme d'entrée est vide. ";
		}
		
		if(!(new RegExp("^.+\.mpr$", "i").test(outputFileName)))
		{
			error += "Le nom du programme de sortie doit posséder l'extension \"mpr\". "
		}
		
		if(error === "")
		{
			resolve();
		}
		else
		{
			reject(error);
		}
	});
}

/**
 * Guesses the output filename from the input filename.
 */
function guessOutputFileName()
{
	let inputFileName = $("input#inputFile").val().split(/(\\|\/)/g).pop();
	$("input#outputFileName").val(inputFileName)
}