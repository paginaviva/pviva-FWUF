<?php

declare(strict_types=1);

/**
 * UserFrosting Application Bootstrap
 * 
 * This file creates and configures the UserFrosting application instance.
 * 
 * @package PVUF
 */

use UserFrosting\App\MyApp;
use UserFrosting\UserFrosting;

// Create and return UserFrosting application with main sprinkle
return new UserFrosting(MyApp::class);
