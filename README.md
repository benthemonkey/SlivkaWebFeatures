Slivka Points Center
=================

## Setup Instructions

Requirements:
* Node
* git
* NPM Packages: Bower, Grunt-CLI

1. git clone the repo and setup `ajax/datastoreVars.php`
2. `bower install` and `npm install`
3. modify `/bower_components/hogan/.bower.json` with the line `main: "web/builds/2.0.0/hogan-2.0.0.amd.js"`
4. `grunt`

## datastoreVars.php
* `$DB_TYPE`: Probably "mysql"
* `$DB_HOST`: Probably "localhost" unless the database moves somewhere
* `$DB_NAME`: The name of the database with all the slivka tables
* `$DB_USER`: Username
* `$DB_PASS`: Password
