/*
	Verti by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
*/

(function($){

	skel.breakpoints({
		xlarge: '(max-width: 1680px)',
		large: '(max-width: 1280px)',
		medium: '(max-width: 980px)',
		small: '(max-width: 736px)'
	});

	// Disable animations/transitions until the page has loaded.
	$("body").addClass('is-loading');
	
	$(window).on('load', function(){
		$("body").removeClass('is-loading');
	});
		
	$(function() {
		// Fix: Placeholder polyfill.
		$('form').placeholder();

		// Prioritize "important" elements on medium.
		skel.on('+medium -medium', function() {
			$.prioritize('.important\\28 medium\\29', skel.breakpoint('medium').active);
		});

		// Dropdowns.
		$("#nav > ul").dropotron({
			"mode": "fade",
			"noOpenerFade": true,
			"speed": 300,
			"expandMode" : "click"
		});

		// Navigation Toggle.
		$("body").append(
			$("<div></div>").attr({"id": "navToggle"}).append($("<a></a>").addClass("toggle").attr({"href": "#navPanel"}))
		);

		// Navigation Panel.
		$("<div></div>").attr({"id": "navPanel"}).append($("<nav></nav>").html($('#nav').navList())).appendTo($("body")).panel({
			"delay": 500,
			"hideOnClick": true,
			"hideOnSwipe": true,
			"resetScroll": true,
			"resetForms": true,
			"side": "left",
			"target": $("body"),
			"visibleClass": "navPanel-visible"
		});

		// Fix: Remove navPanel transitions on WP<10 (poor/buggy performance).
		if (skel.vars.os == 'wp' && skel.vars.osVersion < 10)
		{
			$('#navToggle, #navPanel, #page-wrapper').css('transition', 'none');
		}
	});

})(jQuery);