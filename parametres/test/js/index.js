"use strict";

$(async function(){
	$("input#startDate").val(moment().tz("America/Montreal").subtract(1, "month").format("YYYY-MM-DDTHH:mm:ss"));
	$("input#endDate").val(moment().tz("America/Montreal").format("YYYY-MM-DDTHH:mm:ss"));
	await refreshTests();
});

/**
 * Retrieves all the tests between the two specified dates
 */
async function refreshTests()
{
	let startDate = moment($("input#startDate").val(), "YYYY-MM-DDTHH:mm:ss").tz("America/Montreal");
	let endDate = moment($("input#endDate").val(), "YYYY-MM-DDTHH:mm:ss").tz("America/Montreal");
	
	try{
		$("table.parametersTable >tbody").empty();
		$(await retrieveTestsBetweenDates(startDate, endDate)).each(function(){
			$("table.parametersTable >tbody").append(newTest(this));
		});
	}
	catch(error){
		showError("La récupération des tests a échouée", error);
	}
}

/**
 * Creates a new row for a Test
 * @param {object} test A Test
 * @return {jquery} The new row for the test
 */
function newTest(test)
{
	return $("<tr></tr>").addClass("link").click(test.id, function(event){openTest(event.data);}).append(
		$("<td></td>").addClass("firstVisibleColumn").text(test.id),
		$("<td></td>").text(test.name),
		$("<td></td>").text(test.model),
		$("<td></td>").text(test.type),
		$("<td></td>").text(test.generic),
		$("<td></td>").addClass("lastVisibleColumn").text(test.timestamp)
	);
}