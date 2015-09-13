Slivka Points Center
=================

## Setup Instructions

Requirements:
* Node
* git
* NPM Packages: Bower, Grunt-CLI

1. git clone the repo and setup `ajax/datastoreVars.php`
2. `npm i -g bower grunt-cli`
3. `bower install` and `npm install`
4. `grunt`

## datastoreVars.php
* `$DB_TYPE`: Probably "mysql"
* `$DB_HOST`: Probably "localhost" unless the database moves somewhere
* `$DB_NAME`: The name of the database with all the slivka tables
* `$DB_USER`: Username
* `$DB_PASS`: Password
