<?php
namespace Tests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParentTestClass extends TestCase
{
    use RefreshDatabase;
    public static $is_migrated = false;

    public static function setUpBeforeClass(): void {
        exec('php artisan migrate');
    }

    public function tearDown(): void
    {
        \DB::connection()->setPdo(null);
        parent::tearDown();
    }
}