"use strict";

/**
 * Initiates the simplification process.
 */
async function simplifyProgram()
{
	let args = null;
	
	$("#loadingModal").css({"display": "block"});
	try{
		let inputFilePath = $("input#inputFile")[0].files[0];
		if(inputFilePath !== "" && inputFilePath !== null && inputFilePath !== undefined)
		{
			let inputFile = await readFile(inputFilePath, "iso88591");
			args = [inputFile, $("input#outputFileName").val()];
			if(validateInformation.apply(null, args))
			{
				await linearize.apply(null, args);
			}
		}
		else
		{
			showError("La simplification du programme a échouée", "Aucun fichier n'a été sélectionné.");
		}
	}
	catch(error){
		showError("La simplification du programme a échouée", error);
	}
	finally{
		$("#loadingModal").css({"display": "none"});
	}
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
 * @return {bool} If information is valid, returns true. Otherwise, returns false.
 */
function validateInformation(inputFile, outputFileName)
{
	let err = "";
	if(inputFile === "" || inputFile === null || inputFile === undefined)
	{
		err += "Le programme d'entrée est vide. ";
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

/**
 * Guesses the output filename from the input filename.
 */
function guessOutputFileName()
{
	let inputFileName = $("input#inputFile").val().split(/(\\|\/)/g).pop();
	$("input#outputFileName").val(inputFileName)
}