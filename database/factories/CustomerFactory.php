<?php

use Faker\Generator as Faker;

$factory->define(App\Customer::class, function (Faker $faker) {
    return [
        'name'    => $faker->name,
        'company' => $faker->sentence,
    ];
});
