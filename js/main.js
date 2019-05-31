"use strict";

let ROOT_URL = "/" + document.currentScript.src.match(/(?<=^|\/|\\)[^\\\/]*(?=$|\/|\\)/g)[3];

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

	let errorMessageContainer = document.getElementById("errMsg");
	while(errorMessageContainer.childElementCount > 0)
	{
		errorMessageContainer.firstElementChild.remove();
	}
	errorMessageContainer.appendChild(errorTitleTextNode);
	errorMessageContainer.appendChild(document.createElement("hr"));
	errorMessageContainer.appendChild(errorMessageTextNode);
	errorMessageContainer.appendChild(document.createElement("hr"));
	errorMessageContainer.appendChild(closeMessageTextNode);
	document.getElementById("errMsgModal").style.display = "block";
}

/** Creates a new image button (include imageButton.css to obtain a formatted button)
* @param {string} imagePath The path to the image to display on the button
* @param {string} text The text of the button
* @param {function} callback The callback function on-click for this button
* @param optional {array} parameters An array of parameters to apply to the on-click callback function
* @param optional {any} thisElement The value of this in the callback function
* 
* @return {Node} An image button
*/
function imageButton(imagePath, text, callback, parameters = [], thisElement = null)
{
	let image = document.createElement("img");
	image.src = imagePath;

	let textSpan = document.createElement("span");
	textSpan.textContent = text;

	let imageButton = document.createElement('a');
	imageButton.href = "javascript: void(0);";
	imageButton.className = "imageButton";
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
	return new Promise(function(resolve, reject)
	{
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
		noButton.addEventListener("click", function(){resolve(false);});

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
		yesButton.addEventListener("click", function(){resolve(true);});

		let buttonsContainer = document.createElement("div");
		buttonsContainer.style.textAlign = "center";
		buttonsContainer.style.display = "flex";
		buttonsContainer.style.flexDirection = "row";
		buttonsContainer.appendChild(noButton);
		buttonsContainer.appendChild(yesButton);
		
		let confirmationMessageContainer = document.getElementById("validationMsg");
		while(confirmationMessageContainer.childElementCount > 1)
		{
			confirmationMessageContainer.firstElementChild.remove();
		}
		confirmationMessageContainer.appendChild(confirmationTitleTextNode);
		confirmationMessageContainer.appendChild(document.createElement("hr"));
		confirmationMessageContainer.appendChild(confirmationMessageTextNode);
		confirmationMessageContainer.appendChild(document.createElement("hr"));
		confirmationMessageContainer.appendChild(buttonsContainer);
		confirmationMessageContainer.appendChild(document.createElement("hr"));
		confirmationMessageContainer.appendChild(closeMessageTextNode);
		document.getElementById("validationMsgModal").style.display = "block";
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