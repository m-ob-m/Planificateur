"use strict";

/**
 * Initiates the simplification process.
 */
async function simplifyProgram()
{
	let args = null;
	
	document.getElementById("loadingModal").style.display = "block";
	try{
		let inputFilePath = document.getElementById("inputFile").files[0];
		if(inputFilePath !== "" && inputFilePath !== null && inputFilePath !== undefined)
		{
			let inputFile = await readFile(inputFilePath, "iso88591");
			args = [inputFile, document.getElementById("outputFileName").value];
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
		document.getElementById("loadingModal").style.display = "none";
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
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/machiningPrograms/actions/linearize.php",
			"data": {"inputFile": inputFile, "outputFileName": outputFileName},
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
	let inputFileName = document.getElementById("inputFile").value.split(/(\\|\/)/g).pop();
	document.getElementById("outputFileName").value = inputFileName;
}