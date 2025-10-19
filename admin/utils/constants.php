<?php

// Define the base URL of the API

$serverHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = in_array($serverHost, ['localhost', '127.0.0.1']);

// Environment switch
define('BASE_URL', 'https://himish.com:4002/apis');
define('BASE_URL_NORMAL', 'https://himish.com:4002');

// Define the media base URL 
define('MEDIA_BASE_URL', 'https://postersz.nyc3.cdn.digitaloceanspaces.com/images/');

// Define the security key (authentication header)
define('SECURITY_KEY', 'SGltaXNoIEFwcCBDcmVhdGVkIEJ5IENoYW5kYW4');

// NO DATA AVAILABLE TEXT
define('NO_DATA_IMG', 'assets/images/empty-icon.png');
define('NO_DATA_TITLE', 'No Results Available');
define('NO_DATA_DESC', 'It looks like there’s no matching data at the moment. Try adjusting your search criteria for better results.');

// BRANCH IO
define('BRANCH_KEY', 'key_live_jqAeQ389tV8KAAe5ylia6kpcDvm7APMs');
define('BRANCH_SECRET', 'secret_live_cyhP7DwxDDR6nx9RYdY4Ff0mJEgIEsdo');
define('BRANCH_HOST', 'mypostersz.app.link');
define('BRANCH_TEST_MODE', false); // TestMode
define('DEFAULT_PAGINATION_LIMIT',30);

define('GOOGLE_MAPS_API_KEY',"AIzaSyA8-xD4gQvyPqth_tvkgSuKwf7-p0cmSvc");
define('GEOCODING_API_KEY',"AIzaSyDnS3r4DGCTdcP6YWn8VuB398kBqA4lGG8");

define('MY_URL_SECRET_KEY',"HIM_MHbyuITdfEDOJlqMKhjUYTvFRghxWmAty3XzmxjO4Gu_ISH");
define("MAINTENANCE_MODE_HM", false);

?>