class JobTypeParameterRow{
    /**
	 * Creates a JobTypePartRow.
	 * @param {object} [parameter=JobTypeParameterRow.getVirginParameterTemplate()] The Parameter to initialize the JobTypeParameterRow
     * @param {JobTypeBlock} [parent=null] The parent of the JobTypeParameterRow
     * @param {function|null} [onChange=null] A callback function for the onChange event of this JobTypeParameterRow
	 * @return {JobTypeParameterRow} This JobTypeParameterRow
	 */
	constructor(parameter = JobTypeParameterRow.getVirginParameterTemplate(), parent = null, onChange = null){
        this._parent = parent;
        this._onChange = onChange;
        this._row = this.newParameterRow(parameter);
		return Object.seal(this);
    }

     /**
	 * Returns the row's HTML element.
	 * @return {Element} The HTML element of the row
	 */
	getLayout()
	{
		return this._row;
    }
    
    /**
	 * Calls the onChange callback of this JobTypePartRow.
	 * @return This JobTypePartRow
	 */
	async onChange(){
		await typeof this._onChange !== "undefined" && isFunction(this._onChange) ? this._onChange() : null;
		return this;
	}

    /**
	 * Builds a parameter row
	 * @param {object} parameter A parameter
	 * @return {Element} A new parameter row
	 */
	newParameterRow(parameter)
	{
		let keyInput = document.createElement("input");
		keyInput.disabled = true;
		keyInput.value = parameter.key;

		let keyCell = document.createElement("td");
		keyCell.classList.add("firstVisibleColumn");
		keyCell.style.lineHeight = "0px";
		keyCell.appendChild(keyInput);

		let valueInput = document.createElement("input");
		valueInput.value = parameter.value;
		valueInput.addEventListener("change", async () => {await this.onChange();});

		let valueCell = document.createElement("td");
		valueCell.classList.add("lastVisibleColumn");
		valueCell.style.lineHeight = "0px";
        valueCell.appendChild(valueInput);
        
        let descriptionInput = document.createElement("input");
        descriptionInput.value = parameter.description;
        descriptionInput.disabled = true;

        let descriptionCell = document.createElement("td");
		descriptionCell.style.display = "none";
		descriptionCell.style.lineHeight = "0px";
		descriptionCell.appendChild(descriptionInput);

        let defaultValueInput = document.createElement("input");
        defaultValueInput.value = parameter.defaultValue;
        defaultValueInput.disabled = true;

        let defaultValueCell = document.createElement("td");
		defaultValueCell.style.display = "none";
		defaultValueCell.style.lineHeight = "0px";
		defaultValueCell.appendChild(defaultValueInput);

		let row  = document.createElement("tr");
		row.style.height = "23px";
		row.classList.add("parameterRow");
		row.appendChild(keyCell);
        row.appendChild(valueCell);
        row.appendChild(descriptionCell);
        row.appendChild(defaultValueCell);

		return row;
	}

    /**
	 * Returns the template for an empty Part. 
	 * @return {object} A virgin Part template
	 */
	static getVirginParameterTemplate()
	{
		return {
			"key": null,
			"value": null,
			"quickEdit": false
		};
	}
}