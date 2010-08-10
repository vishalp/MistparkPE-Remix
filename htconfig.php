<?php

// Set the following for your MySQL installation
// Copy or rename this file to .htconfig.php

$db_host = 'your.mysqlhost.com';
$db_user = 'mysqlusername';
$db_pass = 'mysqlpassword';
$db_data = 'mysqldatabasename';

// Choose a legal default timezone. If you are unsure, use "America/Los_Angeles".
// It can be changed later and only applies to timestamps for anonymous viewers.

$default_timezone = 'Australia/Sydney';

// What is your site name?

$a->config['sitename'] = "My Friend Network";

// Maximum size of an imported message, 0 is unlimited (but our database 'text' element is limited to 65535).

$a->config['max_import_size'] = 65535;