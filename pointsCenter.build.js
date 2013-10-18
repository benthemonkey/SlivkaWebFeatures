require.config({
	paths: {
		bootstrap: "bower_components/bootstrap/dist/js/bootstrap",
		"bootstrap-daterangepicker": "bower_components/bootstrap-daterangepicker/daterangepicker",
		"bootstrap-multiselect": "bower_components/bootstrap-multiselect/js/bootstrap-multiselect",
		"bootstrap-switch": "bower_components/bootstrap-switch/static/js/bootstrap-switch",
		datatables: "bower_components/datatables/media/js/jquery.dataTables",
		highcharts: "bower_components/Highcharts-3.0.5/js/highcharts.src",
		hogan: "bower_components/hogan/web/builds/2.0.0/hogan-2.0.0.amd",
		jquery: "bower_components/jquery/jquery",
		moment: "bower_components/moment/moment",
		nprogress: "bower_components/nprogress/nprogress",
		stayInWebApp: "bower_components/stayInWebApp/jquery.stayInWebApp",
		typeahead: "bower_components/typeahead.js/dist/typeahead"
	},
	shim: {
		bootstrap: [
			"jquery"
		],
		"bootstrap-daterangepicker": [
			"jquery"
		],
		"bootstrap-multiselect": [
			"jquery"
		],
		"bootstrap-switch": [
			"jquery"
		],
		highcharts: [
			"jquery"
		],
		stayInWebApp: [
			"jquery"
		],
		nprogress: {
			deps: [
				"jquery"
			],
			exports: "NProgress"
		},
		typeahead: [
			"jquery"
		]
	},
	deps: [
		"bootstrap",
		"bootstrap-daterangepicker",
		"bootstrap-multiselect",
		"bootstrap-switch",
		"datatables",
		"highcharts",
		"jquery",
		"stayInWebApp",
		"typeahead"
	]
});

require(["jquery","js/pointsCenter"],function($,spc) {
	$(document).ready(function(){
		page = window.location.pathname.split("/");
		page = page[page.length-1];
		page = page.substr(0,page.length-4);

		if(page == "index" || page === ""){ page = "breakdown"; }
		spc[page].init();
	});
});
