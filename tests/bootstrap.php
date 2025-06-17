<?php

// Set error reporting
error_reporting(-1);
ini_set('display_errors', '1');

// Set the default timezone
date_default_timezone_set('UTC');

// Include the Composer autoloader
$autoloader = require __DIR__ . '/../vendor/autoload.php';

// Add test classes to the autoloader
$autoloader->addPsr4('SugiPHP\\Logger\\Tests\\', __DIR__);
