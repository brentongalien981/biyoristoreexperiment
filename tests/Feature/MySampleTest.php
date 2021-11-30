<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MySampleTest extends TestCase
{
    use RefreshDatabase;



    public function testExample()
    {
        $u = User::factory()->create();

        $this->assertDatabaseHas('users', ['email' => $u->email]);
        $this->assertTrue(true);
    }
}
