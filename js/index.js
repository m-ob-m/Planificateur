"use strict";

/**
 * Updates the calendar planning interface.
 */
async function reloadEvents()
{
	try {
		updateEventsCalendar(await retrieveEvents());
	}
	catch (error) {
		showError("La mise à jour des évènements a échouée", error);
	}
}

/**
 * Retrieves events from the database
 * 
 * @return {Promise}
 */
function retrieveEvents()
{
	return new Promise(function(resolve, reject){
		$.ajax({
	    	"url": ROOT_URL + "/actions/loadEvents.php",
	        "type": "GET",
	        "contentType": "application/json;charset=utf-8",
	        "data": {
	        	"start": $("#calendar").fullCalendar("getCalendar").view.start.format("Y/M/D"), 
	        	"end": $("#calendar").fullCalendar("getCalendar").view.end.format("Y/M/D")
	        },
			"dataType": "json",
			"async": true,
			"cache": false
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
		})
	});
}

/**
 * Updates the calendar with the provided events
 * 
 * @param events The events to put on the calendar
 */
function updateEventsCalendar(events)
{
	$("#calendar").fullCalendar("removeEvents");
	$("#calendar").fullCalendar("addEventSource", events);
}

/**
 * Initiates the calendar planning interface.
 */
$(function(){
    // Chargement du calendrier
    $("#calendar").fullCalendar({
    	"contentHeight": "9999",
		"height": "9999",
		"header": {"left": "prev,next today", "center": "title", "right": "month, agendaWeek, agendaDay, listMonth"},
		"locale": "fr-ca",
		"navLinks": true, // can click day/week names to navigate views
		"eventLimit": true, // allow "more" link when too many events
		"eventDrop": function(event, delta, revertFunc){
			reschedule(event);
	    },
	    "eventResize": function( event, delta, revertFunc, jsEvent, ui, view) 
	    {	
	    	reschedule(event);
		},
	    "windowResize":  function(view) 
	    {
	        $("#calendar").fullCalendar("option", "height", $("#calendar").height());
	    }
    });
    $("#calendar").css({"width": "100%"});

    
    $(".fc-prev-button, .fc-next-button, .fc-month-button, .fc-agendaWeek-button, .fc-agendaDay-button, .fc-listMonth-button")
    .click(function(){
    	reloadEvents();
    });
    
    $(".fc-agendaWeek-button, .fc-agendaDay-button, .fc-listMonth-button").click(function(){
    	$("html").css({"height": "100%"});
    	$("#calendar").fullCalendar("option", "height", "auto");
    });
    
    $(".fc-month-button").click(function(){
    	$("html").css({"height": "125%"});
    	$("#calendar").fullCalendar("option", "height", $("#calendar").height());
    });
	reloadEvents();
});

/**
 * Reschedule an event
 * @param {FullCalendarEvent} event The event for which the planning is modified
 */
async function reschedule(event)
{
	try {
		event.color = await rescheduleEvent(event);
		$("#calendar").fullCalendar("updateEvent", event);
	}
	catch (error) {
		showError("Le changement de planification de l'évènement a échoué", error);
	}
}

/**
 * Change the planning for an event
 * @param {FullCalendarEvent} event The event for which the planning is modified
 * 
 * @return {Promise}
 */
function rescheduleEvent(event)
{
	return new Promise(function(resolve, reject){
		$.ajax({
	    	"url": "actions/eventDrop.php",
	        "type": "POST",
	        "contentType": "application/json;charset=utf-8",
	        "data": JSON.stringify({
	        	"batchId": event.id, 
	        	"debut": event.start.format(), 
	        	"fin": event.end.format(), 
	        	"allDay":event.allDay
	        }),
			"dataType": "json",
			"async": true,
			"cache": false
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
 * Finds a job by production number
 * 
 * @return {Promise}
 */
function getBatchIdFromJobName(jobName)
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"url": ROOT_URL + "/sections/job/actions/findBatchByJobName.php",
            "type": "POST",
            "contentType": "application/json;charset=utf-8",
            "data": JSON.stringify({"productionNumber": jobName}),
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
 * Update all unitary programs
 * @param {int} modelId The model id for which programs must be updated, null means all
 * @param {int} TypeNo The type id for which programs must be updated, null means all
 */
async function updateUnitaryPrograms(modelId = null, typeNo = null)
{
	document.getElementById("loadingModal").style.display = "block";
	try{
		await updatePrograms()
	}
	catch(error){
		showError("La génération des programmes unitaires a échouée", error);
	}
	finally{
		document.getElementById("loadingModal").style.display = "none";
	};
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
			"url": ROOT_URL + "/parametres/varmodtype/actions/MAJModeleUnitaire.php",
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
 * Regenerates unitary programs
 * 
 * @return {Promise}
 */
function getBatchIdFromJobName(jobName)
{
	return new Promise(function(resolve, reject){
		$.ajax({
			"url": ROOT_URL + "/sections/job/actions/findBatchByJobName.php",
            "type": "POST",
            "contentType": "application/json;charset=utf-8",
            "data": JSON.stringify({"productionNumber": jobName}),
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
 * Finds a job by production number
 */
async function findJobByProductionNumber()
{
	let productionNumber = document.getElementById("findBatchByJobNumberForm").elements["jobNumero"].value;
	try 
	{
		let id = await getBatchIdFromJobName(productionNumber);
		window.location.assign([ROOT_URL + "/sections/batch/index.php", "?", "id=", id].join(""));
	}
	catch (error) {
		showError("La job \"" + productionNumber + "\" n'a pas été trouvée : ", error);
	}
}