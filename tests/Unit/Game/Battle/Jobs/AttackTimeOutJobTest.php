<?php

namespace Tests\Unit\Game\Battle\Jobs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Game\Battle\Jobs\AttackTimeOutJob;
use App\Game\Battle\Events\ShowTimeOutEvent;
use Tests\TestCase;
use Tests\Traits\CreateUser;
use Tests\Setup\CharacterSetup;

class AttackTimeOutJobTest extends TestCase
{
    use RefreshDatabase, CreateUser;


    public function testAttackTimeOutJob()
    {
        Event::fake([
            ShowTimeOutEvent::class,
        ]);

        $user = $this->createUser();

        $character = (new CharacterSetup)->setupCharacter($user, ['can_attack' => false])
                                         ->getCharacter();

        AttackTimeOutJob::dispatch($character);

        $character->refresh();

        $this->assertTrue($character->can_attack);
    }
}