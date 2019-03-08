"use strict";

/**
 * Opens the parameters editor.
 * @this {jquery} The JobType block to edit
 * @param {null | function} [callbackOnAccept = null] The callback to call when modifications are accepted
 * @return 
 */
function openParameterEditor(callbackOnAccept = null)
{
	reloadParameterEditor.apply(this);
	$("select#modelId").val(this.data("model").id);
	$("select#typeNo").val(this.data("type").importNo);
	$("img#acceptEdit, img#cancelEdit").unbind("click");
	$("select#modelId, select#typeNo").unbind("change");
	$("#acceptEdit").bind("click", [this], function(event){acceptEdit.apply(event.data[0], [callbackOnAccept]);});
	$("#cancelEdit").bind("click", [this], function(event){cancelEdit();});
	$("select#modelId, select#typeNo").bind("change", [this], function(event){
		reloadParameterEditor.apply(event.data[0], [parseInt($("select#modelId").val()), parseInt($("select#typeNo").val())]);
	});
	showParameterEditor();
}

/**
 * Closes the parameters editor.
 * @this 
 * @param 
 * @return 
 */
function closeParameterEditor()
{
	hideParameterEditor();
}

/**
 * Reloads the parameters editor with a specied Modeltype combination or with the block currently beign modified.
 * @this {jquery} The JobType block to edit
 * @param 
 * @return 
 */
function reloadParameterEditor(modelId = null, typeNo = null)
{
	let reloadParametersIsRequired = (modelId !== null && typeNo !== null) ? true : false;
	
	if(reloadParametersIsRequired)
	{
		if(modelId !== 2)
		{
			retrieveModelTypeGenericParameters(modelId, typeNo)
			.catch(function(error){
				showError("La récupération des paramètres du modèle-type a échouée.", error);
			})
			.then(function(parameters){
				updateParameterEditor(parameters);
				switchEditionMode(0);
			})
		}
		else
		{
			$("textarea#mprFileContents").val("");
			switchEditionMode(1);
		}
	}
	else
	{
		if(modelId !== 2)
		{
			let block = this;
			let parameters = JSON.parse(JSON.stringify(block.data("genericParameters")));
			$(parameters).each(function(){
				this.defaultValue =  this.value;
				this.value = block.data("jobTypeParameters")[this.key];
			});
			
			updateParameterEditor(parameters);
			switchEditionMode(0);
		}
		else
		{
			$("textarea#mprFileContents").val(this.data("mprFile"));
			switchEditionMode(1);
		}
	}
}

/**
 * Shows the parameters editor.
 * @this 
 * @param 
 * @return 
 */
function showParameterEditor()
{
	$("div#parametersEditor").css({"display": "block"});	
}

/**
 * Hides the parameters editor.
 * @this 
 * @param 
 * @return 
 */
function hideParameterEditor()
{
	$("div#parametersEditor").css({"display": "none"});	
}

/**
 * Updates the parameters editor with fresh parameters.
 * @this 
 * @param {array} [parameters = []] The new set of parameters
 * @return {Promise} 
 */
function updateParameterEditor(parameters = [])
{
	$("table#parametersArray >tbody").empty();
	let color = "#FFFFFF";
	$(parameters).each(function(){
		let display = new RegExp("^.*PRIVATE.*$").test(this.description) ? "none" : "table-row";
		if(display !== "none")
		{
			color = (color === "#FFFFFF") ? "#97BFD9" : "#FFFFFF";
		}
		$("table#parametersArray >tbody").append(
			$("<tr></tr>").css({"display": display, "height": "40px", "background-color": color}).append(
				$("<td></td>").addClass("firstVisibleColumn").append(
					$("<input>")
					.prop({"disabled": true})
					.css({"width": "100%", "font-size": "1em"})
					.val(this.key)
				),
				$("<td></td>").css({"background-color": (this.defaultValue !== this.value) ? "#FFFF00" : "auto"}).append(
					$("<textarea></textarea>")
					.addClass("nonResizable")
					.css({"width": "100%", "font-size": "1em", "line-height": "1.25em"})
					.text(this.value)
				),
				$("<td></td>").addClass("lastVisibleColumn").append(
					$("<textarea></textarea>")
					.addClass("nonResizable")
					.prop({"disabled": true})
					.css({"width": "100%", "font-size": "1em", "line-height": "1.25em"})
					.text(this.description)
				),
				$("<td></td>").css({"display": "none"}).append(
						$("<textarea></textarea>")
						.addClass("nonResizable")
						.prop({"disabled": true})
						.css({"width": "100%", "font-size": "1em", "line-height": "1.25em"})
						.text(this.defaultValue)
				),
				$("<td></td>").css({"display": "none"}).append(
					$("<input>")
					.prop({"disabled": true})
					.css({"width": "100%", "font-size": "1em"})
					.val(this.quickEdit)
				)
			)
		);
	});
}

/**
 * Retrieves the parameters for a ModelTypeGeneric combination
 * @this
 * @param {int} modelId The id of the selected model
 * @param {int} typeNo The import number of the selected model
 * @return {Promise}
 */
function retrieveModelTypeGenericParameters(modelId, typeNo)
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/parametres/varmodtypegen/actions/getParameters.php",
			"data": {"modelId": modelId, "typeNo": typeNo},
			"dataType": "json",
			"async": true,
			"cache": false,
		}) 
		.done(function(response){
			resolve(response.success.data);
		})
		.fail(function(error){
			reject(error.responseText);
		});
	});
}

/**
 * Selects the edition mode.
 * @this 
 * @param {int} [mode = 0] 0 = "program parameters edition", 1 = "program file edition". 
 * @return 
 */
function switchEditionMode(mode = 0)
{
	if (mode === 0)
	{
		$("div#customFileTableBody").css({"display": "none"});
		$("table#parametersArray").css({"display": "table"});
		$("tr#parametersEditorTypeSelectionRow").css({"display": "table-row"});
		$("tr#parametersEditorMprFileSelectionRow").css({"display": "none"});
	}
	else
	{
		$("div#customFileTableBody").css({"display": "block"});
		$("table#parametersArray").css({"display": "none"});
		$("tr#parametersEditorTypeSelectionRow").css({"display": "none"});
		$("tr#parametersEditorMprFileSelectionRow").css({"display": "table-row"});
	}
}

/**
 * Accepts the edited parameters.
 * @this {jquery} The JobType block to edit
 * @param {null | function} callback A callback function
 * @return 
 */
function acceptEdit(callback)
{
	this.data("model", {"id": $("select#modelId").val(), "description": $("select#modelId >option:selected").text()});
	this.data("type", {"importNo": $("select#typeNo").val(), "description": $("select#typeNo >option:selected").text()});
	this.data("genericParameters", []);
	this.data("jobTypeParameters", {});
	if($("select#modelId").val() !== 2)
	{
		this.data("mprFile", null);
		saveParametersToBlock.apply(this);
	}
	else
	{
		this.data("mprFile", $("textarea#mprFileContents").val());
	}
	
	// A user defined callback function may be called with the current JobType block as a parameter.
	if(callback && {}.toString.call(callback) === '[object Function]')
	{
		callback.apply(this);
	}
	
	closeParameterEditor();
}

/**
 * Saves the edited parameters to the edited block.
 * @this {jquery} The JobType block to edit
 * @param
 * @return 
 */
function saveParametersToBlock()
{
	let block = this;
	$("table#parametersArray >tbody >tr").each(function(){
		let key = $(this).find(">td:nth-child(1) >input").val();
		block.data("genericParameters").push({
			"key": key,
			"value": $(this).find(">td:nth-child(4) >textarea").val(),
			"description": $(this).find(">td:nth-child(3) >textarea").val(),
			"quickEdit": parseInt($(this).find(">td:nth-child(5) >input").val())
		});
		block.data("jobTypeParameters")[key] = $(this).find(">td:nth-child(2) >textarea").val();
		updateJobTypeBlockIdentifier.apply(block);
	});
}

/**
 * Cancels the edition of the parameters.
 * @this 
 * @param 
 * @return 
 */
function cancelEdit()
{
	closeParameterEditor();
}