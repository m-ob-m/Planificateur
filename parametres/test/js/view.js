"use strict";

docReady(async function(){
	await refreshParameters();
	let mprFileDialog = document.getElementById("mprFileDialog");
	mprFileDialog.onchange = async function(){
		readMpr(mprFileDialog.files[0]);
		mprFileDialog.value = "";
	}
});

/**
 * Display a message to validate the fact that the user wants to delete this Test
 */
async function deleteConfirm()
{
	if(await askConfirmation("Suppression de test", "Voulez-vous vraiment supprimer ce test?"))
	{
		document.getElementById("loadingModal").style.display = "block";
		try{
			await deleteTest(document.getElementById("id").value);
			goToIndex();
		}
		catch(error){
			showError("La suppression du test a échouée", error);
		}
		finally{
			document.getElementById("loadingModal").style.display = "none";
		}
	}
}

/**
 * Prompts user to confirm the saving of the current Test.
 */
async function saveConfirm()
{	
	let parametersEditionTextArea = document.getElementById("parametersEditionTextArea");
	let idInput = document.getElementById("id");
	let id = idInput.value;
	let name = document.getElementById("name").value;
	let model = document.getElementById("model").value;
	let type = document.getElementById("type").value;
	let mpr = (parametersEditionTextArea !== null) ? parametersEditionTextArea.value : null; 
	let parameters = getModifiedParametersArray(); 
	let args = [(id === "") ? null : id, name, model, type, mpr, parameters];
	
	if(validateInformation.apply(null, args))
	{
		if(await askConfirmation("Sauvegarde de test", "Voulez-vous vraiment sauvegarder ce test?"))
		{
			document.getElementById("loadingModal").style.display = "block";
			try{
				let id = await saveTest.apply(null, args);
				idInput.value = id;
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
				document.getElementById("loadingModal").style.display = "none";
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
	let parametersTable = document.getElementById("parametersTable");
	if(parametersTable !== null)
	{
		[...parametersTable.getElementsByTagName("tbody")[0].getElementsByTagName("tr")].forEach(function(element) {
			let newValue = element.getElementsByTagName("td")[1].getElementsByTagName("textarea")[0].value;
			let previousValue = element.getElementsByTagName("td")[4].getElementsByTagName("input")[0].value;
			if(previousValue !== newValue)
			{
				parameters.push({
					"key": element.getElementsByTagName("td")[0].getElementsByTagName("input")[0].value, 
					"value": newValue,
					"description": element.getElementsByTagName("td")[2].getElementsByTagName("textarea")[0].value
				});
			}
		});
	}
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
		parameters.forEach(function(element, index){
				if(!(new RegExp("^\\S+$")).test(element.key))
				{
					err += "La clé du paramètre de la ligne \"" + element.index + 1 + "\" " +
						"est vide ou contient des espaces blancs.";
					return;
				}
				
				if(!element.value.trim())
				{
					err += "Le paramètre de la clé " + element.key + " n'a pas de valeur.";
				}
			}
		);
	}
		
	if(!isPositiveInteger(id, true, true) && id !== "" && id !== null)
	{
		err += "L'identificateur unique doit être un entier positif.";
	}
	
	if(!(new RegExp("^[A-Za-z0-9_]+$")).test(name))
	{
		err += "Le nom du test ne peut pas être vide et ne doit contenir que des caractères alphanumériques et des \"_\".";
	}
	
	if(!isPositiveInteger(modelId, true, true))
	{
		err += "Le modèle sélectionné présente une erreur.";
	}
	
	if(!isPositiveInteger(typeNo, true, false))
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
 * @returns {Element} The new parameter row
 */
function newParameter(parameter, isNew)
{
	let key = (parameter !== null && parameter.hasOwnProperty("key")) ? parameter.key : null;
	let specificValue = (parameter !== null && parameter.hasOwnProperty("specificValue")) ? parameter.specificValue : null;
	let description = (parameter !== null && parameter.hasOwnProperty("description")) ? parameter.description :null;
	let defaultValue = (parameter !== null && parameter.hasOwnProperty("defaultValue")) ? parameter.defaultValue : null;
	
	let value = null;
	let oldValue = null;
	if(parameter !== null)
	{
		if(specificValue !== null)
		{
			value = specificValue;
			oldValue = isNew ? null : specificValue;
		}
		else if(parameter.hasOwnProperty("defaultValue"))
		{
			value = defaultValue;
		}
	}
	
	let keyInput = document.createElement("input");
	keyInput.classList.add("spaceEfficientText");
	keyInput.disabled = true;
	keyInput.value = key;
	
	let keyCell = document.createElement("td");
	keyCell.classList.add("firstVisibleColumn");
	keyCell.appendChild(keyInput);
	
	let valueInput = document.createElement("textarea");
	valueInput.classList.add("spaceEfficientText");
	valueInput.style.overflowX = "hidden";
	valueInput.style.resize = "none";
	valueInput.value = value;
	
	let valueCell = document.createElement("td");
	valueCell.appendChild(valueInput);
	
	let descriptionInput = document.createElement("textarea");
	descriptionInput.classList.add("spaceEfficientText");
	descriptionInput.readOnly = true;
	descriptionInput.style.overflowX = "hidden";
	descriptionInput.style.resize = "none";
	descriptionInput.value = description;
	
	let descriptionCell = document.createElement("td");
	descriptionCell.appendChild(descriptionInput);
	
	let defaultValueInput = document.createElement("textarea");
	defaultValueInput.classList.add("spaceEfficientText");
	defaultValueInput.disabled = true;
	defaultValueInput.style.overflowX = "hidden";
	defaultValueInput.style.resize = "none";
	defaultValueInput.value = defaultValue;
	
	let defaultValueCell = document.createElement("td");
	defaultValueCell.classList.add("lastVisibleColumn");
	defaultValueCell.style.padding = "5px";
	defaultValueCell.appendChild(defaultValueInput);
	
	let oldValueInput = document.createElement("input");
	oldValueInput.disabled = true;
	oldValueInput.style.display = "none";
	oldValueInput.value = oldValue;
	
	let oldValueCell = document.createElement("td");
	oldValueCell.disabled = true;
	oldValueCell.style.display = "none";
	oldValueCell.appendChild(oldValueInput);

	let row = document.createElement("tr");
	row.style.height = "50px";
	row.appendChild(keyCell);
	row.appendChild(valueCell);
	row.appendChild(descriptionCell);
	row.appendChild(defaultValueCell);
	row.appendChild(oldValueCell);
	return row;
}

/**
 * Refreshes the parameters.
 */
async function refreshParameters()
{
	/* Read the test's id. */
	let parametersEditorContainer = document.getElementById("parametersEditorContainer");
	let testIdInput = document.getElementById("id");
	let testId = (testIdInput.value !== null && testIdInput.value !== "") ? parseInt(testIdInput.value) : null;
	let typeNoSelect = document.getElementById("type"); 
	let modelIdSelect = document.getElementById("model");
	let typeNo = parseInt(typeNoSelect.options[typeNoSelect.selectedIndex].value);
	let modelId = parseInt(modelIdSelect.options[modelIdSelect.selectedIndex].value);
	
	while(parametersEditorContainer.childElementCount > 0)
	{
		parametersEditorContainer.firstElementChild.remove();
	}

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
	document.getElementsByTagName("html")[0].style.height = "100%";
	document.getElementsByTagName("body")[0].style.height = "100%";

	let pageWrapper = document.getElementById("page-wrapper");
	pageWrapper.style.display = "flex"; 
	pageWrapper.style.flexFlow = "column"; 
	pageWrapper.style.height = "100%";
	
	let headerWrapper = document.getElementById("header-wrapper");
	headerWrapper.style.flexGrow = "0";
	headerWrapper.style.flexShrink = "1";
	headerWrapper.style.flexBasis = "auto";

	let featuresWrapper = document.getElementById("features-wrapper");
	featuresWrapper.style.flexGrow = "1";
	featuresWrapper.style.flexShrink = "1";
	featuresWrapper.style.flexBasis = "auto";
	featuresWrapper.style.display = "flex";
	featuresWrapper.style.flexFlow = "column";

	let parametersFormContainer = document.getElementById("parametersFormContainer");
	parametersFormContainer.style.flexGrow = "0";
	parametersFormContainer.style.flexShrink = "1"; 
	parametersFormContainer.style.flexBasis = "auto";
	
	let customTextArea = makeCustomTextArea();
	customTextArea.id = "parametersEditionTextArea";

	let parametersEditorContainer = document.getElementById("parametersEditorContainer");
	parametersEditorContainer.style.flexGrow = "1";
	parametersEditorContainer.style.flexShrink = "1";
	parametersEditorContainer.style.flexBasis = "auto";
	parametersEditorContainer.style.display = "flex";
	parametersEditorContainer.style.flexFlow = "column";
	parametersEditorContainer.appendChild(customTextArea);

	let standardParametersTable = document.getElementById("parametersTable");
	if(standardParametersTable !== null)
	{
		standardParametersTable.remove();
	}

	let mprFileDialogContainer = document.getElementById("mprFileDialogContainer");
	
	if(id === null)
	{
		customTextArea.value = "";
		mprFileDialogContainer.style.visibility = "visible";
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

	document.getElementById("parametersEditionTextArea").value = mpr;
	mprFileDialogContainer.style.visibility = "visible";
}

/**
 * Refreshes the parameters editor using the standard program style.
 * @param {int} id The unique identifier of this Test
 * @param {int} modelId The unique identifier of the model
 * @param {int} typeNo The type's import number
 */
async function refreshParametersStandard(testId, modelId, typeNo)
{
	document.getElementsByTagName("html")[0].style.height = "auto";
	document.getElementsByTagName("body")[0].style.height = "auto";

	let pageWrapper = document.getElementById("page-wrapper");
	pageWrapper.style.display = "block";
	pageWrapper.style.height = "auto";

	let headerWrapper = document.getElementById("header-wrapper");
	headerWrapper.style.flexGrow = "0";
	headerWrapper.style.flexShrink = "0";
	headerWrapper.style.flexBasis = "auto";

	let featuresWrapper = document.getElementById("features-wrapper");
	featuresWrapper.style.flexGrow = "0";
	featuresWrapper.style.flexShrink = "0";
	featuresWrapper.style.flexBasis = "auto";
	featuresWrapper.style.display = "block";

	let parametersFormContainer = document.getElementById("parametersFormContainer");
	parametersFormContainer.style.flexGrow = "0";
	parametersFormContainer.style.flexShrink = "0"; 
	parametersFormContainer.style.flexBasis = "auto";
	
	let standardParametersTable = makeStandardParametersTable();
	standardParametersTable.id = "parametersTable";

	let parametersEditorContainer = document.getElementById("parametersEditorContainer");
	parametersEditorContainer.style.flexGrow = "0";
	parametersEditorContainer.style.flexShrink = "0";
	parametersEditorContainer.style.flexBasis = "auto";
	parametersEditorContainer.style.display = "block";
	parametersEditorContainer.appendChild(standardParametersTable);
	
	let customTextArea = document.getElementById("parametersEditionTextArea");
	if(customTextArea !== null)
	{
		customTextArea.remove();
	}

	try
	{ 
		let parameters = [];
		if(modelId !== null && modelId !== "" && !isNaN(modelId) && typeNo !== null && typeNo !== "" && !isNaN(typeNo))
		{
			parameters = await retrieveParameters(testId, modelId, typeNo);
		}
		fillStandardParametersTable(parameters, testId === null);
		document.getElementById("mprFileDialogContainer").style.visibility = "hidden";
	}
	catch(error){
		showError("La récupération des paramètres de ce test a échouée", error);
	}
}

/**
 * Creates a parameterTable for generic-driven model-types
 * 
 * @return {Element} The new parametersTable
 */
function makeStandardParametersTable()
{
	let keyHeader = document.createElement("th");
	keyHeader.classList.add("firstVisibleColumn", "spaceEfficientText");
	keyHeader.style.width = "10%";
	keyHeader.textContent ="Clé";
	
	let valueHeader = document.createElement("th");
	valueHeader.classList.add("spaceEfficientText");
	valueHeader.style.width = "35%";
	valueHeader.textContent = "Valeur";
	
	let descriptionHeader = document.createElement("th");
	descriptionHeader.classList.add("spaceEfficientText");
	descriptionHeader.style.width = "20%";
	descriptionHeader.textContent = "Description";
	
	let defaultValueHeader = document.createElement("th");
	defaultValueHeader.classList.add("lastVisibleColumn", "spaceEfficientText");
	defaultValueHeader.style.width = "35%";
	defaultValueHeader.textContent = "Valeur par défaut";
	
	let oldValueHeader = document.createElement("th");
	oldValueHeader.style.display = "none";
	oldValueHeader.textContent = "Valeur précédente";
	
	let headerRow = document.createElement("tr");
	headerRow.appendChild(keyHeader);
	headerRow.appendChild(valueHeader);
	headerRow.appendChild(descriptionHeader);
	headerRow.appendChild(defaultValueHeader);
	headerRow.appendChild(oldValueHeader);

	let tableHead = document.createElement("thead");
	tableHead.appendChild(headerRow);

	let table = document.createElement("table");
	table.classList.add("test", "parametersTable");
	table.style.width = "100%";
	table.appendChild(tableHead);
	table.appendChild(document.createElement("tbody"));

	return table;
}

/**
 * Fill the body of a standard parameters table
 * 
 * @param {parameters[]} parameters An array of parameters
 * @param {bool} [isCreating=false] A boolean that determines if the user is creating or updating a Test
 */
function fillStandardParametersTable(parameters, isCreating = false)
{
	let tableBody = document.getElementById("parametersTable").getElementsByTagName("tbody")[0];
	while(tableBody.childElementCount > 0)
	{
		tableBody.firstElementChild.remove();
	}

	if(parameters.length > 0)
	{
		parameters.map(function(parameter){
			tableBody.append(newParameter(parameter, isCreating));
		});
	}
}

/**
 * Creates a textArea for custom model-types edition
 * 
 * @return {Element} The new textarea
 */
function makeCustomTextArea()
{
	let customTextArea = document.createElement("textarea");
	customTextArea.spellcheck = false;
	customTextArea.autocorrect = false;
	customTextArea.classList.add("spaceEfficientText");
	customTextArea.style.resize = "none";
	customTextArea.style.overflowX = "hidden";
	customTextArea.style.flexGrow = "1";
	customTextArea.style.flexShrink = "1";
	customTextArea.style.flexBasis = "auto";
	return customTextArea
}

/**
 * Reads the content of an mpr file and displays it.
 * @param {string} filepath The path to the mpr file
 */
async function readMpr(filepath)
{
	try{
		let mpr = await readFile(filepath, "iso88591");
		document.getElementById("parametersEditionTextArea").value = mpr;
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