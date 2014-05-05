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

* `$VP_NAME`: The name of the current Slivka Vice President
* `$VP_EMAIL`: The VP's email address
* `$VP_EMAIL_COPIES`: The email address to CC for all outgoing emails. It is recommended that you use gmail's feature where you can add a "+" to the end of your gmail address and then any string you want. That way, you can easily set up a gmail filter for all emails to "YOURGMAIL+mailbot@gmail.com", for example.
* `$MAILBOT_EMAIL`: username of the account used to send points center emails
* `$MAILBOT_PASS`: A temporary password set up by the VP for use by the Points Center email service
* `$VP_EMAIL_POINT_SUBMISSION_NOTIFICATIONS1: true/false, whether the Vice President would like to receive email notifications every time points are submitted