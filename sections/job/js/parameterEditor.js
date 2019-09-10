"use strict";

class ParameterEditor{
	/**
	 * Creates a ParameterEditor.
	 * @return {ParameterEditor} This ParameterEditor
	 */
	constructor(){
		if (!ParameterEditor.instance) 
        {
			this._parameterEditor = document.getElementById("parametersEditor");
			document.getElementById("acceptEdit").addEventListener("click", () => {this.acceptEdit();});
			document.getElementById("cancelEdit").addEventListener("click", () => {this.cancelEdit();});
			document.getElementById("modelId").addEventListener("change", async () => {
				await this.onModelOrTypeChange();
			});
			document.getElementById("typeNo").addEventListener("change", async () => {
				await this.onModelOrTypeChange();
			});
			document.getElementById("mprFileSelectionInputBox").addEventListener("change", async () => {
				let mprFileInput = document.getElementById("mprFileSelectionInputBox");
				if(mprFileInput.files.length > 0){
					await this.readMpr(mprFileInput.files[0]);
					mprFileInput.value = "";
				}
			});
			this._acceptCallBack = null;
			this._cancelCallBack = null;
            ParameterEditor.instance = Object.seal(this);
        }
        return ParameterEditor.instance;
	}

	/**
	 * Handles the change of model or type.
	 * @return {ParameterEditor} This ParameterEditor
	 */
	async onModelOrTypeChange()
	{
		try{
			document.getElementById("loadingModal").style.display = "block";
			if(parseInt(document.getElementById("modelId").value) === 2){
				let mprFile = document.getElementById("mprFileContents").value;
				if(mprFile !== "" && mprFile !== null){
					this.reload(mprFile);
				}
				else{
					this.reload(JSON.parse(window.sessionStorage.jobType).mprFile);
				}
			}
			else{
				this.reload(await this.retrieveModelTypeGenericParameters());
			}
		}
		catch{
			showError("Le chargement de l'éditeur de paramètres a échoué.", error);
		}
		finally{
			document.getElementById("loadingModal").style.display = "none";
		}
		return this;
	}

	/**
	 * Accepts edition of parameters.
	 * @return {ParameterEditor} This ParameterEditor
	 */
	async acceptEdit()
	{
		this.toSessionStorage();
		isFunction(this._acceptCallBack) ? await this._acceptCallBack() : null;
		return this.close();
	}

	/**
	 * Cancels edition of parameters.
	 * @return {ParameterEditor} This ParameterEditor
	 */
	async cancelEdit()
	{
		isFunction(this._cancelCallBack) ? await this._cancelCallBack() : null;
		return this.close();
	}

	/**
	 * Opens the parameterEditor
	 * @param {Function|null} [acceptCallBack=null] A callback function for the accept event of this ParameterEditor
	 * @param {Function|null} [cancelCallBack=null] A callback function for the cancel event of this ParameterEditor
	 * @return {ParameterEditor} This ParameterEditor
	 */
	async open(acceptCallBack = null, cancelCallBack = null)
	{
		try{
			this._acceptCallBack = acceptCallBack;
			this._cancelCallBack = cancelCallBack;
			this._parameterEditor.style.display = "block";
			return await this.fromSessionStorage();
		}
		catch(error){
			showError("L'ouverture de l'éditeur de paramètres a échouée.", error);
		}
	}

	/**
	 * Configures the ParameterEditor with the data from session storage.
	 * @return {ParameterEditor} This ParameterEditor
	 */
	async fromSessionStorage(){
		let jobType = JSON.parse(window.sessionStorage.jobType);
		document.getElementById("modelId").value = jobType.model.id;
		document.getElementById("typeNo").value = jobType.type.importNo;
		if(isInteger(jobType.model.id, true) && parseInt(jobType.model.id) !== 2){
			this.reload(jobType.parameters);
		}
		else{
			this.reload(jobType.mprFile);
		}
		return this;
	}

	/**
	 * Saves the ParameterEditor to the session storage.
	 * @return {ParameterEditor} This ParameterEditor
	 */
	toSessionStorage(){
		let jobType = JSON.parse(window.sessionStorage.jobType);
		jobType.model.id = document.getElementById("modelId").value;
		jobType.model.description = document.getElementById("modelId")[document.getElementById("modelId").selectedIndex].text;
		jobType.type.importNo = document.getElementById("typeNo").value;
		jobType.type.description = document.getElementById("typeNo")[document.getElementById("typeNo").selectedIndex].text;
		jobType.mprFile = document.getElementById("mprFileContents").value;
		let parametersTableBody = document.getElementById("parametersArray").getElementsByTagName("tbody")[0];
		jobType.parameters = [...parametersTableBody.getElementsByTagName("tr")].map((parameterRow) => {
			return {
				"key": parameterRow.getElementsByTagName("td")[0].getElementsByTagName("input")[0].value,
				"value": parameterRow.getElementsByTagName("td")[1].getElementsByTagName("textarea")[0].value,
				"description": parameterRow.getElementsByTagName("td")[2].getElementsByTagName("textarea")[0].value,
				"defaultValue": parameterRow.getElementsByTagName("td")[3].getElementsByTagName("textarea")[0].value,
				"quickEdit": parameterRow.getElementsByTagName("td")[4].getElementsByTagName("input")[0].value,
			};
		});
		window.sessionStorage.jobType = JSON.stringify(jobType);
		return this;
	}

	/**
	 * Closes the parameterEditor
	 * @return {ParameterEditor} This ParameterEditor
	 */
	close(){
		this._parameterEditor.style.display = "none";
		return this;
	}

	/** Reloads the parameters editor.
	 * @param {object[]|string} [parametersOrMprFile=[]] An array of parameters or a mpr file.
	 * @return {ParameterEditor} This ParameterEditor
	 */
	reload(parametersOrMprFile = []){
		if(isArray(parametersOrMprFile))
		{
			let parametersTableBody = document.getElementById("parametersArray").getElementsByTagName("tbody")[0];
			this.emptyParametersTable();
			parametersOrMprFile.map((parameter) => {
				parametersTableBody.appendChild(this.newParameterRow(parameter));
			});
			this.switchEditionMode(0);
		}
		else if(isString(parametersOrMprFile, false)){
			document.getElementById("mprFileContents").value = parametersOrMprFile;
			this.switchEditionMode(1);
		}
		else{
			throw "The parameters editors was reloaded with an invalid parameters array or mpr file."
		}
		return this;
	}

	/**
	 * Changes edition mode
	 * @param {int} [mode=0] When mode is 0, edition is made possible by using the parameters table. Otherwise, edition uses the custom 
	 * 						 file textarea.
	 * @return {ParameterEditor} This ParameterEditor
	 */
	switchEditionMode(mode = 0){
		if (mode === 0)
		{
			document.getElementById("customFileTableBody").style.display = "none";
			document.getElementById("parametersArray").style.display = "table";
			document.getElementById("parametersEditorTypeSelectionRow").style.display = "table-row";
			document.getElementById("parametersEditorMprFileSelectionRow").style.display = "none";
			let modalContent = this._parameterEditor.getElementsByClassName("modal-content")[0];
			modalContent.style.display = "block";
			modalContent.style.height = "auto";
		}
		else
		{
			document.getElementById("customFileTableBody").style.display = "block";
			document.getElementById("parametersArray").style.display = "none";
			document.getElementById("parametersEditorTypeSelectionRow").style.display = "none";
			document.getElementById("parametersEditorMprFileSelectionRow").style.display = "table-row";
			let modalContent = this._parameterEditor.getElementsByClassName("modal-content")[0];
			modalContent.style.display = "flex";
			modalContent.style.height = "100%";
		}
		return this;
	}

	/**
	 * Empties the parameters table.
	 * @return {ParameterEditor} This ParameterEditor
	 */
	emptyParametersTable()
	{
		let parametersTableBody = document.getElementById("parametersArray").getElementsByTagName("tbody")[0];
		while(parametersTableBody.childElementCount > 0)
		{
			parametersTableBody.firstChild.remove();
		}
		return this;
	}

	/**
	 * Creates a parameter row for the parameters table.
	 * @param {Object} parameter A parameter that possesses the following properties (key, value, description, defaultValue and quickEdit)
	 * @return {Element} A new parameter row
	 */
	newParameterRow(parameter)
	{
		if(!parameter.hasOwnProperty("key") || !parameter.hasOwnProperty("value") || !parameter.hasOwnProperty("defaultValue")){
			throw("The parameter is missing at least one of the following properties : key, value and defaultValue.");
		}

		let row = document.createElement("tr");
		
		let keyCell = document.createElement("td");
		keyCell.classList.add("firstVisibleColumn");
		row.appendChild(keyCell);

		let keyInput = document.createElement("input");
		keyInput.disabled = true;
		keyInput.style.width = "100%";
		keyInput.style.fontSize = "1em";
		keyInput.value = parameter.key;
		keyCell.appendChild(keyInput);

		let valueCell = document.createElement("td");
		valueCell.style.backgroundColor = (parameter.value === parameter.defaultValue) ? "auto" : "#FFFF00";
		row.appendChild(valueCell);

		let valueTextArea = document.createElement("textarea");
		valueTextArea.classList.add("nonResizable");
		valueTextArea.style.position = "relative";
		valueTextArea.style.width = "100%";
		valueTextArea.style.fontSize = "1em";
		valueTextArea.style.lineHeight = "1.25em";
		valueTextArea.textContent = parameter.value;
		valueCell.appendChild(valueTextArea);

		let descriptionCell = document.createElement("td");
		descriptionCell.classList.add("lastVisibleColumn");
		row.appendChild(descriptionCell);

		let descriptionTextArea = document.createElement("textarea");
		descriptionTextArea.classList.add("nonResizable");
		descriptionTextArea.disabled = true;
		descriptionTextArea.style.position = "relative";
		descriptionTextArea.style.width = "100%";
		descriptionTextArea.style.fontSize = "1em";
		descriptionTextArea.style.lineHeight = "1.25em";
		descriptionTextArea.textContent = parameter.hasOwnProperty("description") ? parameter.description : "";
		descriptionCell.appendChild(descriptionTextArea);

		let defaultValueCell = document.createElement("td");
		defaultValueCell.style.display = "none";
		row.appendChild(defaultValueCell);

		let defaultValueTextArea = document.createElement("textarea");
		defaultValueTextArea.classList.add("nonResizable");
		defaultValueTextArea.disabled = true;
		defaultValueTextArea.style.width = "100%";
		defaultValueTextArea.style.fontSize = "1em";
		defaultValueTextArea.style.lineHeight = "1.25em";
		defaultValueTextArea.textContent = parameter.defaultValue;
		defaultValueCell.appendChild(defaultValueTextArea);

		let quickEditCell = document.createElement("td");
		quickEditCell.style.display = "none";
		row.appendChild(quickEditCell);

		let quickEditInput = document.createElement("input");
		quickEditInput.disabled = true;
		quickEditInput.style.width = "100%";
		quickEditInput.style.fontSize = "1em";
		quickEditInput.value = parameter.hasOwnProperty("quickEdit") ? parameter.quickEdit : 0;
		quickEditCell.appendChild(quickEditInput);

		return row;
	}

	/**
	 * Retrieves the parameters for the selected ModelTypeGeneric combination
	 * @return {Promise}
	 */
	async retrieveModelTypeGenericParameters()
	{
		return new Promise(function(resolve, reject){
			ajax.send({
				"type": "GET",
				"contentType": "application/json;charset=utf-8",
				"url": ROOT_URL + "/parametres/varmodtypegen/actions/getParameters.php",
				"data": {
					"modelId": document.getElementById("modelId").value, 
					"typeNo": document.getElementById("typeNo").value
				},
				"dataType": "json",
				"async": true,
				"cache": false,
				"onSuccess": function(response){
					if(response.status === "success"){
						resolve(response.success.data);
					}
					else{
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
	 * Reads the content of an mpr file and displays it.
	 * @param {string} filepath The path to the mpr file
	 * @return {ParameterEditor} This ParameterEditor
	 */
	async readMpr(filepath)
	{
		try{
			let mpr = await readFile(filepath, "iso88591");
			document.getElementById("mprFileContents").value = mpr;
		}
		catch(error){
			showError("La lecture du fichier a échouée", error);
		}
		return this;
	}
}