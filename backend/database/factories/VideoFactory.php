<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Video;
use Faker\Generator as Faker;

$factory->define(Video::class, function (Faker $faker) {
    $rating = array_rand(Video::RATING_LIST);
    return [
        'title' => $faker->sentence(3),
        'description' => $faker->sentence(10),
        'year_launched' => rand(1895, 2022),
        'rating' => Video::RATING_LIST[$rating],
        'duration' => rand(1, 30),
    ];
});
