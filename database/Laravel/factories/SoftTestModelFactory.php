<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Umbrellio\TableSync\Tests\functional\Laravel\Models\SoftTestModel;

$factory->define(SoftTestModel::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
