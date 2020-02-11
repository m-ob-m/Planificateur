"use strict";

let ROOT_URL = "/" + document.currentScript.src.match(new RegExp("[^\\\\\\/]+", "g"))[2];

/**
 * Display a modal window for error messages
 * @param {string} title The title of the window
 * @param {string} message The error message to display in the window
 */
function showError(title, message)
{
	let errorTitleTextNode = document.createElement("h4");
	errorTitleTextNode.textContent = title;

	let errorMessageTextNode = document.createElement("p");
	errorMessageTextNode.textContent = message;

	let closeMessageTextNode = document.createElement("h1");
	closeMessageTextNode.textContent = "Cliquer sur cette fenetre pour la fermer...";

	let errorMessageContainer = document.createElement("div");
	errorMessageContainer.classList.add("modal-content");
	errorMessageContainer.style.color = "#FF0000";
	errorMessageContainer.appendChild(errorTitleTextNode);
	errorMessageContainer.appendChild(document.createElement("hr"));
	errorMessageContainer.appendChild(errorMessageTextNode);
	errorMessageContainer.appendChild(document.createElement("hr"));
	errorMessageContainer.appendChild(closeMessageTextNode);

	let errorMessageWindow = document.createElement("div");
	errorMessageWindow.classList.add("modal");
	errorMessageWindow.appendChild(errorMessageContainer);
	errorMessageWindow.addEventListener("click", function(){this.remove();});
	errorMessageWindow.id = "errMsgModal";
	errorMessageWindow.style.display = "block";

	document.getElementsByTagName("body")[0].appendChild(errorMessageWindow);
}

/** Creates a new image button (include imageButton.css to obtain a formatted button)
* @param {string} imagePath The path to the image to display on the button
* @param {string} text The text of the button
* @param {function} callback The callback function on-click for this button
* @param optional {array} parameters An array of parameters to apply to the on-click callback function
* @param optional {any} thisElement The value of this in the callback function
* 
* @return {Element} An image button
*/
function imageButton(imagePath, text, callback, parameters = [], thisElement = null)
{
	let image = document.createElement("img");
	image.src = imagePath;

	let textSpan = document.createElement("span");
	textSpan.textContent = text;

	let imageButton = document.createElement("a");
	imageButton.href = "javascript: void(0);";
	imageButton.classList.add("imageButton");
	imageButton.addEventListener("click", function(){return callback.apply(thisElement, parameters);});
	imageButton.appendChild(image);
	imageButton.appendChild(textSpan);
	return imageButton;
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
	return new Promise(function(resolve)
	{
		let confirmationMessageWindow = document.createElement("div");

		let confirmationTitleTextNode = document.createElement("h4");
		confirmationTitleTextNode.textContent = title;

		let confirmationMessageTextNode = document.createElement("p");
		confirmationMessageTextNode.textContent = message;

		let closeMessageTextNode = document.createElement("h1");
		closeMessageTextNode.textContent = "Cliquer sur cette fenetre pour la fermer...";

		let noButton = document.createElement("button");
		noButton.type = "button";
		noButton.style.width = "50%";
		noButton.style.marginTop = "5px";
		noButton.style.marginBottom = "5px";
		noButton.style.marginRight = "10px";
		noButton.style.FlexGrow = 1;
		noButton.style.FlexShrink = 1;
		noButton.style.FlexBasis = "auto";
		noButton.textContent = "Non";
		noButton.addEventListener("click", function(){
			confirmationMessageWindow.remove();
			resolve(false);
		});

		let yesButton = document.createElement("button");
		yesButton.type = "button";
		yesButton.style.width = "50%";
		yesButton.style.marginTop = "5px";
		yesButton.style.marginBottom = "5px";
		yesButton.style.marginLeft = "10px";
		yesButton.style.FlexGrow = 1;
		yesButton.style.FlexShrink = 1;
		yesButton.style.FlexBasis = "auto";
		yesButton.textContent = "Oui";
		yesButton.addEventListener("click", function(){
			confirmationMessageWindow.remove();
			resolve(true);
		});

		let buttonsContainer = document.createElement("div");
		buttonsContainer.style.textAlign = "center";
		buttonsContainer.style.display = "flex";
		buttonsContainer.style.flexDirection = "row";
		buttonsContainer.appendChild(noButton);
		buttonsContainer.appendChild(yesButton);
		
		let confirmationMessageContainer = document.createElement("div");
		confirmationMessageContainer.classList.add("modal-content");
		confirmationMessageContainer.style.color = "#FF0000";
		confirmationMessageContainer.appendChild(confirmationTitleTextNode);
		confirmationMessageContainer.appendChild(document.createElement("hr"));
		confirmationMessageContainer.appendChild(confirmationMessageTextNode);
		confirmationMessageContainer.appendChild(document.createElement("hr"));
		confirmationMessageContainer.appendChild(buttonsContainer);
		confirmationMessageContainer.appendChild(document.createElement("hr"));
		confirmationMessageContainer.appendChild(closeMessageTextNode);

		confirmationMessageWindow.classList.add("modal");
		confirmationMessageWindow.appendChild(confirmationMessageContainer);
		confirmationMessageWindow.id = "validationMsgModal";
		confirmationMessageWindow.style.display = "block";

		document.getElementsByTagName("body")[0].appendChild(confirmationMessageWindow);
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

	req.onload = function(){
		let blob = req.response;
		let link = document.createElement("a");
		link.href = window.URL.createObjectURL(blob);
		link.download = fileName;
		link.style.visibility = "hidden";
		link.style.display = "none";
		document.getElementsByTagName("body")[0].appendChild(link);
	    link.click();
	    link.remove();
	};

	req.send();
}

/**
 * Creates a select box.
 * @param {object} [options={}] An  object listing the options as value: text pairs with the first pair being the selected option 
 * @param {function|null} [callback=null] The onchange callback function of the select box
 * @return {Element} A select box
 */
function selectBox(options = {}, callback = null)
{
	let firstElement = true;
	let select = document.createElement("select");
	Object.keys(options).map(function(key){
		let option = document.createElement("option");
		option.text = options[key];
		option.value = key;
		select.appendChild(option);
		if(firstElement)
		{
			select.value = key;
			firstElement = false;
		}
	});
	select.addEventListener("change", function(){callback !== "undefined" && isFunction(callback) ? callback(this.value) : null;});
	return select;
}	