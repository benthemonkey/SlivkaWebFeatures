module.exports = function(grunt) {
	"use strict";
	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),
		concat: {
			options: {
				separator: ";"
			},
			dist: {
				src: ["js/jquery.js","js/jquery-ui.js","js/jquery.stayInWebApp.js","js/bootstrap.js","js/typeahead.js","js/pointsCenter.js"],
				dest: "js/<%= pkg.name %>.built.js"
			}
		},
		uglify: {
			options: {
				banner: "/*! <%= pkg.name %> <%= grunt.template.today(\"dd-mm-yyyy\") %> */\n"
			},
			dist: {
				files: {
					"js/<%= pkg.name %>.built.min.js": ["<%= concat.dist.dest %>"]
				}
			}
		},
		qunit: {
			files: ["test/tests.html"]
		},
		jshint: {
			files: ["gruntfile.js", "js/pointsCenter.js"],
			options: {
				// options here to override JSHint defaults
				"browser": true,
				"esnext": true,
				"quotmark": "double",
				"smarttabs": true,
				"trailing": true,
				"undef": true,
				"unused": true,
				"strict": true,
				globals: {
					jQuery: true,
					google: true,
					console: true,
					module: true,
					document: true
				}
			}
		},
		watch: {
			files: ["<%= jshint.files %>"],
			tasks: ["jshint", "qunit"]
		},
		cssmin: {
			combine: {
				options: {
					banner: "/* Slivka Points Center minified css (includes Bootstrap, Bootstrap-theme)",
					keepSpecialComments: 0,
					report: "gzip"
				},
				files: {
					"css/<%= pkg.name %>.built.css": [
						"css/bootstrap.css",
						"css/bootstrap-theme.css",
						"css/typeahead.js-bootstrap.css",
						"css/pointsCenter.css",
						"css/pointsTable.css"
					]
				}
			}/*,
			minify: {
				expand: true,
				src: ["css/<%= pkg.name %>.built.css"],
				dest: "",
				ext: ".built.min.css"
			}*/
		}
	});

	grunt.loadNpmTasks("grunt-contrib-uglify");
	grunt.loadNpmTasks("grunt-contrib-jshint");
	grunt.loadNpmTasks("grunt-contrib-qunit");
	grunt.loadNpmTasks("grunt-contrib-watch");
	grunt.loadNpmTasks("grunt-contrib-concat");
	grunt.loadNpmTasks("grunt-contrib-cssmin");

	grunt.registerTask("test", ["jshint", "qunit"]);

	grunt.registerTask("default", ["jshint", "qunit", "concat", "uglify", "cssmin"]);

};