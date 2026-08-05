<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

expect()->extend('toBeMoney', function (string $expected) {
    return $this->toBe(number_format((float) $expected, 2, '.', ''));
});
