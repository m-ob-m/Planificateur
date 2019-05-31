"use strict";

/**
 * Creates a new interface for a JobType
 * 
 * @param {object} jobType An object that represents a JobType
 */
function newJobType(jobType)
{
	let jobTypeContainer = $("<div></div>");
	jobTypeContainer.addClass("blockContainer").data(jobType).append(newJobTypeTable(jobTypeContainer));
	$(jobType.parts).each(function(){addPart.apply(jobTypeContainer, [this]);});
	refreshJobTypeBlock.apply(jobTypeContainer);
	
	return jobTypeContainer;
}

/**
 * Refreshes information displaye for the selected block. 
 * @this {jquery} The JobType block.
 */
function refreshJobTypeBlock()
{
	let modelDescription = ((this.data("model") === null) ? "Modèle inconnu" : this.data("model").description);
	let typeDescription = ((this.data("type") === null) ? "Type inconnu" : this.data("type").description);
	this.find(">table >thead >tr >th >span.jobTypeIdentifier").text(modelDescription + " - " + typeDescription);
	reloadQuickEditParametersTable.apply(this);
}

/**
 * Reloads parameters from a block's internal data into the quick edit table of the said block. 
 * @this {jquery} The JobType block.
 */
function reloadQuickEditParametersTable()
{
	let block = this;
	this.find(">table >tbody >tr >td:nth-child(1) >table >tbody").empty();
	$(block.data("genericParameters")).each(function(){
		if(this.quickEdit === 1)
		{
			let key = this.key;
			let value = block.data("jobTypeParameters")[key];
			if(typeof value === "undefined" || value === "" || value === null)
			{
				value = this.value;
				block.data("jobTypeParameters")[key] = this.value;
			}
			
			addQuickEditParameter.apply(block, [key, value]);
		}
	});
	dataHasChanged(true);
}

/**
 * Creates a block from a jobType.
 * 
 * @param {jquery} block The JobType block to which this table belongs.
 * @return {jquery} 
 */
function newJobTypeTable(block)
{
	let removeButton = $(imageButton(ROOT_URL + "/images/cancel16.png", "Supprimer", removeJobType, [], block))
	.css({"float": "right", "margin-left": "10px", "color": "#FFFFFF"});
	let modifyButton = $(imageButton(ROOT_URL + "/images/edit.png", "Modifier", modifyJobType, [], block))
	.css({"float": "right", "margin-left": "10px", "color": "#FFFFFF"});
	let descriptionSpan = $("<span></span>").addClass("jobTypeIdentifier").css({"float": "left"});
	
	return $("<table></table>").addClass("parametersTable hoverEffectDisabled").css({"width": "100%", "margin-bottom": "23px"})
	.append(
		$("<thead></thead>").append(
			$("<tr></tr>").append(
				$("<th></th>").attr("colspan", "2").addClass("titreBleu firstVisibleColumn lastVisibleColumn")
				.css({"padding-left": "10px", "padding-right": "10px"})
				.append(descriptionSpan, removeButton, modifyButton)
			)
		),
		$("<tbody></tbody>").append(
			$("<tr></tr>").append(
				$("<td></td>").addClass("firstVisibleColumn").css({"vertical-align": "top", "width": "25%"}).append(
					newQuickEditParametersTable()
				),
				$("<td></td>").addClass("lastVisibleColumn").css({"vertical-align": "top", "width": "75%"}).append(
					newPartsTable(block)
				)
			)
		)
	);
}

/**
 * Creates a new parameters table
 * 
 * @return {jquery} A new quick edit parameters table
 */
function newQuickEditParametersTable()
{
	return $("<table></table>").addClass("parametersTable noExternalBorder hoverEffectDisabled").css({"width": "100%"}).append(
		$("<thead></thead>").append(
			$("<tr></tr>").append(
				$("<th></th>").addClass("titreBleu firstVisibleColumn lastVisibleColumn").attr("colspan", 2).html("Paramètres")
			),
			$("<tr></tr>").append(
				$("<th></th>").addClass("titreBleu firstVisibleColumn").css({"width": "50%"}).html("Clé"),
				$("<th></th>").addClass("titreBleu lastVisibleColumn").css({"width": "50%"}).html("Valeur")
			)
		),
		$("<tbody></tbody>")
	);
}

/**
 * Creates a table for parts in the current block.
 * @param {jquery} block The JobType block to which this table belongs.
 * 
 * @return {jquery} A new Parts table
 */
function newPartsTable(block)
{	
	let addButton = $(imageButton(ROOT_URL + "/images/add.png", "Ajouter", addPart, [], block))
	.css({"flex": "0 1 auto", "margin-left": "10px", "color": "#FFFFFF"});
	let minusButton = $(imageButton(ROOT_URL + "/images/minus.png", "Vider", emptyPartsTable, [], block))
	.css({"flex": "0 1 auto", "margin-left": "10px", "color": "#FFFFFF"});
	let grainSelect = $("<select></select>").append(
		$("<option></option>").text("Tous").val(""),
		$("<option></option>").text("Aucun").val("N"),
		$("<option></option>").text("Horizontal").val("X"),
		$("<option></option>").text("Vertical").val("Y")
	).val("").change(function(){
		setGrainForAllParts.apply(block, [$(this).val()]); 
		$(this).val("");
	});
	
	return $("<table></table>").addClass("parametersTable noExternalBorder").css({"width": "100%"}).append(
		$("<thead></thead>").append(
			$("<tr></tr>").append(
				$("<th></th>").addClass("titreBleu firstVisibleColumn lastVisibleColumn").attr({"colspan": 5}).append(
					$("<div></div>").css({"display": "flex", "flex-flow": "row", "padding-left": "10px", "padding-right": "10px"})
					.append(
						$("<span></span>").text("Éléments").css({"text-align": "center", "flex": "1 1 auto"}),
						addButton,
						minusButton
					)
				)
			), 
			$("<tr></tr>").append(
				$("<th></th>").addClass("titreBleu  firstVisibleColumn").css({"width": "12%"}),
				$("<th></th>").addClass("titreBleu").css({"display": "none"}).prop({"disabled": true}).html("ID"),
				$("<th></th>").addClass("titreBleu").css({"width": "22%", "text-align": "center"}).html("Quantité"),
				$("<th></th>").addClass("titreBleu").css({"width": "22%", "text-align": "center"}).html("Hauteur"),
				$("<th></th>").addClass("titreBleu").css({"width": "22%", "text-align": "center"}).html("Largeur"),
				$("<th></th>").addClass("titreBleu lastVisibleColumn").css({"width": "22%"}).append(
					$("<span></span>").css({"margin-right": "10px"}).html("Grain"),
					grainSelect
				)
			)
		),
		$("<tbody></tbody>")
	);
}

/**
 * Inserts a block after previous element or, if previous element is not provided, at the beginning of parent. If parent 
 * is not provided either, throws an exception.
 * @this {jquery} The parent element in which the block should be inserted
 * @param {jquery} block The block to insert somewhere in the structure of the parent
 * @param optional {int} position=-1 The position where the new block should be inserted (if positive, inserts before the 
 * 									 element at the specified position; if negative, inserts after the element at the 
 * 									 specified position).
 * 
 * @return {jquery} The new block
 */
function insertBlock(block, position = -1)
{	
	if(parent.children().length === 0 || position === -1)
	{
		parent.append(block);
	}
	else if (position >= 0)
	{
		this.insertBefore(parent.children().eq(position));
	}
	else if(position < 0)
	{
		this.insertAfter(parent.children().eq(position));
	}
	
	return this;
}

/**
 * Removes a JobType from the list.
 * @this {jquery} The JobType to remove.
 */
async function removeJobType()
{
	let block = this;
	if(await askConfirmation("Retirement de bloc", "Voulez-vous vraiment retirer ce bloc?"))
	{
		if(block.siblings().length > 0)
		{
			removeBlock.apply(block);
		}
		else
		{
			emptyPartsTable.apply(block);
		}
	}
}

/**
 * Modifies a JobType.
 * 
 * @this {jquery} The JobType to modify.
 */
function modifyJobType()
{
	/* Display the block modification window. */
	openParameterEditor.apply(this, [refreshJobTypeBlock]);
}

/**
 * Creates a new element.
 * @param {object} part A part
 * @return {jquery} The new part's row. 
 */
function newPart(part)
{	
	let id = (typeof part !== "undefined" && typeof part.id !== "undefined") ? part.id : null;
	let qty = (typeof part !== "undefined" && typeof part.quantity !== "undefined") ? part.quantity : null;
	let length = (typeof part !== "undefined" && typeof part.length !== "undefined") ? part.length : null;
	let width = (typeof part !== "undefined" && typeof part.width !== "undefined") ? part.width : null;
	let grain = (typeof part !== "undefined" && typeof part.grain !== "undefined") ? part.grain : null;
	let row = $("<tr></tr>").css({"height": "23px"}).append(
		$("<td></td>").css({"line-height": "0px"}).addClass("firstVisibleColumn"),
		$("<td></td>").css({"line-height": "0px", "display": "none"}).append(
			$("<input>")
			.val((id !== undefined && id !== null && id !== "") ? id : null)
		),
		$("<td></td>").css({"line-height": "0px"}).append(
			$("<input>")
			.val((qty !== undefined && qty !== null && qty !== "") ? qty : 1)
			.change(function(){dataHasChanged(true);})
		),
		$("<td></td>").css({"line-height": "0px"}).append(
			$("<input>")
			.val((length !== undefined && length !== null && length !== "") ? length : 0)
			.change(function(){dataHasChanged(true);})
		),
		$("<td></td>").css({"line-height": "0px"}).append(
				$("<input>")
				.val((width !== undefined && width !== null && width !== "") ? width : 0)
				.change(function(){dataHasChanged(true);})
		),
		$("<td></td>").css({"line-height": "0px"}).addClass("lastVisibleColumn").append(
			$("<select></select>")
			.append(
				$("<option></option>").text("Aucun").val("N"),
				$("<option></option>").text("Horizontal").val("X"),
				$("<option></option>").text("Vertical").val("Y")
			)
			.val(new RegExp("^N$|^X$|^Y$", "i").test(grain) ? grain : "N")
			.change(function(){dataHasChanged(true);})
		)
	);
	
	let modifyTool = $(imageButton(ROOT_URL + "/images/edit.png", "", modifyPart, [], row))
	.css({"margin-left": "10px"});
	let deleteTool = $(imageButton(ROOT_URL + "/images/cancel16.png", "", removePart, [], row))
	.css({"margin-left": "10px"});
	row.find(">td:first-child").append(modifyTool, deleteTool);
	return row;
}

/**
 * Adds a part to a JobType block
 * @this {jquery} A JobType block
 * @param {object} part A part
 * @return {jquery} The block to which the part was added
 */
function addPart(part)
{
	this.find(">table >tbody >tr >td:nth-child(2) >table >tbody").append(newPart(part));
	dataHasChanged(true);
	return this;
}

/**
 * Remove all parts from a JobType block.
 * @this {jquery} The JobType block to empty
 * @return {jquery} The emptied JobType block.
 */
function emptyPartsTable()
{
	this.find(">table >tbody >tr >td:nth-child(2) >table >tbody").empty();
	dataHasChanged(true);
	return this;
}

/**
 * Opens the part modification modal window.
 * @this {jquery} The Part's row
 */
function modifyPart()
{
	let partRow = this;
	let originalBlock = partRow.parents("div.blockContainer:first");
	$("div#blocksList").empty();
	
	/* Déplacer l'élément entre les blocs existants. */
	$("div#blocksContainer >div.blockContainer").each(function(){
		if(this === originalBlock[0])
		{
			return;
		}
		
		let newBlock = $(this);
		$("div#blocksList")
		.append(
			$("<div></div>").css({"cursor": "pointer"}).click(function(){
				movePartBetweenBlocks.apply(partRow, [newBlock]);
			}).append(
				$("<span></span>").text(newBlock.find(">table >thead >tr >th >span").text())
			)
		);
	});
	
	/* Déplacer l'élément vers un nouveau bloc. */
	$("div#blocksList").append(
		$("<div></div>").css({"cursor": "pointer"}).click(function(){
			let newBlock = newJobType(originalBlock.data({"id": null,"parts": []}).data()); 
			$("div#blocksContainer").append(newBlock); 
			movePartBetweenBlocks.apply(partRow, [newBlock]);
		}).append(
			$("<span></span>").text("Nouveau bloc")
		)
	);
	
	$("div.modal#moveBetweenBlocksModal").show();
}

/**
 * Removes a part from a block.
 * @this The Part's row
 */
function removePart()
{
	this.remove();
	dataHasChanged(true);
}

/**
 * Removes a block.
 * @this The block
 */
function removeBlock()
{
	this.remove();
	dataHasChanged(true);
}

/**
 * Adds a quick edit parameter to a JobType block
 * @this {jquery} A JobType block
 * @param {string} key The key of the parameter
 * @param {string} value The value of the parameter
 * @return {jquery} The block to which the parameter was added
 */
function addQuickEditParameter(key, value)
{
	let block = this;
	let id = block.data("id") + "_" + key;
	block.find(">table >tbody >tr >td:nth-child(1) >table >tbody").append(
		newQuickEditParameter.apply(block, [key, value]).attr({"id": id}).css({"display": "auto"})
	);
	return this;
}

/**
 * Builds a quick edit parameter row
 * @this {jquery} The block for which the new quick edit parameter is created
 * @param {string} key The key of the parameter
 * @param {string} value The value of the parameter
 * @return {jquery} The new parameter's row
 */
function newQuickEditParameter(key, value)
{
	let block = this;
	return $("<tr></tr>").css({"height": "23px"}).append(
		$("<td></td>")
		.addClass("firstVisibleColumn").css({"line-height": "0px"})
		.append(
			$("<input>").prop({"disabled": true}).val(key)
		),
		$("<td></td>").addClass("lastVisibleColumn").css({"line-height": "0px"}).append(
			$("<input>").val(value)
			.change(function(){
				block.data("jobTypeParameters")[key] = $(this).val();
				dataHasChanged(true);
			})
		)
	);
}

/**
 * Moves a part from a block to another block
 * @this {jquery} The part's row
 * @param {jquery} destinationBlock The destination block
 * @return {jquery} The part's row
 */
function movePartBetweenBlocks(destinationBlock)
{
	this.detach();
	destinationBlock.find(">table >tbody >tr >td:last-child >table >tbody").append(this);
	dataHasChanged(true);
	return this;
}

/**
 * Sets the grain for all the parts in a block
 * @this {jquery} The block
 * @param {string} grain The new value of grain
 * @return {jquery} The block
 */
function setGrainForAllParts(grain)
{
	this.find(">table >tbody >tr >td:last-child >table >tbody >tr >td:last-child >select").each(function(){
		$(this).val(grain);
	});
	return this;
}

/**
 * Update the metadata of the parts of a block.
 * @this {jquery} The block
 * @return {jquery} The block
 */
function updateJobTypePartsMetaData()
{
	let block = this;
	block.data("parts", []);
	block.find(">table >tbody >tr >td:last-child >table >tbody >tr").each(function(){
		block.data("parts").push({
			"done": "N",
			"grain": $(this).find(">td:nth-child(6) >select").val(),
			"id": $(this).find(">td:nth-child(2) >input").val() !== "" ? $(this).find(">td:nth-child(2) >input").val() : null,
			"jobTypeId": block.id,
			"length": $(this).find(">td:nth-child(4) >input").val(),
			"producedQuantity": "0",
			"quantityToProduce": $(this).find(">td:nth-child(3) >input").val(),
			"width": $(this).find(">td:nth-child(5) >input").val()
		});
	});
	
	return block;
}

/**
 * Gets or sets the state of the dataHasChanged boolean.
 * @param {bool | null} The new status to apply to the dataHasChanged boolean. If null, the current status of the boolean is 
 * 					  returned.
 * @return {null | bool} If the status of the dataHasChanged boolean is being set, null is returned. Otherwise, the current state 
 * 						 of the boolean is returned.
 */
function dataHasChanged(status = null)
{
	if(status === null && window.sessionStorage.getItem("dataHasChanged") === "true")
	{
		return true;
	}
	else if(status === null && window.sessionStorage.getItem("dataHasChanged") === "false")
	{
		return false;
	}
	else if(status === true)
	{
		window.sessionStorage.setItem("dataHasChanged", "true");
		return null;
	}
	else if(status === false)
	{
		window.sessionStorage.setItem("dataHasChanged", "false");
		return null;
	}
}