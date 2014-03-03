require.config({
	paths: {
		bootstrap: 'bower_components/bootstrap/dist/js/bootstrap',
		'bootstrap-daterangepicker': 'bower_components/bootstrap-daterangepicker/daterangepicker',
		'bootstrap-multiselect': 'bower_components/bootstrap-multiselect/js/bootstrap-multiselect',
		'bootstrap-switch': 'bower_components/bootstrap-switch/build/js/bootstrap-switch',
		datatables: 'bower_components/datatables/media/js/jquery.dataTables',
		highcharts: 'bower_components/highcharts/highcharts',
		hogan: 'bower_components/hogan/lib/hogan',
		jquery: 'bower_components/jquery/jquery',
		moment: 'bower_components/moment/moment',
		nprogress: 'bower_components/nprogress/nprogress',
		add2home: 'bower_components/add-to-homescreen/src/add2home',
		stayInWebApp: 'bower_components/stayInWebApp/jquery.stayInWebApp',
		typeahead: 'bower_components/typeahead.js/dist/typeahead'
	},
	shim: {
		bootstrap: [
			'jquery'
		],
		'bootstrap-daterangepicker': [
			'jquery'
		],
		'bootstrap-multiselect': [
			'jquery'
		],
		'bootstrap-switch': [
			'jquery'
		],
		datatables: [
			'jquery'
		],
		highcharts: [
			'jquery'
		],
		add2home: {
			exports: 'addToHome'
		},
		stayInWebApp: [
			'jquery'
		],
		nprogress: {
			deps: [
				'jquery'
			],
			exports: 'NProgress'
		},
		typeahead: [
			'jquery'
		]
	}
});

require([
	'jquery',
	'js/pointsCenter',
	'nprogress',
	'bootstrap',
	'bootstrap-daterangepicker',
	'bootstrap-multiselect',
	'bootstrap-switch',
	'datatables',
	'highcharts',
	'add2home',
	'stayInWebApp',
	'typeahead'
], function($, spc, NProgress) {
	'use strict';
	var page = window.location.pathname;
	page = page.lastIndexOf('/')+1 == page.length ? 'breakdown' : page.substring(page.lastIndexOf('/')+1, page.length-4);

	$('.' + page + '-link').addClass('active');

	//bind ajax start and stop to nprogress
	NProgress.configure({
		trickleRate: 0.1
	});
	$(document).on('ajaxStart', NProgress.start);
	$(document).on('ajaxStop', NProgress.done);

	//mobile app support
	$.stayInWebApp();

	spc[page].init();
});
