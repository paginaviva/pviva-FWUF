<?php

/**
 * UserFrosting Application Bootstrap
 * 
 * This file creates and configures the UserFrosting application instance.
 * 
 * @package PVUF
 */

use UserFrosting\UserFrosting;

// Define paths
$projectRoot = dirname(__DIR__);

// Create UserFrosting application
$uf = new UserFrosting($projectRoot);

// Return the application instance
return $uf;
