"use strict";

$(function(){
	addInputFileSelectionField();
});

/**
 * Adds an input file selection field.
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
async function mergePrograms()
{
	let args = null;
	
	let inputFiles = []
	await Promise.all(
		$("div#inputFilesContainer >input:not(:last-child)").toArray().map(async function(inputFile){
			return new Promise(async function(resolve, reject){
				try{
					let contents = await readFile(inputFile.files[0], "iso88591");
					inputFiles.push(contents);
					resolve();
				}
				catch(error)
				{
					reject(error);
				}
			});
		})
	)

	args = [inputFiles, $("input#outputFileName").val()];
	if(validateInformation.apply(null, args))
	{
		$("#loadingModal").css({"display": "block"});
		try{
			await merge.apply(null, args);
		}
		catch(error){
			showError("La combinaison des programmes a échouée", error);
		}
		finally{
			$("#loadingModal").css({"display": "none"});
		}
	}
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
 * @return {bool} If information is valid, returns true. Otherwise, returns false.
 */
function validateInformation(inputFiles, outputFileName)
{
	let err = "";
	
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
		err += "La liste de programmes d'entrée ne contient qu'un seul programme. ";
	}
	else
	{
		err += "La liste de programmes d'entrée est vide. ";
	}
	
	if(!(new RegExp("^.+\.mpr$", "i").test(outputFileName)))
	{
		err += "Le nom du programme de sortie doit posséder l'extension \"mpr\". "
	}
	
	// S'il y a erreur, afficher la fenêtre d'erreur
	if(err == "")
	{
		return true;
	}
	else
	{
		showError("Les informations fournies ne sont pas valides", err);
		return false;
	}
}