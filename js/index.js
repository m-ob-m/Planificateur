"use strict";

/**
 * Updates the calendar planning interface.
 * 
 * @return {Promise}
 */
function reloadEvents()
{
	return retrieveEvents()
	.catch(function(error){
		showError("La récupération des évènements a échouée", error);
		return Promise.reject();
	})
	.then(function(events){
		updateEventsCalendar(events)
	})
	.catch(function(){
		/* Errors were treated as they were triggered. Some of them required different treatments so they couldn't be handled here. 
		 * This is only here to prevent unhandled rejected promise errors generation. */
	});
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
	    	"url": "/Planificateur/actions/loadEvents.php",
	        "type": "GET",
	        "contentType": "application/json;charset=utf-8",
	        "data": {
	        	"start": $('#calendar').fullCalendar('getCalendar').view.start.format("Y/M/D"), 
	        	"end": $('#calendar').fullCalendar('getCalendar').view.end.format("Y/M/D")
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
	$("#calendar").fullCalendar('removeEvents');
	$("#calendar").fullCalendar('addEventSource', events);
	$("#calendar").fullCalendar('rerenderEvents');
}

/**
 * Initiates the calendar planning interface.
 */
$(function(){
    // Chargement du calendrier
    $('#calendar').fullCalendar({
    	"height": "auto",
		"header": {"left": 'prev,next today', "center": 'title', "right": 'month, agendaWeek, agendaDay, listMonth'},
		"locale": 'fr-ca',
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
	        $('#calendar').fullCalendar('option', 'height', $("#calendar").height());
	    }
    });
    $('#calendar').css({"width": "100%"});

    
    $('.fc-prev-button, .fc-next-button, .fc-month-button, .fc-agendaWeek-button, .fc-agendaDay-button, .fc-listMonth-button')
    .click(function(){
    	reloadEvents();
    });
    
    $('.fc-agendaWeek-button, .fc-agendaDay-button, .fc-listMonth-button').click(function(){
    	$("html").css({"height": "100%"});
    	$("#calendar").fullCalendar("option", "height", "auto");
    });
    
    $('.fc-month-button').click(function(){
    	$("html").css({"height": "125%"});
    	$("#calendar").fullCalendar("option", "height", $("#calendar").height());
    }).click();
    
    reloadEvents();
});

/**
 * Reschedule an event
 * @param {FullCalendarEvent} event The event for which the planning is modified
 * 
 * @return {Promise}
 */
function reschedule(event)
{
	return rescheduleEvent(event)
	.catch(function(error){
		showError("Le changement de planification de l'évènement a échoué", error);
		return Promise.reject();
	})
	.then(function(color){
		event.color = color; // Le résultat modifie la couleur
		$('#calendar').fullCalendar('updateEvent', event); 
	})
	.catch(function(){
		/* Errors were treated as they were triggered. Some of them required different treatments so they couldn't be handled here. 
		 * This is only here to prevent unhandled rejected promise errors generation. */
	});
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
			"url": "/Planificateur/sections/job/actions/findBatchByJobName.php",
            "type": "POST",
            "contentType": "application/json;charset=utf-8",
            "data": JSON.stringify({"productionNumber": jobName}),
            "dataType": 'json',
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
 * 
 * @return {Promise}
 */
function findJobByProductionNumber()
{
	var productionNumber = $("form#findBatchByJobNumberForm > input[name=jobNumero]").val();
	return getBatchIdFromJobName(productionNumber)
	.catch(function(error){
		showError("La job \"" + productionNumber + "\" n'a pas été trouvée : ", error);
		return Promise.reject();
	})
	.then(function(id){
		window.location.assign(["/Planificateur/sections/batch/index.php", "?", "id=", id].join(""));
	})
	.catch(function(){
		/* Errors were treated as they were triggered. Some of them required different treatments so they couldn't be handled here. 
		 * This is only here to prevent unhandled rejected promise errors generation. */
	});
}