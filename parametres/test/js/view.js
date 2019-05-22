"use strict";

$(async function(){
	await refreshParameters();
	$("input#mprFileDialog").change(async function(){
		readMpr($(this).prop('files')[0]);
		$(this).val("");
	})
});

/**
 * Display a message to validate the fact that the user wants to delete this Test
 */
async function deleteConfirm()
{
	if(await askConfirmation("Suppression de test", "Voulez-vous vraiment supprimer ce test?"))
	{
		$("#loadingModal").css({"display": "block"});
		try{
			await deleteTest($("#id").val());
			goToIndex();
		}
		catch(error){
			showError("La suppression du test a échouée", error);
		}
		finally{
			$("#loadingModal").css({"display": "none"});
		}
	}
}

/**
 * Prompts user to confirm the saving of the current Test.
 */
async function saveConfirm()
{	
	let parameters = getModifiedParametersArray();
	let mpr = ($("#parametersEditionTextArea").length > 0) ? $("#parametersEditionTextArea").val() : null;  
	let testId = ($("#id").val() && 0 !== $("#id").val().length) ? $("#id").val() : null;
	let args = [testId, $("#name").val(), $("#model").val(), $("#type").val(), mpr, parameters];
	
	if(validateInformation.apply(null, args))
	{
		if(await askConfirmation("Sauvegarde de test", "Voulez-vous vraiment sauvegarder ce test?"))
		{
			$("#loadingModal").css({"display": "block"});
			try{
				let id = await saveTest.apply(null, args);
				$("input#id").val(id);
				try{
					await createMachiningProgram(id);
					openTest(id);
				}
				catch(error){
					showError("La généation du programme d'usinage du test a échouée", error);
				}
			}
			catch(error){
				showError("La sauvegarde du test a échouée", error);
			}
			finally{
				$("#loadingModal").css({"display": "none"});
			}
		}
	}
}

/**
 * Gets the modified parameters from the page and puts them into an array
 * 
 * @return {array} The modified parameters as an array
 */
function getModifiedParametersArray()
{
	let parameters = [];
	$("table#parametersTable >tbody >tr").each(function(index){
		if($(this).find('td:nth-child(5) >input').val() !== $(this).find('td:nth-child(2) >textarea').val())
		{
			parameters.push({
				"key": $(this).find('td:nth-child(1) >input').val(), 
				"value": $(this).find('td:nth-child(2) >textarea').val(),
				"description": $(this).find('td:nth-child(3) >textarea').val(),
				"index": index
			});
		}
	});
	return parameters;
}

/**
 * Validates information entered by user
 * @param {int} id The id of the Test.
 * @param {string} name The name of the Test
 * @param {string} modelId The id of the model associated to this Test
 * @param {string} typeNo The import number of the type associated to this Test
 * @param {string} mpr The contents of the mpr file associated to this Test.
 * @param {object array} parameters The parameters of this Test
 * 
 * @return {bool} If information is valid, returns true. Otherwise, returns false.
 */
function validateInformation(id, name, modelId, typeNo, mpr, parameters)
{
	let err = "";
		
	// Validation des parametres
	if(parameters.length > 0)
	{
		$(parameters).each(function(index){
				if(!(new RegExp("^\\S+$")).test(this.key))
				{
					err += "La clé du paramètre de la ligne \"" + this.index + 1 + "\" " +
						"est vide ou contient des espaces blancs.";
					return;
				}
				
				if(!this.value.trim())
				{
					err += "Le paramètre de la clé " + this.key + " n'a pas de valeur.";
				}
			}
		);
	}
		
	if(!isPositiveInteger(id) && id !== "" && id !== null)
	{
		err += "L'identificateur unique doit être un entier positif.";
	}
	
	if(!(new RegExp("^[A-Za-z0-9_]+$")).test(name))
	{
		err += "Le nom du test ne peut pas être vide et ne doit contenir que des caractères alphanumériques et des \"_\".";
	}
	
	if(!isPositiveInteger(modelId))
	{
		err += "Le modèle sélectionné présente une erreur.";
	}
	
	if(!isPositiveInteger(typeNo))
	{
		err += "Le type sélectionné présente une erreur.";
	}

	// S'il y a erreur, afficher la fenêtre d'erreur
	if(err == "")
	{
		return true;
	}
	else
	{
		showError("Les informations du modèle ne sont pas valides", err);
		return false;
	}
} 

/**
 * Creates a new parameter row for the parameters table
 * @param {object} parameter An object of the type {key => "key", value => "value"}
 * @param {boolean} isNew A bit that indicates if thisTest is new (it has no id)
 */
function newParameter(parameter, isNew)
{
	let key = (parameter !== null && parameter.hasOwnProperty("key")) ? parameter.key : null;
	let description = (parameter !== null && parameter.hasOwnProperty("description")) ? parameter.description :null;
	let defaultValue = (parameter !== null && parameter.hasOwnProperty("defaultValue")) ? parameter.defaultValue : null;
	
	let value = null;
	let oldValue = null;
	if(parameter !== null)
	{
		if(parameter.hasOwnProperty("specificValue"))
		{
			value = parameter.specificValue;
			if(parameter.specificValue !== null && !isNew)
			{
				oldValue = parameter.specificValue
			}
		}
		else if(parameter.hasOwnProperty("defaultValue"))
		{
			value = parameter.defaultValue;
		}
	}
	
	let row = $("<tr></tr>")
	.css({"height": "50px"});
	
	let keyInput = $("<input>")
	.addClass("spaceEfficientText")
	.prop({"disabled": true})
	.val(key);
	
	let keyCell = $("<td></td>")
	.addClass("firstVisibleColumn")
	.append(keyInput);
	
	let valueInput = $("<textarea></textarea>")
	.addClass("spaceEfficientText")
	.css({"overflow-x": "hidden", "overflow-y": "auto", "resize": "none"})
	.val((value === null) ? defaultValue : value);
	
	let valueCell = $("<td></td>")
	.append(valueInput);
	
	let descriptionInput = $("<textarea></textarea>")
	.prop({"readonly": true})
	.css({"overflow-x": "hidden", "overflow-y": "auto", "resize": "none"})
	.addClass("spaceEfficientText")
	.val(description);
	
	let descriptionCell = $("<td></td>")
	.append(descriptionInput);
	
	let defaultValueInput = $("<textarea></textarea>")
	.prop({"disabled": true})
	.css({"overflow-x": "hidden", "overflow-y": "auto", "resize": "none"})
	.addClass("spaceEfficientText")
	.val(defaultValue);
	
	let defaultValueCell = $("<td></td>")
	.addClass("lastVisibleColumn")
	.css("padding", "5px")
	.append(defaultValueInput);
	
	let oldValueInput = $("<input></input>")
	.prop("disabled", true)
	.css("display", "none")
	.val(oldValue);
	
	let oldValueCell = $("<td></td>")
	.prop("disabled", true)
	.css("display", "none")
	.append(oldValueInput);
	
	return row.append(keyCell, valueCell, descriptionCell, defaultValueCell, oldValueCell);
}

/**
 * Refreshes the parameters.
 */
async function refreshParameters()
{
	/* Read the test's id. */
	let testId = ($("#id").val() && 0 !== $("#id").val().length) ? parseInt($("#id").val()) : null;
	let typeNo = parseInt($("#type option:selected").val());
	let modelId = parseInt($("#model option:selected").val());
	
	$("#parametersEditorContainer").empty();
	if(modelId === 2)
	{
		try{
			await refreshParametersCustom(testId);
		}
		catch(error){
			showError("La récupération des paramètres a échouée", error);
		}
	}
	else
	{
		try{
			await refreshParametersStandard(testId, modelId, typeNo);
		}
		catch(error){
			showError("La récupération des paramètres a échouée", error);
		}
	}
}

/**
 * Refreshes the parameters editor using the custom program style.
 * @param {int} id The unique identifier of this Test
 */
async function refreshParametersCustom(id)
{
	$("html").css({"height": "100%"});
	$("body").css({"height": "100%"});
	$("#page-wrapper").css({"display": "flex", "flex-flow": "column", "height": "100%"});
	$("#header-wrapper").css({"flex": "0 1 auto"});
	$("#features-wrapper").css({"flex": "1 1 auto", "display": "flex", "flex-flow": "column"});
	$("#parametersFormContainer").css({"flex": "0 1 auto"});
	
	$("#parametersEditorContainer")
	.css({"flex": "1 1 auto", "display": "flex", "flex-flow": "column"})
	.append(makeCustomTextArea().attr({"id": "parametersEditionTextArea"}));
	
	if(id === null)
	{
		$("textArea#parametersEditionTextArea").val("");
		$("div#mprFileDialogContainer").show();
	}
	
	let mpr = "";
	if(id !== null && id !== "")
	{
		try{
			mpr = await retrieveCustomMpr(id);
		}
		catch(error){
			showError("La récupération du contenu du fichier mpr associé à ce test a échouée", error);
		}
	}

	$("textArea#parametersEditionTextArea").val(mpr);
	$("div#mprFileDialogContainer").show();
}

/**
 * Refreshes the parameters editor using the standard program style.
 * @param {int} id The unique identifier of this Test
 * @param {int} modelId The unique identifier of the model
 * @param {int} typeNo The type's import number
 */
async function refreshParametersStandard(testId, modelId, typeNo)
{
	$("html").css({"height": "auto"});
	$("body").css({"height": "auto"});
	$("#page-wrapper").css({"display": "block", "height": "auto"});
	$("#header-wrapper").css({"flex": "none"});
	$("#features-wrapper").css({"flex": "none", "display": "block"});
	$("#parametersFormContainer").css({"flex": "none"});
	
	$("#parametersEditorContainer")
	.css({"flex": "none", "display": "block"})
	.append(makeStandardParametersTable().attr({"id": "parametersTable"}));
	
	try{ 
		let parameters = await retrieveParameters(testId, modelId, typeNo);
		fillStandardParametersTable(parameters, testId === null);
		$("div#mprFileDialogContainer").hide();
	}
	catch(error){
		showError("La récupération des paramètres de ce test a échouée", error);
	}
}

/**
 * Creates a parameterTable for generic-driven model-types
 * 
 * @return {jquery} The new parametersTable
 */
function makeStandardParametersTable()
{
	let keyHeader = $("<th></th>")
	.addClass("firstVisibleColumn spaceEfficientText")
	.css({"width": "10%"})
	.text("Clé");
	
	let valueHeader = $("<th></th>")
	.addClass("spaceEfficientText")
	.css({"width": "35%"})
	.text("Valeur");
	
	let descriptionHeader = $("<th></th>")
	.addClass("spaceEfficientText")
	.css({"width": "20%"})
	.text("Description");
	
	let defaultValueHeader = $("<th></th>")
	.addClass("lastVisibleColumn spaceEfficientText")
	.css({"width": "35%"})
	.text("Valeur par défaut");
	
	let oldValueHeader = $("<th></th>")
	.css({"display": "none"})
	.text("Valeur précédente");
	
	return $("<table></table>")
	.addClass("test parametersTable")
	.css({"width": "100%"})
	.append(
		$("<thead></thead>")
		.append(
			$("<tr></tr>").append(keyHeader, valueHeader, descriptionHeader, defaultValueHeader, oldValueHeader)
		),
		$("<tbody></tbody>")
	);
	
}

/**
 * Fill the body of a standard parameters table
 * 
 * @param {parameters[]} parameters An array of parameters
 * @param {bool} [isCreating=false] A boolean that determines if the user is creating or updating a Test
 */
function fillStandardParametersTable(parameters, isCreating = false)
{
	$("table#parametersTable >tbody >tr").remove();
	if(parameters.length > 0)
	{
		$(parameters).each(function(){
			$("table#parametersTable >tbody").append(newParameter(this, isCreating));
		});
	}
}

/**
 * Creates a textArea for custom model-types edition
 * 
 * @return {jquery} The new textarea
 */
function makeCustomTextArea()
{
	return $("<textArea></textArea>")
	.attr({"spellcheck": false, "autocorrect": false})
	.addClass("spaceEfficientText")
	.css({"resize": "none", "overflow-x": "hidden", "overflow-y": "auto", "flex": "1 1 auto"});
}

/**
 * Reads the content of an mpr file and displays it.
 * @param {string} filepath The path to the mpr file
 */
async function readMpr(filepath)
{
	try{
		let mpr = await readFile(filepath, "iso88591");
		$("textarea#parametersEditionTextArea").val(mpr);
	}
	catch(error){
		showError("La lecture du fichier a échouée", error);
	}
}

/**
 * Returns to index page.
 */
function goToIndex()
{
	window.location.assign("index.php");
}