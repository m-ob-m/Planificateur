$(
	function()
	{
		goToFirst();
	}
);

/**
 * Retrieves the door properties and displays them on the screen
 * @param {int} doorId The id of the door
 */
async function displayDoorProperties(doorId)
{
	try{
		let properties = await fetchDoorProperties(doorId);
		formatDoorProperties(properties);
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
function fetchDoorProperties(doorId)
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/sections/visualiseur/actions/fetchProperties.php",
			"data": {"jobTypePorteId": doorId},
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
 * Shows the properties window
 * 
 */
function showPropertiesWindow()
{
	$("#rightPannel").show().scrollTop();
}

/**
 * Closes the properties window.
 * 
 */
function closePropertiesWindow()
{
	$("#rightPannel").hide();
}

/**
 * Creates a row for a new door property
 * @param {object} doorProperties An object containing the door properties
 */
function formatDoorProperties(doorProperties)
{	
	$("div#rightPannel >table >tbody").empty().append(
		newDoorProperty("Commande", doorProperties.orderName),
		newDoorProperty("Modèle", doorProperties.modelName),
		newDoorProperty("Type", doorProperties.typeName),
		newDoorProperty("Générique", doorProperties.genericName),
		newDoorProperty("Hauteur", doorProperties.height),
		newDoorProperty("Largeur", doorProperties.width),
		newDoorProperty("Quantité", doorProperties.quantity),
		newDoorProperty("Grain", doorProperties.grain),
		$("<tr></tr>").append(
			$("<td></td>").text("Programme"), 
			$("<td></td>").append(
				$("<a></a>")
				.attr({"href": "javascript:void(0);"})
				.css({"color": "#2A00E1"})
				.text("Télécharger le fichier")
				.click(function(){downloadProgram(doorProperties.id);})
			)
		)
	);
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
	return $("<tr></tr>").append($("<td></td>").text(name), $("<td></td>").text(value));
}

/**
 * Goes to the first pannel in the collection.
 * 
 */
function goToFirst()
{
	if($("div.pannelContainer").length > 0)
	{
		goTo(0);
	}
}

/**
 * Goes to the last pannel in the collection.
 * 
 */
function goToLast()
{
	if($("div.pannelContainer").length > 0)
	{
		goTo($("div.pannelContainer").length - 1);
	}
}

/**
 * Goes to the previous pannel in the collection.
 * 
 */
function goToPrevious()
{
	let currentlyVisibleElement = $("div.pannelContainer:visible");
	if(currentlyVisibleElement.length > 0)
	{
		let currentIndex = $("div.pannelContainer").index(currentlyVisibleElement[0]);
		if(currentIndex > 0)
		{
			goTo(currentIndex - 1);
		}
	}
}

/**
 * Goes to the next pannel in the collection
 * 
 */
function goToNext()
{
	let currentlyVisibleElement = $("div.pannelContainer:visible");
	if(currentlyVisibleElement.length > 0)
	{
		let currentIndex = $("div.pannelContainer").index(currentlyVisibleElement[0]);
		if(currentIndex < $("div.pannelContainer").length - 1)
		{
			goTo(currentIndex + 1);
		}
	}
}

/**
 * Goes to the specified index in the collection
 * @param {int} i The index of the pannel (0-based) 
 */
function goTo(i)
{
	$("div.pannelContainer").hide();
	$("div.pannelContainer:nth-child(" + (i + 1) + ")").show();
}

/**
 * Prints the current pannel
 * 
 */
function printPannel()
{
	window.print();
}

/**
 * Prints all pannels
 * 
 */
function printAllPannels()
{
	let currentlyInvisibleElements = $("div.pannelContainer:hidden");
	currentlyInvisibleElements.show();
	window.print();
	currentlyInvisibleElements.hide();
}

/**
 * Gets a dowload link to the machining program of a door. 
 */
function getLinkToProgram(id)
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": "/Planificateur/sections/visualiseur/actions/downloadProgram.php",
			"data": {"jobTypePorteId": id},
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
 * Downloads the machining program of a door. 
 */
async function downloadProgram(id)
{
	try{
		let downloadableFile = await getLinkToProgram(id);
		downloadFile(downloadableFile.url, downloadableFile.name);
	}
	catch(error){
		showError("La récupération du programme d'usinage a échouée", error);
	}
}