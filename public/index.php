<?php

/**
 * UserFrosting Application Entry Point
 * 
 * This is the single entry point for all HTTP requests to the application.
 * It initializes the UserFrosting framework and handles the request/response cycle.
 * 
 * @package PVUF
 * @author PaginaViva
 * @link https://github.com/paginaviva/pviva-FWUF
 */

// Define application paths
$projectRoot = dirname(__DIR__);

// Load Composer autoloader
require_once $projectRoot . '/vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
$dotenv->safeLoad();

// Bootstrap UserFrosting application
$app = require_once $projectRoot . '/app/app.php';

// Run the application
$app->run();
