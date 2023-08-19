<?php

namespace Tests\Unit;

use App\Actions\GetModelsClassNameInLowerCaseAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function testCanGetModelsClassNameAdLowerCaseString()
    {
        $user = User::factory()->create();

        $className = GetModelsClassNameInLowerCaseAction::make()->execute($user);

        $this->assertEquals("user", $className);
    }
}