<?php

namespace App\Services\Messaging;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConversationService
{
    public function canMessage(User $sender, User $recipient, ?Property $property = null): bool
    {
        if ($sender->id === $recipient->id) {
            return false;
        }

        if ($sender->role === 'admin') {
            return true;
        }

        if ($property !== null) {
            if ($property->status !== 'published') {
                return false;
            }

            if ($property->user_id !== $recipient->id) {
                return false;
            }

            return true;
        }

        if (in_array($sender->role, ['owner', 'agent'], true)) {
            return $recipient->role === 'admin';
        }

        return false;
    }

    public function findBetween(User $first, User $second, ?Property $property = null): ?Conversation
    {
        $propertyId = $property?->id;

        return Conversation::query()
            ->when(
                $propertyId,
                fn ($query) => $query->where('property_id', $propertyId),
                fn ($query) => $query->whereNull('property_id'),
            )
            ->where(function ($query) use ($first, $second) {
                $query->where(function ($query) use ($first, $second) {
                    $query->where('initiator_id', $first->id)->where('recipient_id', $second->id);
                })->orWhere(function ($query) use ($first, $second) {
                    $query->where('initiator_id', $second->id)->where('recipient_id', $first->id);
                });
            })
            ->first();
    }

    /**
     * @throws ValidationException
     */
    public function start(User $sender, User $recipient, string $body, ?Property $property = null): Conversation
    {
        if (! $this->canMessage($sender, $recipient, $property)) {
            throw ValidationException::withMessages([
                'recipient_id' => ['You are not allowed to message this user.'],
            ]);
        }

        return DB::transaction(function () use ($sender, $recipient, $body, $property) {
            $conversation = $this->findBetween($sender, $recipient, $property);

            if ($conversation === null) {
                $conversation = Conversation::create([
                    'property_id' => $property?->id,
                    'initiator_id' => $sender->id,
                    'recipient_id' => $recipient->id,
                    'last_message_at' => now(),
                ]);
            }

            $message = $conversation->messages()->create([
                'sender_id' => $sender->id,
                'body' => $body,
            ]);

            $conversation->update(['last_message_at' => now()]);

            broadcast(new MessageSent($message))->toOthers();

            return $conversation;
        });
    }

    public function resolveRecipient(User $sender, ?int $recipientId, ?int $propertyId): User
    {
        if ($propertyId !== null) {
            $property = Property::query()->published()->find($propertyId);

            if ($property === null) {
                throw ValidationException::withMessages([
                    'property_id' => ['The selected property is not available.'],
                ]);
            }

            return $property->owner;
        }

        if ($recipientId === null) {
            throw ValidationException::withMessages([
                'recipient_id' => ['Please choose who you want to message.'],
            ]);
        }

        $recipient = User::query()->find($recipientId);

        if ($recipient === null) {
            throw ValidationException::withMessages([
                'recipient_id' => ['The selected recipient could not be found.'],
            ]);
        }

        if ($sender->role === 'user' && ! in_array($recipient->role, ['owner', 'agent'], true)) {
            throw ValidationException::withMessages([
                'recipient_id' => ['Please select a property to message its owner or agent.'],
            ]);
        }

        return $recipient;
    }
}
