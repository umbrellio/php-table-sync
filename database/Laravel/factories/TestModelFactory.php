<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Umbrellio\TableSync\Tests\functional\Laravel\Models\TestModel;
use Umbrellio\TableSync\Tests\functional\Laravel\Models\TestModelWithExceptedFields;

$factory->define(TestModel::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'some_field' => $faker->word,
    ];
});

$factory->define(TestModelWithExceptedFields::class, function () use ($factory) {
    return $factory->raw(TestModel::class);
});
