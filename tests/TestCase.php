<?php
namespace Tests;

/*
namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;


    public function setUp()
    {
        parent::setUp();
        Artisan::call('db:seed');
    }
}
*/


use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
        //Artisan::call('db:seed', ['--class' => 'DatabaseSeeder ', '--database' => 'testing']);
        Artisan::call('db:seed');
    }
}
