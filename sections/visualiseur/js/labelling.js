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
			else if(button.classList.contains("printAll"))
			{
				button.onclick = async function(){
					let ids = [...viewer.getCurrentPannel().getElementsByClassName("porte")].map(function(part){
						return parseInt(part.dataset.id);
					});
					try
					{
						await downloadPartLabelsCsvFileToLocalPrintServer(ids);
					}
					catch(error)
					{
						showError("La génération du fichier d'étiquettes a échouée", error);
					}
				};
			}
		});
		[...document.getElementsByClassName("porte")].forEach(function(part){
			part.onclick = async function(){
				try
				{
					await downloadPartLabelsCsvFileToLocalPrintServer(parseInt(part.dataset.id));
				}
				catch(error)
				{
					showError("La génération du fichier d'étiquettes a échouée", error);
				}
			};
		});
	});
	document.getElementById("findBatch").addEventListener("click", async function(){
		let name = document.getElementById("batchName").value;
		if(name.trim() !== "")
		{
			await openBatchByName(name);
		}
	});
	document.getElementById("batchName").addEventListener("keyup", function(event){
		if (event.keyCode === 13){
			document.getElementById("findBatch").click();
		}
	});
});

async function openBatchByName()
{
	let name = document.getElementById("batchName").value;
	try{
		let id = await getBatchIdFromBatchName(name);
		window.location.href = [window.location.protocol, "//", window.location.host, window.location.pathname].join("") + "?id=" + id;
	}
	catch(error){
		showError("Le projet n'a pas été trouvé.", error);
	}
}

/**
 * Downloads a CSV file dirrectly into the Print_Server shared folder of this computer. A print server will handle the printing part.
 * @param {int|int[]} id The unique identifier of the parts to print labels for
 * @return {Promise}
 */
function downloadPartLabelsCsvFileToLocalPrintServer(id)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "POST",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/visualiseur/actions/downloadPartLabelsCsvFileToLocalPrintServer.php",
			"data": JSON.stringify({"id": id}),
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
 * Gets the id of a batch by batch name.
 * @param {string} batchName The name of a batch
 * @return {Promise}
 */
function getBatchIdFromBatchName(name)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/batch/actions/getBatchIdByName.php",
			"data": {"name": name},
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