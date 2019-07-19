class JobTypePartRow{
    /**
	 * Creates a JobTypePartRow.
	 * @param {object} [part=JobTypePartRow.getVirginPartTemplate()] The Part to use to initialize the JobTypePartRow
     * @param {JobTypeBlock} [parent=null] The parent of the JobTypePartRow
     * @param {function|null} [onChange=null] A callback function for the onChange event of this JobTypePartRow
	 * @return {JobTypePartRow} This JobTypePartRow
	 */
	constructor(part = JobTypePartRow.getVirginPartTemplate(), parent = null, onChange = null){
        this._parent = parent;
        this._onChange = onChange;
        this._row = this.newPartRow(part);
		return Object.seal(this);
    }

    /**
	 * Returns the row's HTML element.
	 * @return {Element} The HTML element of the row
	 */
	getLayout(){
		return this._row;
    }
    
    /**
	 * Calls the onChange callback of this JobTypePartRow.
	 * @return This JobTypePartRow
	 */
	async onChange(){
		if(typeof this._onChange !== "undefined" && isFunction(this._onChange)){
			await this._onChange();
		}
		else {
			await this._parent.onChange();
		}
		return this;
	}
    
    /**
	 * Creates a new part row.
	 * @param {object} part The part used to initialize the part row
	 * @return {Element} A new part row. 
	 */
	newPartRow(part)
	{	
		let row = document.createElement("tr");

		let modifyTool = imageButton(ROOT_URL + "/images/edit.png", "", async () => {await this.openOperationsModalWindow();});
		modifyTool.style.marginLeft = "10px";
		
		let deleteTool = imageButton(ROOT_URL + "/images/cancel16.png", "", async () => {await this.remove();});
		deleteTool.style.marginLeft = "10px";

		let toolsCell = document.createElement("td");
		toolsCell.classList.add("firstVisibleColumn");
		toolsCell.style.lineHeight = "0px";
		toolsCell.appendChild(deleteTool);
		toolsCell.appendChild(modifyTool);

		let idInput = document.createElement("input");
		idInput.value = (typeof part !== "undefined" && typeof part.id !== "undefined") ? part.id : "";

		let idCell = document.createElement("td");
		idCell.style.lineHeight = "0px";
		idCell.style.display = "none";
		idCell.appendChild(idInput);

		let quantityInput = document.createElement("input");
		quantityInput.value = typeof part !== "undefined" && typeof part.quantity !== "undefined" ? part.quantity : "";
		quantityInput.addEventListener("change", async () => {await this.onChange();});

		let quantityCell = document.createElement("td");
		quantityCell.style.lineHeight = "0px";
		quantityCell.appendChild(quantityInput);

		let lengthInput = document.createElement("input");
		lengthInput.value = (typeof part !== "undefined" && typeof part.length !== "undefined") ? part.length : "";
		lengthInput.addEventListener("change", async () => {await this.onChange();});

		let lengthCell = document.createElement("td");
		lengthCell.style.lineHeight = "0px";
		lengthCell.appendChild(lengthInput);

		let widthInput = document.createElement("input");
		widthInput.value = (typeof part !== "undefined" && typeof part.width !== "undefined") ? part.width : "";
		widthInput.addEventListener("change", async () => {await this.onChange();});

		let widthCell = document.createElement("td");
		widthCell.style.lineHeight = "0px";
		widthCell.appendChild(widthInput);

		let grain = (typeof part !== "undefined" && typeof part.grain !== "undefined") ? part.grain : null;
        let options = {"N": "Aucun", "X": "Horizontal", "Y": "Vertical"}
		let grainCell = document.createElement("td");
		grainCell.classList.add("lastVisibleColumn");
		grainCell.style.lineHeight = "0px";
		grainCell.appendChild(this.newGrainSelectBox(options, grain, async () => {await this.onChange();}));

		row.style.height = "23px";
		row.classList.add("partRow");
		row.appendChild(toolsCell);
		row.appendChild(idCell);
		row.appendChild(quantityCell);
		row.appendChild(lengthCell);
		row.appendChild(widthCell);
		row.appendChild(grainCell);

		return row;
    }
    
    /**
	 * Creates a grain select box.
	 * @param {object} [options={}] An  object listing the options as value: text pairs with the first pair being the selected option
     * @param {string|null} [selectedValue=null] The initial value of the select box
	 * @param {function|null} [callback=null] The onchange callback function of the select box
	 * @return {Element} A grain select box
	 */
	newGrainSelectBox(options = {}, selectedValue = null, callback = null)
	{
        let grainSelect = selectBox(options, callback);
        grainSelect.classList.add("grainSelectBox");
        grainSelect.value = options.hasOwnProperty(selectedValue) ? selectedValue : grainSelect.value;
		return grainSelect;
	}

    /**
	 * Removes a JobTypePartRow.
	 * @return {JobTypePartRow} This JobTypePartRow
	 */
	async remove(){
        this._row.remove();
        await this.onChange();
    }
    
    /**
	 * Opens the part movement modal window.
	 * @return {JobTypePartRow} This JobTypePartRow
	 */
	async openOperationsModalWindow()
	{
		let partRow = this;
        let jobTypeBlocksList = document.getElementById("jobTypeBlocksList");
        while(jobTypeBlocksList.childElementCount > 0)
        {
            jobTypeBlocksList.firstElementChild.remove();
        }
		
		/* Déplacer l'élément entre les blocs existants. */
		await Promise.all([...document.getElementsByClassName("blockContainer")].map(async (block) => {
            let jobTypeBlock = await JobTypeBlock.build(block);
            if(jobTypeBlock.getLayout() !== partRow._parent.getLayout()){
				let titleSpan = document.createElement("span");
                titleSpan.textContent = jobTypeBlock.getTitle();

                let container = document.createElement("div");
                container.style.cursor = "pointer";
                container.style.width = "fit-content";
                container.appendChild(titleSpan);
                container.addEventListener("click", async () => {
                    await this.moveToJobTypeBlock(jobTypeBlock);
                });

                jobTypeBlocksList.appendChild(container);
            }
            else{
                /* The part is already in this block. */
            }
		}));
		
		/* Déplacer l'élément vers un nouveau bloc. */
        let titleSpan = document.createElement("span");
        titleSpan.textContent = "Nouveau bloc";

        let container = document.createElement("div");
        container.style.cursor = "pointer";
        container.style.width = "fit-content";
        container.appendChild(titleSpan);
        container.addEventListener("click", async () => {
            let jobTypeBlock = await this._parent.clone(true);
            this._parent.getLayout().parentElement.appendChild(jobTypeBlock.getLayout());
            await this.moveToJobTypeBlock(jobTypeBlock);
        });

        jobTypeBlocksList.appendChild(container);
        document.getElementById("partOperationsModal").style.display = "block";
        return this;
    }
    
    /**
     * Moves a part from a block to another block
     * @param {JobTypeBlock} jobTypeBlock The destination JobTypeBlock
     * @return {null} null
     */
    async moveToJobTypeBlock(jobTypeBlock)
    {
        await this.remove();
        await jobTypeBlock.addPart({
            "id": this._row.getElementsByTagName("td")[1].getElementsByTagName("input")[0].value,
            "quantity": this._row.getElementsByTagName("td")[2].getElementsByTagName("input")[0].value,
            "length" : this._row.getElementsByTagName("td")[3].getElementsByTagName("input")[0].value,
            "width": this._row.getElementsByTagName("td")[4].getElementsByTagName("input")[0].value,
            "grain": this._row.getElementsByTagName("td")[5].getElementsByTagName("select")[0].value
        });
        return null;
    }

    /**
	 * Returns the template for an empty Part. 
	 * @return {object} A virgin Part template
	 */
	static getVirginPartTemplate()
	{
		return {
			"id": null,
			"quantity": null,
			"length": null,
			"width": null,
            "grain": null
		};
	}
}