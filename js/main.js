/**
 * Update all unitary programs
 * @param {int} modelId The model id for which programs must be updated, null means all
 * @param {int} TypeNo The type id for which programs must be updated, null means all
 */
function updateUnitaryPrograms(modelId = null, typeNo = null)
{
	$('#loadingModal').css("display", "block");
	updatePrograms()
	.catch(function(error){
		showError("La génération des programmes unitaires a échouée", error);
	})
	.finally(function(){
		$('#loadingModal').css("display", "none");
	});
}

/**
 * Updates unitary programs
 * 
 * @return {Promise}
 */
function updatePrograms()
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/parametres/varmodtype/actions/MAJModeleUnitaire.php",
			"data": JSON.stringify({}),
			"dataType": "json",
			"async": true,
			"cache": false,
		})
		.done(function(response){
			if(response.status === "success")
			{
				resolve(response.success.data);
			}
			else
			{
				reject(response.failure.message);
			}
		})
		.fail(function(error){
			reject(error.responseText);
		});
	});
}

/**
 * Display a modal window for error messages
 * @param {string} title The title of the window
 * @param {string} message The error message to display in the window
 */
function showError(title, message)
{
	$("#errMsg").empty()
		.append($("<H4></H4>").text(title))
		.append($("<hr>"))
		.append($("<p></p>").text(message))
		.append($("<br>"))
		.append($("<hr>"))
		.append($("<h1></h1>").text("Cliquer sur cette fenetre pour la fermer..."));
		$("#errMsgModal").css("display", "block");
}

/** Creates a new image button (include imageButton.css to obtain a formatted button)
* @param {string} imagePath The path to the image to display on the button
* @param {string} text The text of the button
* @param {function} callback The callback function on-click for this button
* @param optional {array} parameters An array of parameters to apply to the on-click callback function
* @param optional {any} thisElement The value of this in the callback function
* 
* @return {jquery} An image button
*/
function imageButton(imagePath, text, callback, parameters = [], thisElement = null)
{
	return $("<a></a>")
	.addClass("imageButton")
	.attr({"href": "javascript: void(0);"})
	.click(function(){return callback.apply(thisElement, parameters);})
	.append(
		$("<img>").attr({"src": imagePath}), 
		$("<span></span>").text(text)
	);
}

/**
 * Display a yes / no modal window for validation messages and executes callback if user clicks on yes.
 * @param {string} title The title of the window
 * @param {string} message The error message to display in the window
 * 
 * @return {Promise}
 */
function askConfirmation(title, message)
{
	return new Promise(function(resolve, reject){
		$("#validationMsg").empty()
		.append($("<center></center>")
			.append($("<h4></h4>").text(title))
			.append($("<hr>"))
			.append($("<p></p>").text(message))
			.append($("<hr>"))
			.append($("<button></button>").attr("type", "button").css("margin", "5px").text("Oui").click(function(){resolve();}))	
			.append($(" ")).append($(" ")).append($(" ")).append($(" ")).append($(" "))
			.append($(" ")).append($(" ")).append($(" ")).append($(" ")).append($(" "))
			.append($("<button></button>").attr("type", "button").text("Non").css("margin", "5px").click(function(){reject();}))
			.append($("<br>"))
			.append($("<hr>"))
			.append($("<h1></h1>").text("Cliquer sur cette fenetre pour la fermer..."))
		);
		$('#validationMsgModal').css("display", "block");
	});
}

/**
 * Reads the contents of a file and returns it.
 * 
 * @param {string} filepath The path of the file to load
 * @param {string} [encoding=utf-8] The encoding used to read the file
 * 
 * @return {Promise}
 */
function readFile(filepath, encoding = "utf-8")
{
	return new Promise(function(resolve, reject){
		let fileReader = new FileReader();
		fileReader.onload = function(){
			if(filepath !== "")
			{
				resolve(fileReader.result)
			}
			else
			{
				reject("No file selected.");
			}
		};
		fileReader.onerror = function(event){
			reject(event.type);
		};
			
		fileReader.readAsText(filepath, encoding);
	});
}

/**
 * Downloads a file from the server (automatically initiated).
 * @param {string} url The url of the file to download
 * @param {string} fileName The default name for the downloaded file
 * 
 */
function downloadFile(url, fileName)
{
	let req = new XMLHttpRequest();
	req.open("GET", url, true);
	req.responseType = "blob";

	req.onload = function (event) 
	{
		let blob = req.response;
	    link = document.createElement("a");
	    $(link).attr({"href": window.URL.createObjectURL(blob), "download": fileName});
	    $(link).css({"visibility": "hidden", "display": "none"});
	    $(link).appendTo("html >body");
	    link.click();
	    $(link).remove();
	};

	req.send();
}