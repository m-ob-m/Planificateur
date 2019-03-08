"use strict";

$(function(){
	$("input#startDate").val(moment().tz("America/Montreal").subtract(1, "month").format("YYYY-MM-DDTHH:mm:ss"));
	$("input#endDate").val(moment().tz("America/Montreal").format("YYYY-MM-DDTHH:mm:ss"));
	refreshTests();
});

/**
 * Retrieves all the tests between the two specified dates
 * 
 */
function refreshTests()
{
	let startDate = moment($("input#startDate").val(), "YYYY-MM-DDTHH:mm:ss").tz("America/Montreal");
	let endDate = moment($("input#endDate").val(), "YYYY-MM-DDTHH:mm:ss").tz("America/Montreal");
	
	return retrieveTestsBetweenDates(startDate, endDate)
	.then(function(tests){
		$("table.parametersTable >tbody").empty();
		$(tests).each(function(){
			$("table.parametersTable >tbody").append(newTest(this));
		});
	})
	.catch(function(error){
		showError("La récupération des tests a échouée", error);
		return Promise.reject(error);
	})
}

/**
 * Creates a new row for a Test
 * @param {object} test A Test
 * @return {jquery} The new row for the test
 */
function newTest(test)
{
	let moo = $("<td></td>").addClass("firstVisibleColumn").val(test.id);
	return $("<tr></tr>").addClass("link").click(test.id, function(event){openTest(event.data);}).append(
		$("<td></td>").addClass("firstVisibleColumn").text(test.id),
		$("<td></td>").text(test.name),
		$("<td></td>").text(test.model),
		$("<td></td>").text(test.type),
		$("<td></td>").text(test.generic),
		$("<td></td>").addClass("lastVisibleColumn").text(test.timestamp)
	);
}