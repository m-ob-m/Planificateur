"use strict";

docReady(function(){
	let viewer = new MachiningProgramViewer();
	Array.from(document.getElementsByClassName("pannelContainer")).forEach(function(pannelContainer){
		Array.from(pannelContainer.getElementsByTagName("button")).forEach(function(button){
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
		});
		Array.from(document.getElementsByClassName("porte")).forEach(function(part){
			part.onclick = function(){printPartLabel(parseInt(part.dataset.id));};
		});
	});
});

/**
 * Prints the label of a given part
 * @param {int} id The id of the door
 */
function printPartLabel(id)
{
	fetchLabelInformationForPart(id)
	.then(function(part){
		document.getElementById("productionNumber").value = part.orderName;
		document.getElementById("dimensions").value = part.height + "\" X " + part.width + "\"";
		document.getElementById("modelNumber").value = part.modelName;
		document.getElementById("customerPO").value = part.customerPO
		window.print();
	});
}

/**
 * Fetches the information to print on the label of a given part
 * @param {int} id The id of the door
 * 
 * @return {Promise}
 */
function fetchLabelInformationForPart(id)
{
	return new Promise(function(resolve, reject){
		ajax.send({
			"type": "GET",
			"contentType": "application/json;charset=utf-8",
			"url": ROOT_URL + "/sections/visualiseur/actions/getLabelInformationForPart.php",
			"data": {"id": id},
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