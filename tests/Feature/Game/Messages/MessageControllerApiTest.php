<?php

namespace Tests\Feature\Game\Messages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Setup\Character\CharacterFactory;
use Tests\Traits\CreateRole;
use Tests\Traits\CreateUser;

class MessageControllerApiTest extends TestCase
{
    use RefreshDatabase, CreateRole, CreateUser;

    private $character;

    public function setUp(): void {
        parent::setUp();

        $role = $this->createAdminRole();

        $this->createAdmin([], $role);

        $this->character = (new CharacterFactory)->createBaseCharacter()->givePlayerLocation();

        Event::fake();
    }

    public function tearDown(): void {
        parent::tearDown();

        $this->character= null;
    }

    public function testFetchUserInfo() {
        $user = $this->character->getUser();

        $response = $this->actingAs($user)
                         ->json('GET', '/api/user-chat-info/' . $user->id)
                         ->response;

        $content = json_decode($response->content());

        $this->assertFalse($content->user->is_silenced);
    }

    public function testFetchMessages() {
        $user = $this->character->getUser();

        $response = $this->actingAs($user)
                         ->json('GET', '/api/last-chats/')
                         ->response;

        $this->assertEquals(200, $response->status());
    }

    public function testUserCanSendMessage() {
        $user = $this->character->getUser();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/public-message', [
                             'message' => 'sample'
                         ])
                         ->response;

        $this->assertEquals(200, $response->status());
    }

    public function testWhenNotLoggedInCannotSendMessage() {
        $response = $this->json('POST', '/api/public-message', [
                             'message' => 'sample'
                         ])
                         ->response;

        $this->assertEquals(401, $response->status());
    }

    public function testGetServerMesssageForType() {
        $user = $this->character->getUser();

        $response = $this->actingAs($user)
                         ->json('GET', '/api/server-message', [
                             'type' => 'message_length_0'
                         ])
                         ->response;

        $this->assertEquals(200, $response->status());
    }


    public function testWhenNotLoggedInCannotAccessServerMessage() {
        $response = $this->json('GET', '/api/server-message', [
                             'type' => 'message_length_0'
                         ])
                         ->response;

        $this->assertEquals(401, $response->status());
    }

    public function testSendPrivateMesssage() {
        $user = $this->character->getUser();
        $character = (new CharacterFactory)->createBaseCharacter();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/private-message', [
                             'message' => 'sample',
                             'user_name' => $character->getCharacter()->name
                         ])
                         ->response;

        $user      = $this->character->getUser();
        $character = $character->getCharacter();

        $this->assertEquals($user->messages->first()->fromUser->id, $user->id);
        $this->assertEquals($user->messages->first()->toUser->id, $character->user->id);
        $this->assertEquals($user->messages->first()->message, 'sample');

        $this->assertEquals(200, $response->status());
    }

    public function testCannotFindPlayerForPrivateMesssage() {
        $user = $this->character->getUser();

        $response = $this->actingAs($user)
                         ->json('POST', '/api/private-message', [
                             'message' => 'sample',
                             'user_name' => 'Gorge'
                         ])
                         ->response;

        $this->assertEquals(200, $response->status());
    }

    public function testNotLoggedIn() {

        $response = $this->json('POST', '/api/private-message', [
                             'message' => 'sample',
                             'user_name' => 'Gorge'
                         ])
                         ->response;

        $this->assertEquals(401, $response->status());
    }
}
