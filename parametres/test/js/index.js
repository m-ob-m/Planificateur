"use strict";

docReady(async function(){
	let startDate = moment().tz("America/Montreal").subtract(1, "month").format("YYYY-MM-DDTHH:mm:ss");
	let endDate = moment().tz("America/Montreal").format("YYYY-MM-DDTHH:mm:ss");
	document.getElementById("startDate").value = startDate;
	document.getElementById("endDate").value = endDate;
	await refreshTests();
});

/**
 * Retrieves all the tests between the two specified dates
 */
async function refreshTests()
{
	let startDateInput = document.getElementById("startDate");
	let endDateInput = document.getElementById("endDate");
	let startDate = moment(startDateInput.value, "YYYY-MM-DDTHH:mm:ss").tz("America/Montreal");
	let endDate = moment(endDateInput.value, "YYYY-MM-DDTHH:mm:ss").tz("America/Montreal");
	
	try{
		let parametersTable = document.getElementById("parametersTable")
		while(parametersTable.getElementsByTagName("tbody")[0].childElementCount > 0)
		{
			parametersTable.getElementsByTagName("tbody")[0].firstElementChild.remove();
		}
		(await retrieveTestsBetweenDates(startDate, endDate)).forEach(function(test){
			parametersTable.getElementsByTagName("tbody")[0].appendChild(newTest(test));
		});
	}
	catch(error){
		showError("La récupération des tests a échouée", error);
	}
}

/**
 * Creates a new row for a Test
 * @param {object} test A Test
 * @return {Node} The new row for the test
 */
function newTest(test)
{
	let idCell = document.createElement("td");
	idCell.classList = "firstVisibleColumn";
	idCell.textContent = test.id;

	let nameCell = document.createElement("td");
	nameCell.textContent = test.name;

	let modelCell = document.createElement("td");
	modelCell.textContent = test.model;

	let typeCell = document.createElement("td");
	typeCell.textContent = test.type;

	let genericCell = document.createElement("td");
	genericCell.textContent = test.generic;

	let timestampCell = document.createElement("td");
	timestampCell.classList = "lastVisibleColumn";
	timestampCell.textContent = test.timestamp;

	let row = document.createElement("tr");
	row.classList = "link";
	row.onclick = function(){
		openTest(test.id);
	}
	row.appendChild(idCell);
	row.appendChild(nameCell);
	row.appendChild(modelCell);
	row.appendChild(typeCell);
	row.appendChild(genericCell);
	row.appendChild(timestampCell);
	return row;
}