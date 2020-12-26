<?php

declare(strict_types=1);

use Faker\Generator as Faker;
use Umbrellio\TableSync\Tests\_data\Laravel\Models\TestModel;
use Umbrellio\TableSync\Tests\_data\Laravel\Models\TestModelWithExceptedFields;

$factory->define(TestModel::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'some_field' => $faker->word,
    ];
});

$factory->define(TestModelWithExceptedFields::class, function (Faker $faker) use ($factory) {
    return $factory->raw(TestModel::class);
});
