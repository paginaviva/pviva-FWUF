<?php

declare(strict_types=1);

namespace UserFrosting\App;

use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Admin\Admin;

class MyApp implements SprinkleRecipe
{
    public function getName(): string
    {
        return 'PVUF Application';
    }

    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    public function getSprinkles(): array
    {
        return [
            Core::class,
            Account::class,
            Admin::class,
        ];
    }

    public function getRoutes(): array
    {
        return [];
    }

    public function getServices(): array
    {
        return [];
    }
}
