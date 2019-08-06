"use strict";

docReady(function(){
	addInputFileSelectionField();
});

/**
 * Adds an input file selection field.
 */
function addInputFileSelectionField()
{
	document.getElementById("inputFilesContainer").appendChild(newFileInput());
}

/**
 * Creates a new file input element.
 * 
 * @return {Element} The new file input element
 */
function newFileInput()
{
	let inputFileInput = document.createElement("input");
	inputFileInput.type = "file";
	inputFileInput.accept = "*.mpr";
	inputFileInput.style.width = "100%";
	inputFileInput.style.flexGrow = "0";
	inputFileInput.style.flexShrink = "1";
	inputFileInput.style.flexBasis = "auto";
	inputFileInput.value = "";
	inputFileInput.onchange = function(){
		let inputFileInputArray = document.getElementById("inputFilesContainer").getElementsByTagName("input");
		if(inputFileInputArray[inputFileInputArray.length - 1] === this)
		{
			if(this.value !== "" || this.value !== null || this.value !== undefined)
			{
				addInputFileSelectionField();
			}
		}
		else
		{
			if(this.value === "" || this.value === null || this.value === undefined)
			{
				removeInputFileSelectionField.apply(this);
			}
		}
	}
	return inputFileInput;
}

/**
 * Removes an input file selection field
 * 
 * @this {Element} The input file selection field to remove
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
	
	let inputFiles = [];
	await Promise.all(
		[...document.getElementById("inputFilesContainer").getElementsByTagName("input")].slice(0, -1).map(async function(inputFile){
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

	args = [inputFiles, document.getElementById("outputFileName").value];
	if(validateInformation.apply(null, args))
	{
		document.getElementById("loadingModal").style.display = "block";
		try{
			await merge.apply(null, args);
		}
		catch(error){
			showError("La combinaison des programmes a échouée", error);
		}
		finally{
			document.getElementById("loadingModal").style.display = "none";
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
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/machiningPrograms/actions/merge.php",
			"data": {"inputFiles": inputFiles, "outputFileName": outputFileName},
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
		inputFiles.forEach(function(element){
			if(element === "" || element === null || element === undefined)
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