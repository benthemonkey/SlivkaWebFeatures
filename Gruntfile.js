module.exports = function(grunt) {
	'use strict';
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		bower: {
			target: {
				rjsConfig: 'pointsCenter.build.js',
				options: {
					exclude: ['qunit', 'almond']
				}
			}
		},
		requirejs: {
			compile: {
				options: {
					baseUrl: './',
					mainConfigFile: 'pointsCenter.build.js',
					include: 'pointsCenter.build.js',
					name: 'bower_components/almond/almond',
					out: 'pointsCenter.built.js',
					optimize: 'uglify',
					uglify: {
						toplevel: true,
						ascii_only: true,
						beautify: false,
						max_line_length: 10000
					},
					inlineText: true,
					useStrict: false,
					skipPragmas: false,
					skipModuleInsertion: false,
					optimizeAllPluginResources: false,
					findNestedDependencies: false,
					removeCombined: false,
					fileExclusionRegExp: /^\./,
					preserveLicenseComments: false,
					logLevel: 0
				}
			}
		},
		qunit: {
			files: ['test/tests.html']
		},
		jshint: {
			files: ['gruntfile.js', 'js/pointsCenter.js'],
			options: {
				// options here to override JSHint defaults
				'browser': true,
				'esnext': true,
				'quotmark': 'single',
				'smarttabs': true,
				'trailing': true,
				'undef': true,
				'unused': true,
				'strict': true,
				globals: {
					define: true,
					console: true,
					module: true
				}
			}
		},
		watch: {
			files: ['<%= jshint.files %>'],
			tasks: ['jshint', 'qunit']
		},
		cssmin: {
			combine: {
				options: {
					banner: '/* Slivka Points Center minified css (includes Bootstrap, Bootstrap-theme, typeahead, nprogress, bootstrap-multiselect, and add2home) */',
					keepSpecialComments: 0,
					report: 'gzip'
				},
				files: {
					'css/<%= pkg.name %>.built.css': [
						'css/bootstrap.css',
						'css/bootstrap-theme.css',
						'css/typeahead.js-bootstrap.css',
						'css/nprogress.css',
						'css/bootstrap-multiselect.css',
						'css/add2home.css',
						'css/pointsCenter.css'
					]
				}
			},
			minify: {
				expand: true,
				src: ['css/<%= pkg.name %>.built.css'],
				dest: '',
				ext: '.built.min.css'
			}
		},
		compress: {
			main: {
				options: {
					mode: 'gzip'
				},
				expand: true,
				src: ['css/<%= pkg.name %>.built.min.css'],
				dest: '',
				ext: '.built.min.css.gz'
			}
		}
	});

	grunt.loadNpmTasks('grunt-bower-requirejs');
	grunt.loadNpmTasks('grunt-contrib-requirejs');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-qunit');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-compress');

	grunt.registerTask('test', ['jshint', 'qunit']);

	grunt.registerTask('default', ['jshint', 'qunit', 'bower', 'requirejs', 'cssmin']);

};
