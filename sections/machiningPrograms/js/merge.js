"use strict";

$(function(){
	addInputFileSelectionField();
});

/**
 * Adds an input file selection field.
 * 
 * @return
 */
function addInputFileSelectionField()
{
	$("div#inputFilesContainer").append(
		$("<input>")
		.attr({"type": "file", "accept": "*.mpr"})
		.css({"width": "100%", "flex": "0 1 auto"})
		.change(function(){
			if($("div#inputFilesContainer >input:last-child")[0] === this)
			{
				if($(this).val() !== "" || $(this).val() !== null || $(this).val() !== undefined)
				{
					addInputFileSelectionField();
				}
			}
			else
			{
				if($(this).val() === "" || $(this).val() === null || $(this).val() === undefined)
				{
					removeInputFileSelectionField.apply($(this));
				}
			}
		})
		.val("")
	);
}

/**
 * Removes an input file selection field
 * 
 * @this {jquery} The input file selection field to remove
 * 
 * @return
 */
function removeInputFileSelectionField()
{
	this.remove();
}

/**
 * Initiates the merging process.
 */
function mergePrograms()
{
	let args = null;
	
	$("#loadingModal").css({"display": "block"});
	let inputFiles = []
	let promises = $("div#inputFilesContainer >input:not(:last-child)").toArray().map(function(inputFile){
		return readFile(inputFile.files[0], "iso88591")
		.then(function(contents){
			inputFiles.push(contents);
		});
	});
	
	Promise.all(promises)
	.then(function(){
		args = [inputFiles, $("input#outputFileName").val()];
	})
	.then(function(){
		return validateInformation.apply(null, args);
	})
	.then(function(){
		return merge.apply(null, args);
	})
	.catch(function(error){
		showError("La combinaison des programmes a échouée", error);
	})
	.then(function(){
		$("#loadingModal").css({"display": "none"});
	});
}

/**
 * Retrieves the pannel choices associated to a material
 * 
 * @param {string[]} inputFiles The contents of the input files
 * @param {string} outputFileName The desired name for the output file
 * 
 * @return {Promise}
 */
function merge(inputFiles, outputFileName)
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/sections/machiningPrograms/actions/merge.php",
			"data": JSON.stringify({"inputFiles": inputFiles, "outputFileName": outputFileName}),
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
 * @param {string[]} inputFiles The contents of the input file
 * @param {string} outputFileName The desired name for the output file
 * 
 * @return {Promise}
 */
function validateInformation(inputFiles, outputFileName)
{
	return new Promise(function(resolve, reject){
		let error = "";
		
		if(Array.isArray(inputFiles) && inputFiles.length > 1)
		{
			$(inputFiles).each(function(){
				if(this === "" || this === null || this === undefined)
				{
					error += "Un des programmes d'entrée est vide. ";
				}
			});
		}
		else if(inputFiles.length > 0)
		{
			error += "La liste de programmes d'entrée ne contient qu'un seul programme. ";
		}
		else
		{
			error += "La liste de programmes d'entrée est vide. ";
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