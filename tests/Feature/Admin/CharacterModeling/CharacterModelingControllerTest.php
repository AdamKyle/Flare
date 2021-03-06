<?php

namespace Tests\Feature\Admin\CharacterModeling;

use App\Admin\Jobs\RunTestSimulation;
use App\Flare\Mail\GenericMail;
use App\Flare\Models\Character;
use App\Flare\Models\CharacterSnapShot;
use App\Flare\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mail;
use Queue;
use Tests\TestCase;
use Tests\Traits\CreateUser;
use Tests\Traits\CreateRole;
use Tests\Traits\CreateItem;
use Tests\Traits\CreateRace;
use Tests\Traits\CreateClass;
use Tests\Traits\CreateGameMap;
use Tests\Setup\Character\CharacterFactory;
use Tests\Traits\CreateGameSkill;
use Tests\Traits\CreateMonster;
use Tests\Traits\CreateAdventure;

class CharacterModelingControllerTest extends TestCase
{
    use RefreshDatabase,
        CreateUser,
        CreateRole,
        CreateItem,
        CreateClass,
        CreateRace,
        CreateGameMap,
        CreateGameSkill,
        CreateMonster,
        CreateAdventure;

    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $role = $this->createAdminRole();

        $this->user = $this->createAdmin([], $role);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->user = null;
    }

    public function testCanSeeGenerateButton() {
        $this->actingAs($this->user)->visit(route('admin.character.modeling'))
                                    ->see('Generate Character Modeling');
    }

    public function testDoNotSeeGenerateButtonWhenThereAreSnapShots() {
        CharacterSnapShot::factory()->create([
            'character_id' => (new CharacterFactory)->createBaseCharacter()->getCharacter()->id,
            'snap_shot'    => []
        ]);

        $this->actingAs($this->user)->visit(route('admin.character.modeling'))
                                    ->dontSee('Generate Character Modeling')
                                    ->see('Modeling');
    }

    public function testFetchSheetForTestCharacter() {
        $character = (new CharacterFactory)->createBaseCharacter()->getCharacter();

        $this->actingAs($this->user)->visit(route('admin.character.modeling.sheet', [
            'character' => $character
        ]))->see($character->name)->see('Character management');
    }

    public function testAssignItemToCharacter() {
        $character = (new CharacterFactory)->createBaseCharacter()->getCharacter();

        $this->actingAs($this->user)->post(route('admin.character-modeling.assign-item', [
            'character' => $character->id
        ]), [
            'item_id' => $this->createItem()->id
        ]);

        $this->assertTrue($character->refresh()->inventory->slots->isNotEmpty());
    }

    public function testFailToAssignItemToCharacter() {
        $character = (new CharacterFactory)->createBaseCharacter()->updateCharacter(['inventory_max' => 0])->getCharacter();

        $this->actingAs($this->user)->post(route('admin.character-modeling.assign-item', [
            'character' => $character->id
        ]), [
            'item_id' => $this->createItem()->id
        ]);

        $this->assertFalse($character->refresh()->inventory->slots->isNotEmpty());
    }

    public function testAssignMultipleItemsToCharacter() {
        $character = (new CharacterFactory)->createBaseCharacter()->getCharacter();

        $this->actingAs($this->user)->post(route('admin.character-modeling.assign-all', [
            'character' => $character->id
        ]), [
            'items' => [$this->createItem()->id]
        ]);

        $this->assertTrue($character->refresh()->inventory->slots->isNotEmpty());
    }

    public function testFailToAssignMultipleItemsToCharacter() {
        $character = (new CharacterFactory)->createBaseCharacter()->updateCharacter(['inventory_max' => 0])->getCharacter();

        $this->actingAs($this->user)->post(route('admin.character-modeling.assign-all', [
            'character' => $character->id
        ]), [
            'items' => [$this->createItem()->id]
        ]);

        $this->assertFalse($character->refresh()->inventory->slots->isNotEmpty());
    }

    public function testResetCharacterInventory() {
        $character = (new CharacterFactory)->createBaseCharacter()->equipStartingEquipment();

        $item      = $this->createItem(['name' => 'apple sauce']);

        $character = $character->inventoryManagement()
                               ->giveItem($item)
                               ->getCharacterFactory()
                               ->getCharacter();

        $this->actingAs($this->user)->post(route('admin.character.modeling.reset-inventory', [
            'character' => $character->id
        ]));


        $foundSlot = $character->refresh()->inventory->slots->filter(function($slot) use($item) {
            return $slot->item_id === $item->id;
        })->first();

        $this->assertNull($foundSlot);
    }

    public function testApplySnapShot() {
        $character = (new CharacterFactory)->createBaseCharacter();

        $snapShotOne = CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->getCharacter()->getAttributes(),
        ]);

        $snapShotTwo = CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->levelCharacterUp()->getCharacter()->getAttributes(),
        ]);

        $beforetest = $character->getCharacter();

        $this->assertEquals($beforetest->getAttributes(), $snapShotTwo->snap_shot);

        $character = $character->getCharacter();

        $this->actingAs($this->user)->post(route('admin.character.modeling.assign-snap-shot', [
            'character' => $character->id
        ]), [
            'snap_shot' => $snapShotOne->id
        ]);

        $character = $character->refresh();

        $attributes = $character->getAttributes();
        $snapShot   = $snapShotOne->snap_shot;

        // These tend to be off by a bit.
        // So we remove them.
        unset($attributes['created_at']);
        unset($attributes['updated_at']);

        unset($snapShot['created_at']);
        unset($snapShot['updated_at']);

        $this->assertEquals($attributes, $snapShot);
    }

    public function testGenerateCharacters() {
        Queue::fake();

        $this->createRace();
        $this->createClass();

        $this->actingAs($this->user)->post(route('admin.character.modeling.generate'))->response;

        $this->assertEquals(1, User::count());
    }

    public function testCannotGenerateCharacters() {
        Queue::fake();

        (new CharacterFactory)->createBaseCharacter();

        $response = $this->actingAs($this->user)->post(route('admin.character.modeling.generate'))->response;

        $response->assertSessionHas('error', 'You already have test characters for every race and class and combination of.');
    }

    public function testGenerateCharactersWithJob() {
        Mail::fake();

        $this->createRace();
        $this->createClass();
        $this->createRace();
        $this->createClass();
        $this->createGameSkill(['name' => 'Accuracy']);
        $this->createGameSkill(['name' => 'Dodge']);
        $this->createGameSkill(['name' => 'Looting']);
        $this->createGameMap();
        $this->createItem();

        $this->actingAs($this->user)->post(route('admin.character.modeling.generate'))->response;

        Mail::assertSent(GenericMail::class);

        $this->assertTrue(true);
    }

    public function testSimmulateMonsterBattle() {
        Queue::fake();

        $this->createMonster();

        $character = (new CharacterFactory)->createBaseCharacter();

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->getCharacter()->getAttributes(),
        ]);

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->levelCharacterUp()->getCharacter()->getAttributes(),
        ]);

        $this->user->update([
            'is_test' => false,
        ]);

        $response = $this->actingAs($this->user)->visit(route('monsters.list'))->post(route('admin.character.modeling.test'), [
            'model_id' => $this->createMonster()->id,
            'type' => 'monster',
            'characters' => [$character->getCharacter()->id],
            'character_levels' => '1',
            'total_times' => '1',
        ])->response;

        $response->assertSessionHas('success', 'Testing under way. You may log out, we will email you when done.');
    }

    public function testSimmulateMonsterBattleWithMultipleCharacters() {
        Queue::fake();

        $this->createMonster();

        $character = (new CharacterFactory)->createBaseCharacter();

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->getCharacter()->getAttributes(),
        ]);

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->levelCharacterUp()->getCharacter()->getAttributes(),
        ]);

        $this->user->update([
            'is_test' => false,
        ]);

        $response = $this->actingAs($this->user)->visit(route('monsters.list'))->post(route('admin.character.modeling.test'), [
            'model_id' => $this->createMonster()->id,
            'type' => 'monster',
            'characters' => [$character->getCharacter()->id, $character->getCharacter()->id, $character->getCharacter()->id],
            'character_levels' => '1',
            'total_times' => '1',
        ])->response;

        $response->assertSessionHas('success', 'Testing under way. You may log out, we will email you when done.');
    }

    public function testSimmulateAdventure() {
        Queue::fake();

        $character = (new CharacterFactory)->createBaseCharacter();

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->getCharacter()->getAttributes(),
        ]);

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->levelCharacterUp()->getCharacter()->getAttributes(),
        ]);

        $this->user->update([
            'is_test' => false,
        ]);

        $response = $this->actingAs($this->user)->visit(route('adventures.list'))->post(route('admin.character.modeling.test'), [
            'model_id' => $this->createNewAdventure()->id,
            'type' => 'adventure',
            'characters' => [$character->getCharacter()->id, $character->getCharacter()->id],
            'character_levels' => '1',
            'total_times' => '1',
        ])->response;

        $response->assertSessionHas('success', 'Testing under way. You may log out, we will email you when done.');
    }

    public function testFailSimmulateMonsterBattleWithMoreThenTenRounds() {
        Queue::fake();

        $this->createMonster();

        $character = (new CharacterFactory)->createBaseCharacter();

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->getCharacter()->getAttributes(),
        ]);

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->levelCharacterUp()->getCharacter()->getAttributes(),
        ]);

        $this->user->update([
            'is_test' => false,
        ]);

        $response = $this->actingAs($this->user)->visit(route('monsters.list'))->post(route('admin.character.modeling.test'), [
            'model_id' => $this->createMonster()->id,
            'type' => 'monster',
            'characters' => [$character->getCharacter()->id, $character->getCharacter()->id],
            'character_levels' => '1',
            'total_times' => '100',
        ])->response;

        $response->assertSessionHas('error', 'You may only repeat this test 10 times with any one character.');
    }

    public function testCannotSimmulateMonsterBattleNonExistantCharacter() {
        Queue::fake();

        $this->createMonster();

        $character = (new CharacterFactory)->createBaseCharacter();

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->getCharacter()->getAttributes(),
        ]);

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->levelCharacterUp()->getCharacter()->getAttributes(),
        ]);

        $this->user->update([
            'is_test' => false,
        ]);

        $response = $this->actingAs($this->user)->visit(route('monsters.list'))->post(route('admin.character.modeling.test'), [
            'model_id' => $this->createMonster()->id,
            'type' => 'monster',
            'characters' => [22],
            'character_levels' => '1',
            'total_times' => '2',
        ])->response;

        $response->assertSessionHas('error', 'Character does not exist for id: 22');
    }

    public function testCannotSimmulateMonsterBattleSnapShotDoesntExist() {
        Queue::fake();

        $this->createMonster();

        $character = (new CharacterFactory)->createBaseCharacter();

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->getCharacter()->getAttributes(),
        ]);

        CharacterSnapShot::factory()->create([
            'character_id' => $character->getCharacter()->id,
            'snap_shot'    => $character->levelCharacterUp()->getCharacter()->getAttributes(),
        ]);

        $this->user->update([
            'is_test' => false,
        ]);

        $response = $this->actingAs($this->user)->visit(route('monsters.list'))->post(route('admin.character.modeling.test'), [
            'model_id' => $this->createMonster()->id,
            'type' => 'monster',
            'characters' => [$character->getCharacter()->id],
            'character_levels' => '150',
            'total_times' => '1',
        ])->response;

        $response->assertSessionHas('error', 'Level entered does not match any snap shot data for character: 1');
    }

    public function testSeeMonsterDetails() {
        $this->createBattleResults();

        $this->actingAs($this->user)->visit(route('admin.character.modeling.monster-data', [
            'monster' => 1
        ]))->see('Battle Simulation Data For: ');
    }

    public function testSeeBattleResults() {
        $this->createBattleResults();

        $this->actingAs($this->user)->visit(route('admin.character.modeling.battle-simmulation.results', [
            'characterSnapShot' => 1
        ]))->see('Data For Fight');

        $this->assertEquals(2, Character::first()->level);
    }

    public function testSeeBattleResultsMultipleTimes() {
        $this->createBattleResults(3);

        $this->actingAs($this->user)->visit(route('admin.character.modeling.battle-simmulation.results', [
            'characterSnapShot' => 1
        ]))->see('Data For Fight');
    }

    public function testSeeAdventureDetails() {
        $this->createAdventureResults(2);

        $this->actingAs($this->user)->visit(route('admin.character.modeling.adventure-data', [
            'adventure' => 1
        ]))->see('Adventure Simulation Data For: ');
    }

    public function testSeeAdventureResults() {
        $this->createAdventureResults();

        $this->actingAs($this->user)->visit(route('admin.character.modeling.adventure-simmulation.results', [
            'characterSnapShot' => 1
        ]))->see('Data For Fight');
    }

    protected function createBattleResults(int $times = 1) {
        $this->createRace();
        $this->createClass();
        $this->createGameSkill(['name' => 'Accuracy']);
        $this->createGameSkill(['name' => 'Dodge']);
        $this->createGameSkill(['name' => 'Looting']);
        $this->createGameMap();
        $this->createItem();

        $this->actingAs($this->user)->post(route('admin.character.modeling.generate'))->response;

        $this->actingAs($this->user)->visit(route('monsters.list'))->post(route('admin.character.modeling.test'), [
            'model_id' => $this->createMonster()->id,
            'type' => 'monster',
            'characters' => [1],
            'character_levels' => '1',
            'total_times' => $times,
        ]);
    }

    protected function createAdventureResults($timesToRun = 1) {
        Mail::fake();

        $this->createRace();
        $this->createClass();
        $this->createGameSkill(['name' => 'Accuracy']);
        $this->createGameSkill(['name' => 'Dodge']);
        $this->createGameSkill(['name' => 'Looting']);
        $this->createGameMap();
        $this->createItem();

        $this->actingAs($this->user)->post(route('admin.character.modeling.generate'))->response;

        for ($i = 1; $i <= $timesToRun; $i++) {
            $this->actingAs($this->user)->visit(route('adventures.list'))->post(route('admin.character.modeling.test'), [
                'model_id' => $this->createNewAdventure()->id,
                'type' => 'adventure',
                'characters' => [1],
                'character_levels' => '1',
                'total_times' => '1',
            ]);
        }

        Mail::assertSent(GenericMail::class);
    }
}
