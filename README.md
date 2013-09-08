Slivka Points Center
=================

## Setup Instructions

Requirements:
* Node
* git
* NPM Packages: Bower, Grunt-CLI

1. git clone the repo and setup the `ajax/datastoreVars.php` and `ajax/DatabasePDO.php` files
2. `bower install` and `npm install`
3. (for now) manually add `/bower_components/Highcharts-3.0.5/js/highcharts.src.js`
4. modify `/bower_components/hogan/.bower.json` with the line `main: "web/builds/2.0.0/hogan-2.0.0.amd.js"`
5. `grunt`
