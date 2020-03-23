"use strict";

class JobTypeBlock{

	/**
	 * Creates a JobTypeBlock. Should not be called directly. Use the static method build instead.
	 * @param {Element|null} [block=null] The HTML element associated to the JobTypeBlock or null if built from scratch
	 * @param {Function|null} [onChange=null] A callback function for the onChange event of this JobTypeBlock
	 * @return {JobTypeBlock} This JobTypeBlock
	 */
	constructor(block = null, onChange = null){
		if(block instanceof Element && block.classList.contains("blockContainer")){
			this._block = block;
			this._onChange = null;
		}
		else if(!(block instanceof Element)){
			this._block = document.createElement("div");
			this._block.classList.add("blockContainer");
			this.newJobTypeTable();
			this._onChange = onChange;
		}
		else{
			throw("An invalid JobTypeBlock was provided.");
		}
		return Object.seal(this);
	}

	/**
	 * Creates a JobTypeBlock.
	 * @param {object|null} [jobType=null] The JobType to use to initialize the JobTypeBlock
	 * @param {function|null} [onChange=null] A callback function for the onChange event of this JobTypeBlock
	 * @return {JobTypeBlock} This JobTypeBlock
	 */
	static async build(jobType = null, onChange = null){
		let instance;
		if(jobType instanceof Element && jobType.classList.contains("blockContainer"))
		{
			instance = new JobTypeBlock(jobType, onChange);
		}
		else if(jobType === null)
		{
			instance = new JobTypeBlock(null, onChange);
		}
		else
		{
			/* Object was provided by fabplan so most fields need to be remapped */
			let mprFileName = jobType._mprFileName;
			let mprFileContents = jobType._mprFileContents;
			instance = new JobTypeBlock(null, onChange);
			instance._block.dataset.id = jobType._id;
			instance._block.dataset.modelId = jobType._model._id;
			instance._block.dataset.modelDescription = jobType._model._description;
			instance._block.dataset.typeNo = jobType._type._importNo;
			instance._block.dataset.typeDescription = jobType._type._description;
			instance._block.dataset.mprFileName = mprFileName === null ? "" : mprFileName;
			instance._block.dataset.mprFileContents = mprFileContents === null ? "" : mprFileContents;

			await Promise.all(jobType._parameters.map(async function(parameter){
				let genericParameter = jobType._type._generic._parameters.find((genericParameter) => {
					return genericParameter._key === parameter._key;
				});
				return await instance.addParameter({
					"key": parameter._key, 
					"value": parameter._value, 
					"description": genericParameter._description,
					"defaultValue": genericParameter._value,
					"quickEdit": genericParameter._quick_edit
				});
			}));
			await Promise.all(jobType._parts.map(async function(part){
				return await instance.addPart({
					"id": part._id,
					"quantity": part._quantityToProduce,
					"length" : part._length,
					"width": part._width,
					"grain": part._grain
				});
			}));
			instance.resetTitle();
		}
		return instance;
	}

	/**
	 * Calls the onChange callback of this JobTypeBlock.
	 * @return This JobTypeBlock
	 */
	async onChange(){
		return isFunction(this._onChange) ? await this._onChange() : this;
	}

	/**
	 * Returns the block's HTML element.
	 * @return {Element} The HTML element of the JobTypeBlock
	 */
	getLayout(){
		return this._block;
	}

	/**
	 * Creates a JobType table.
	 * @return {Element} A JobType table 
	 */
	newJobTypeTable(){
		let table = document.createElement("table");
		table.classList.add("parametersTable", "hoverEffectDisabled", "jobTypeTable");
		table.style.width = "100%";
		table.style.marginBottom = "23px";
		this._block.appendChild(table);

		let tableHead = document.createElement("thead");
		table.appendChild(tableHead);

		let tableHeadRow = document.createElement("tr");
		tableHead.appendChild(tableHeadRow);

		let tableHeadRowCell = document.createElement("th");
		tableHeadRowCell.colSpan = "2";
		tableHeadRowCell.classList.add("titreBleu", "firstVisibleColumn", "lastVisibleColumn");
		tableHeadRowCell.style.paddingLeft = "10px";
		tableHeadRowCell.style.paddingRight = "10px";
		tableHeadRow.appendChild(tableHeadRowCell);

		let descriptionSpan = document.createElement("span");
		descriptionSpan.classList.add("jobTypeIdentifier");
		descriptionSpan.style.float = "left";
		tableHeadRowCell.appendChild(descriptionSpan);

		let removeButton = imageButton(ROOT_URL + "/images/cancel16.png", "Supprimer", () => {this.remove();});
		removeButton.style.float = "right";
		removeButton.style.marginLeft = "10px";
		removeButton.style.color = "#FFFFFF";
		tableHeadRowCell.appendChild(removeButton);

		let modifyButton = imageButton(ROOT_URL + "/images/edit.png", "Modifier", async () => {this.modify();});
		modifyButton.style.float = "right";
		modifyButton.style.marginLeft = "10px";
		modifyButton.style.color = "#FFFFFF";
		tableHeadRowCell.appendChild(modifyButton);

		let tableBody = document.createElement("tbody");
		table.appendChild(tableBody);

		let tableBodyRow = document.createElement("tr");
		tableBody.appendChild(tableBodyRow);
		
		let tableBodyRowCell1 = document.createElement("td");
		tableBodyRowCell1.classList.add("firstVisibleColumn", "jobTypeParametersTableContainer");
		tableBodyRowCell1.style.verticalAlign = "top";
		tableBodyRowCell1.style.width = "25%";
		tableBodyRow.appendChild(tableBodyRowCell1);
		
		let tableBodyRowCell2 = document.createElement("td");
		tableBodyRowCell2.classList.add("lastVisibleColumn", "jobTypePartsTableContainer");
		tableBodyRowCell2.style.verticalAlign = "top";
		tableBodyRowCell2.style.width = "75%";
		tableBodyRow.appendChild(tableBodyRowCell2);

		this.newParametersTable();
		this.newPartsTable();
		return table;
	}

	/**
	 * Resets the title of the JobTypeBlock.
	 * @return {JobTypeBlock} This JobTypeBlock
	 */
	resetTitle(){
		let title = this._block.dataset.modelDescription + "-" + this._block.dataset.typeDescription;
		this._block.getElementsByClassName("jobTypeIdentifier")[0].textContent = title;
		return this;
	}

	/**
	 * gets the title of the JobTypeBlock.
	 * @return {string} This JobTypeBlock's title
	 */
	getTitle(){
		return this._block.getElementsByClassName("jobTypeIdentifier")[0].textContent;
	}	

	/**
	 * Creates a new parameters table
	 * @param {object|null} jobType The JobType to use to initialize the block
	 * @return {Element} A new parameters table
	 */
	newParametersTable(){
		let jobTypeTable = this._block.getElementsByClassName("jobTypeTable")[0];

		let table  = document.createElement("table");
		table.classList.add("parametersTable", "hoverEffectDisabled", "noExternalBorder", "jobtypeParametersTable");
		table.style.width = "100%";
		jobTypeTable.getElementsByClassName("jobTypeParametersTableContainer")[0].appendChild(table);

		let tableHead = document.createElement("thead");
		table.appendChild(tableHead);

		let tableHeadRow1 = document.createElement("tr");
		tableHead.appendChild(tableHeadRow1);

		let tableHeadRow1Cell1 = document.createElement("th");
		tableHeadRow1Cell1.colSpan = "2";
		tableHeadRow1Cell1.classList.add("titreBleu", "firstVisibleColumn", "lastVisibleColumn");
		tableHeadRow1Cell1.textContent = "Paramètres";
		tableHeadRow1.appendChild(tableHeadRow1Cell1);

		let tableHeadRow2 = document.createElement("tr");
		tableHead.appendChild(tableHeadRow2);

		let tableHeadRow2Cell1 = document.createElement("th");
		tableHeadRow2Cell1.classList.add("titreBleu", "firstVisibleColumn");
		tableHeadRow2Cell1.style.width = "50%";
		tableHeadRow2Cell1.textContent = "Clé";
		tableHeadRow2.appendChild(tableHeadRow2Cell1);

		let tableHeadRow2Cell2 = document.createElement("th");
		tableHeadRow2Cell2.classList.add("titreBleu", "lastVisibleColumn");
		tableHeadRow2Cell2.style.width = "50%";
		tableHeadRow2Cell2.textContent = "Valeur";
		tableHeadRow2.appendChild(tableHeadRow2Cell2);

		let tableHeadRow2Cell3 = document.createElement("th");
		tableHeadRow2Cell3.style.display = "none";
		tableHeadRow2Cell3.classList.add("titreBleu");
		tableHeadRow2Cell3.textContent = "Description";
		tableHeadRow2.appendChild(tableHeadRow2Cell3);

		let tableHeadRow2Cell4 = document.createElement("th");
		tableHeadRow2Cell4.style.display = "none";
		tableHeadRow2Cell4.classList.add("titreBleu");
		tableHeadRow2Cell4.textContent = "Valeur par défaut";
		tableHeadRow2.appendChild(tableHeadRow2Cell4);

		let tableBody = document.createElement("tbody");
		table.appendChild(tableBody);

		return table;
	}

	/**
	 * Creates a Parts table.
	 * @param {object|null} jobType The JobType to use to initialize the block
	 * @return {Element} A new Parts table
	 */
	newPartsTable(){	
		let jobTypeTable = this._block.getElementsByClassName("jobTypeTable")[0];

		let table  = document.createElement("table");
		table.classList.add("parametersTable", "hoverEffectDisabled", "noExternalBorder", "jobtypePartsTable");
		table.style.width = "100%";
		jobTypeTable.getElementsByClassName("jobTypePartsTableContainer")[0].appendChild(table);

		let tableHead = document.createElement("thead");
		table.appendChild(tableHead);

		let tableBody = document.createElement("tbody");
		table.appendChild(tableBody);

		let tableHeadRow1 = document.createElement("tr");
		tableHead.appendChild(tableHeadRow1);

		let tableHeadRow1Cell1 = document.createElement("th");
		tableHeadRow1Cell1.colSpan = "5";
		tableHeadRow1Cell1.classList.add("titreBleu", "firstVisibleColumn", "lastVisibleColumn");
		tableHeadRow1.appendChild(tableHeadRow1Cell1);

		let tableHeadRow1Cell1Title = document.createElement("span");
		tableHeadRow1Cell1Title.style.textAlign = "center";
		tableHeadRow1Cell1Title.style.flexGrow = "1";
		tableHeadRow1Cell1Title.style.flexShrink = "1";
		tableHeadRow1Cell1Title.style.flexBasis = "auto";
		tableHeadRow1Cell1Title.textContent = "Éléments";
		tableHeadRow1Cell1.appendChild(tableHeadRow1Cell1Title);

		let addButton = imageButton(ROOT_URL + "/images/add.png", "Ajouter", async () => {await this.addPart();});
		addButton.style.float = "right";
		addButton.style.marginLeft = "10px";
		addButton.style.color = "#FFFFFF"
		tableHeadRow1Cell1.appendChild(addButton);

		let minusButton = imageButton(ROOT_URL + "/images/minus.png", "Vider", async () => {await this.emptyPartsTable();});
		minusButton.style.float = "right";
		minusButton.style.marginLeft = "10px";
		minusButton.style.color = "#FFFFFF"
		tableHeadRow1Cell1.appendChild(minusButton);
		
		let tableHeadRow2 = document.createElement("tr");
		tableHead.appendChild(tableHeadRow2);

		let tableHeadRow2Cell1 = document.createElement("th");
		tableHeadRow2Cell1.classList.add("titreBleu", "firstVisibleColumn");
		tableHeadRow2Cell1.style.width = "12%";
		tableHeadRow2.appendChild(tableHeadRow2Cell1);

		let tableHeadRow2Cell2 = document.createElement("th");
		tableHeadRow2Cell2.disabled = true;
		tableHeadRow2Cell2.classList.add("titreBleu");
		tableHeadRow2Cell2.style.display = "none";
		tableHeadRow2Cell2.textContent = "ID";
		tableHeadRow2.appendChild(tableHeadRow2Cell2);

		let tableHeadRow2Cell3 = document.createElement("th");
		tableHeadRow2Cell3.classList.add("titreBleu");
		tableHeadRow2Cell3.style.width = "22%";
		tableHeadRow2Cell3.style.textAlign = "center";
		tableHeadRow2Cell3.textContent = "Quantité";
		tableHeadRow2.appendChild(tableHeadRow2Cell3);

		let tableHeadRow2Cell4 = document.createElement("th");
		tableHeadRow2Cell4.classList.add("titreBleu");
		tableHeadRow2Cell4.style.width = "22%";
		tableHeadRow2Cell4.style.textAlign = "center";
		tableHeadRow2Cell4.textContent = "Hauteur";
		tableHeadRow2.appendChild(tableHeadRow2Cell4);

		let tableHeadRow2Cell5 = document.createElement("th");
		tableHeadRow2Cell5.classList.add("titreBleu");
		tableHeadRow2Cell5.style.width = "22%";
		tableHeadRow2Cell5.style.textAlign = "center";
		tableHeadRow2Cell5.textContent = "Largeur";
		tableHeadRow2.appendChild(tableHeadRow2Cell5);

		let tableHeadRow2Cell6 = document.createElement("th");
		tableHeadRow2Cell6.classList.add("titreBleu", "lastVisibleColumn");
		tableHeadRow2Cell6.style.width = "22%";
		tableHeadRow2.appendChild(tableHeadRow2Cell6);
		
		let tableHeadRow2Cell6Title =  document.createElement("span");
		tableHeadRow2Cell6Title.style.marginLeft = "5px";
		tableHeadRow2Cell6Title.style.marginRight = "5px";
		tableHeadRow2Cell6Title.textContent = "Grain";
		tableHeadRow2Cell6.appendChild(tableHeadRow2Cell6Title);
		
		let options = {"": "Tous", "N": "Aucun", "X": "Horizontal", "Y": "Vertical"};
		let grainSelect = selectBox(options, async (grain) => {
			await this.setGrainForAllParts(grain);
			grainSelect.value = "";
		});
		grainSelect.classList.add("grainSelectBox");
		grainSelect.style.float = "right";
		tableHeadRow2Cell6.appendChild(grainSelect);

		return table;
	}
	
	/**
	 * Sets the grain for all the parts in a block
	 * @param {string} grain The new value of grain
	 * @return {JobTypeBlock} This JobTypeBlock
	 */
	async setGrainForAllParts(grain){
		let partsTable = this._block.getElementsByClassName("jobtypePartsTable")[0];
		await Promise.all([...partsTable.getElementsByTagName("tbody")[0].getElementsByClassName("grainSelectBox")].map(function(select){
			select.value = grain;
		}));
		partsTable.getElementsByTagName("thead")[0].getElementsByClassName("grainSelectBox")
		return this;
	}

	/**
	 * Removes all parts from this JobTypeBlock.
	 * @return {JobTypeblock} This JobTypeBlock.
	 */
	async emptyPartsTable(){
		await Promise.all([...this._block.getElementsByClassName("partRow")].map(function(part){part.remove();}));
		return this;
	}

	/**
	 * Removes all parameters from this JobTypeBlock.
	 * @return {JobTypeblock} This JobTypeBlock.
	 */
	async emptyParametersTable(){
		await Promise.all([...this._block.getElementsByClassName("parameterRow")].map(function(parameter){parameter.remove();}));
		return this;
	}

	/**
	 * Adds a part to this JobTypeBlock
	 * @param {object} [part=Part.getVirginPartTemplate()] A Part object
	 * @return {JobTypeBlock} This JobTypeBlock
	 */
	async addPart(part = JobTypePartRow.getVirginPartTemplate()){
		let partRow = new JobTypePartRow(part, this).getLayout();
		this._block.getElementsByClassName("jobtypePartsTable")[0].getElementsByTagName("tbody")[0].append(partRow);
		return await this.onChange();
	}

	/**
	 * Adds a parameter to this JobTypeBlock
	 * @param {object} parameter The parameter to add
	 * @return {JobTypeBlock} This JobTypeBlock
	 */
	async addParameter(parameter){
		let jobTypeParametersTable = this._block.getElementsByClassName("jobtypeParametersTable")[0];
		let parameterRow = new JobTypeParameterRow(parameter).getLayout();
		if(isPositiveInteger(parameter.quickEdit, true, true)){
			parameterRow.classList.add("quickEditParameter");
			let color = jobTypeParametersTable.getElementsByClassName("quickEditParameter").length % 2 ? "#FFFFFF" : "#97BFD9";
			parameterRow.style.backgroundColor = color;
		}
		else{
			parameterRow.style.display = "none";
		}
		jobTypeParametersTable.getElementsByTagName("tbody")[0].appendChild(parameterRow);
		return await this.onChange();
	}

	/**
	 * Removes the JobTypeBlock from the page.
	 * @return This JobTypeBlock
	 */
	async remove(){
		this._block.parentElement.childElementCount > 1 ? await this._block.remove() : await this.emptyPartsTable();
		return await this.onChange();
	}

	/**
	 * Displays the jobType editor.
	 * @return {JobTypeBlock} This JobTypeBlock
	 */
	async modify(){
		this.toSessionStorage();
		await new ParameterEditor().open(async () => {await this.fromSessionStorage();});
		return this;
	}

	/**
	 * Stores this JobTypeBlock in the session storage possibly before external manipulation. 
	 * @return {JobTypeBlock} This JobTypeBlock
	 */
	toSessionStorage(){
		window.sessionStorage.jobType = JSON.stringify({
			"id": this._block.dataset.id,
			"model": {"id": this._block.dataset.modelId, "description": this._block.dataset.modelDescription},
			"type": {"importNo": this._block.dataset.typeNo, "description": this._block.dataset.typeDescription},
			"parameters": [...this._block.getElementsByClassName("parameterRow")].map(function(parameterRow){
				return {
					"key": parameterRow.getElementsByTagName("td")[0].getElementsByTagName("input")[0].value, 
					"value": parameterRow.getElementsByTagName("td")[1].getElementsByTagName("input")[0].value,
					"description": parameterRow.getElementsByTagName("td")[2].getElementsByTagName("input")[0].value,
					"defaultValue": parameterRow.getElementsByTagName("td")[3].getElementsByTagName("input")[0].value,
					"quickEdit": parameterRow.style.display !== "none" ? 1 : 0
				};
			}),
			"parts": [...this._block.getElementsByClassName("partRow")].map(function(partRow){
				return {
					"id": partRow.getElementsByTagName("td")[1].getElementsByTagName("input")[0].value,
					"quantity": partRow.getElementsByTagName("td")[2].getElementsByTagName("input")[0].value,
					"length": partRow.getElementsByTagName("td")[3].getElementsByTagName("input")[0].value,
					"width": partRow.getElementsByTagName("td")[4].getElementsByTagName("input")[0].value,
					"grain": partRow.getElementsByTagName("td")[5].getElementsByTagName("select")[0].value
				};
			}),
			"mprFileName": this._block.dataset.mprFileName,
			"mprFileContents": this._block.dataset.mprFileContents
		});
	}

	/**
	 * Returns the template for an empty JobType. 
	 * @return {object} A virgin JobType template
	 */
	static getVirginJobTypeTemplate(){
		return {
			"model": {"id": null, "description": null},
			"type": {"importNo": null, "description": null},
			"parameters": [],
			"parts": [],
			"mprFileName": null,
			"mprFileContents": null
		};
	}

	/**
	 * Restores this JobTypeBlock from the session storage possibly after external manipulation. 
	 * @return {JobTypeBlock} This JobTypeBlock
	 */
	async fromSessionStorage(){
		let jobType = JSON.parse(window.sessionStorage.jobType);
		this._block.dataset.id = jobType.id;
		this._block.dataset.modelId = jobType.model.id;
		this._block.dataset.modelDescription = jobType.model.description;
		this._block.dataset.typeNo = jobType.type.importNo;
		this._block.dataset.typeDescription = jobType.type.description;
		await (await this.emptyParametersTable()).emptyPartsTable();
		jobType.parameters.map((parameter) => {this.addParameter(parameter);});
		jobType.parts.map((part) => {this.addPart(part);});
		this._block.dataset.mprFileName = isString(jobType.mprFileName) ? jobType.mprFileName : "";
		this._block.dataset.mprFileContents = isString(jobType.mprFileContents) ? jobType.mprFileContents : "";
		return await (this.resetTitle()).onChange();
	}
	
	/**
	 * Clones this JobTypeBlock. 
	 * @param {Boolean} [eraseParts=false] When true, the clone has no part
	 * @return {JobTypeBlock} A clone of this JobTypeBlock
	 */
	async clone(eraseParts = false){
		this.toSessionStorage();
		let clone = await JobTypeBlock.build(null, this._onChange)
		await clone.fromSessionStorage();
		clone._block.dataset.id = "";
		return eraseParts ? await clone.emptyPartsTable() : clone;
	}
}