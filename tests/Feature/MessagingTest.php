<?php

use App\Models\Conversation;
use App\Models\Property;
use App\Models\User;

test('user can start a conversation about a published property', function () {
    $owner = User::factory()->create(['role' => 'owner', 'status' => 'active']);
    $property = Property::factory()->published()->create(['user_id' => $owner->id]);
    $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

    $response = $this->actingAs($user)->post(route('messages.store'), [
        'property_id' => $property->id,
        'body' => 'Is this still available?',
    ]);

    $conversation = Conversation::query()->first();

    expect($conversation)->not->toBeNull()
        ->and($conversation->property_id)->toBe($property->id)
        ->and($conversation->messages)->toHaveCount(1);

    $response->assertRedirect(route('messages.show', $conversation));
});

test('owner can message admin support and resume the same thread', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $owner = User::factory()->create(['role' => 'owner', 'status' => 'active']);

    $this->actingAs($owner)->post(route('messages.store'), [
        'recipient_id' => $admin->id,
        'body' => 'I need help with verification.',
    ])->assertRedirect();

    $first = Conversation::query()->first();
    expect($first)->not->toBeNull();

    $this->actingAs($admin)->post(route('messages.store'), [
        'recipient_id' => $owner->id,
        'body' => 'Happy to help — what do you need?',
    ])->assertRedirect();

    expect(Conversation::query()->count())->toBe(1)
        ->and($first->fresh()->messages)->toHaveCount(2);
});

test('admin can send a direct message to any user', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $tenant = User::factory()->create(['role' => 'user', 'status' => 'active']);

    $this->actingAs($admin)->post(route('messages.store'), [
        'recipient_id' => $tenant->id,
        'body' => 'Welcome to BetGo!',
    ])->assertRedirect();

    $conversation = Conversation::query()->first();

    expect($conversation)->not->toBeNull()
        ->and($conversation->initiator_id)->toBe($admin->id)
        ->and($conversation->recipient_id)->toBe($tenant->id)
        ->and($conversation->property_id)->toBeNull();
});

test('user cannot message admin without selecting a property', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

    $this->actingAs($user)->post(route('messages.store'), [
        'recipient_id' => $admin->id,
        'body' => 'Hello admin',
    ])->assertSessionHasErrors('property_id');

    expect(Conversation::query()->count())->toBe(0);
});

test('participants can open a conversation thread', function () {
    $owner = User::factory()->create(['role' => 'owner', 'status' => 'active']);
    $user = User::factory()->create(['role' => 'user', 'status' => 'active']);

    $conversation = Conversation::create([
        'initiator_id' => $user->id,
        'recipient_id' => $owner->id,
        'last_message_at' => now(),
    ]);

    $this->actingAs($user)->get(route('messages.show', $conversation))->assertSuccessful();
    $this->actingAs($owner)->get(route('messages.show', $conversation))->assertSuccessful();
});

test('strangers cannot view a private conversation', function () {
    $owner = User::factory()->create(['role' => 'owner', 'status' => 'active']);
    $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
    $stranger = User::factory()->create(['role' => 'user', 'status' => 'active']);

    $conversation = Conversation::create([
        'initiator_id' => $user->id,
        'recipient_id' => $owner->id,
        'last_message_at' => now(),
    ]);

    $this->actingAs($stranger)->get(route('messages.show', $conversation))->assertForbidden();
});
