require.config({
	paths: {
		bootstrap: 'bower_components/bootstrap/dist/js/bootstrap',
		'bootstrap-multiselect': 'bower_components/bootstrap-multiselect/js/bootstrap-multiselect',
		datatables: 'bower_components/datatables/media/js/jquery.dataTables',
		highcharts: 'bower_components/highcharts/highcharts',
		hogan: 'bower_components/hogan/web/builds/2.0.0/hogan-2.0.0.amd',
		jquery: 'bower_components/jquery/jquery',
		moment: 'bower_components/moment/moment',
		nprogress: 'bower_components/nprogress/nprogress',
		add2home: 'bower_components/add-to-homescreen/src/add2home',
		stayInWebApp: 'bower_components/stayInWebApp/jquery.stayInWebApp',
		typeahead: 'bower_components/typeahead.js/dist/typeahead',
		qunit: 'bower_components/qunit/qunit/qunit'
	},
	shim: {
		bootstrap: [
			'jquery'
		],
		'bootstrap-multiselect': [
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
	//'jquery',
	'js/pointsCenter',
	'nprogress',
	//'bootstrap',
	'bootstrap-multiselect',
	'datatables',
	'highcharts',
	'add2home',
	'stayInWebApp',
	'typeahead'
], function(spc, NProgress) {
	'use strict';
	var page = window.location.pathname.split('/');

	if (page) {
		page = page[2];
	} else {
		page = 'breakdown';
	}

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
