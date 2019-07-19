"use strict";

docReady(function(){
	let viewer = new MachiningProgramViewer();
	[...document.getElementsByClassName("pannelContainer")].forEach(function(pannelContainer){
		[...pannelContainer.getElementsByTagName("button")].forEach(function(button){
			if(button.classList.contains("goToFirst"))
			{
				button.onclick = function(){viewer.goToFirst()};;
			}
			else if(button.classList.contains("goToPrevious"))
			{
				button.onclick = function(){viewer.goToPrevious();};
			}
			else if(button.classList.contains("goToNext"))
			{
				button.onclick = function(){viewer.goToNext();};		
			}
			else if(button.classList.contains("goToLast"))
			{
				button.onclick = function(){viewer.goToLast();};
			}
			else if(button.classList.contains("printSingle"))
			{
				button.onclick = function(){viewer.printPannel();};
			}
			else if(button.classList.contains("printAll"))
			{
				button.onclick = function(){viewer.printAllPannels();};
			}
		});
		[...document.getElementsByClassName("porte")].forEach(function(part){
			part.onclick = function(){displayDoorProperties(parseInt(part.dataset.id));};
		});
		document.getElementById("propertiesWindowCloseButton").onclick = function(){closePropertiesWindow();};
	});
});

/**
 * Retrieves the door properties and displays them on the screen
 * @param {int} doorId The id of the door
 */
async function displayDoorProperties(doorId)
{
	try{

		formatDoorProperties(await fetchDoorProperties(doorId));
		showPropertiesWindow();
	}
	catch(error){
		showError("La récupération des paramètres de la porte a échouée", error);
	}
}

/**
 * Fetches this door's data.
 * @param {int} doorId The id of the door
 * 
 * @return {Promise}
 */
async function fetchDoorProperties(doorId)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/visualiseur/actions/fetchProperties.php",
			"data": {"jobTypePorteId": doorId},
			"dataType": "json",
			"async": true,
			"cache": false,
			"onSuccess": function(response){
				if(response.status === "success")
				{
					resolve(response.success.data);
				}
				else
				{
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
 * Shows the properties window
 * 
 */
function showPropertiesWindow()
{
	let rightPannel = document.getElementById("rightPannel");
	rightPannel.style.display = null;
	rightPannel.scrollTop = 0;
}

/**
 * Closes the properties window.
 * 
 */
function closePropertiesWindow()
{
	document.getElementById("rightPannel").style.display = "none";
}

/**
 * Creates a row for a new door property
 * @param {object} doorProperties An object containing the door properties
 */
function formatDoorProperties(doorProperties)
{	
	let downloadLink = document.createElement("a");
	downloadLink.href = "javascript: void(0);";
	downloadLink.style.color = "#2A00E1";
	downloadLink.textContent = "Télécharger le fichier";
	downloadLink.onclick = function(){
		downloadProgram(doorProperties.id);
	};

	let downloadLinkCell = document.createElement("td");
	downloadLinkCell.colSpan = "2";
	downloadLinkCell.appendChild(downloadLink);
	
	let downloadLinkRow = document.createElement("tr");
	downloadLinkRow.append(downloadLinkCell);

	let tableBody = document.getElementById("rightPannel").getElementsByTagName("table")[0].getElementsByTagName("tbody")[0];
	while(tableBody.childElementCount > 0)
	{
		tableBody.firstElementChild.remove();
	}
	tableBody.appendChild(newDoorProperty("Commande", doorProperties.orderName));
	tableBody.appendChild(newDoorProperty("Modèle", doorProperties.modelName));
	tableBody.appendChild(newDoorProperty("Type", doorProperties.typeName));
	tableBody.appendChild(newDoorProperty("Générique", doorProperties.genericName));
	tableBody.appendChild(newDoorProperty("Hauteur", doorProperties.height));
	tableBody.appendChild(newDoorProperty("Largeur", doorProperties.width));
	tableBody.appendChild(newDoorProperty("Quantité", doorProperties.quantity));
	tableBody.appendChild(newDoorProperty("Grain", doorProperties.grain));
	tableBody.appendChild(downloadLinkRow);
}

/**
 * Gets a dowload link to the machining program of a door. 
 */
function getLinkToProgram(id)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/visualiseur/actions/downloadProgram.php",
			"data": {"jobTypePorteId": id},
			"dataType": "json",
			"async": true,
			"cache": false,
			"onSuccess": function(response){
				if(response.status === "success")
				{
					resolve(response.success.data);
				}
				else
				{
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
 * Downloads the machining program of a door. 
 */
async function downloadProgram(id)
{
	try{
		let downloadableFile = await getLinkToProgram(id);
		downloadFile(ROOT_URL + "/sections/visualiseur/temp/" + downloadableFile.url, downloadableFile.name);
	}
	catch(error){
		showError("La récupération du programme d'usinage a échouée", error);
	}
}

/**
 * Creates a row for a new door property
 * @param {string} name The name of the property
 * @param {string} value The value of the property
 * 
 * @param {jquery} The new row
 */
function newDoorProperty(name, value)
{
	let keyCell = document.createElement("td");
	keyCell.textContent = name;

	let valueCell = document.createElement("td");
	valueCell.textContent = value;

	let row = document.createElement("tr");
	row.appendChild(keyCell);
	row.appendChild(valueCell);
	return row;
}