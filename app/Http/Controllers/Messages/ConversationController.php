<?php

namespace App\Http\Controllers\Messages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Messages\StoreConversationRequest;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\User;
use App\Services\Messaging\ConversationService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(private ConversationService $conversations) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::with(['initiator', 'recipient', 'property', 'latestMessage'])
            ->forUser($user->id)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'property' => $c->property?->only(['id', 'title']),
                'other_user' => $c->otherUser($user->id)->only(['id', 'name']),
                'last_message' => $c->latestMessage?->body,
                'last_at' => $c->last_message_at,
                'unread_count' => $c->unreadCountFor($user->id),
            ]);

        return view('messages.index', compact('conversations'));
    }

    public function compose(Request $request)
    {
        $user = $request->user();
        $admin = User::query()->where('role', 'admin')->orderBy('id')->first();

        $mode = match ($user->role) {
            'admin' => 'admin',
            'owner', 'agent' => 'support',
            default => 'property',
        };

        $recipients = $mode === 'admin'
            ? User::query()
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role'])
            : collect();

        $properties = $mode === 'property'
            ? Property::query()->published()->with('owner:id,name')->orderBy('title')->get(['id', 'title', 'user_id'])
            : collect();

        $preselectedRecipientId = $request->integer('recipient') ?: null;

        return view('messages.compose', compact('mode', 'recipients', 'properties', 'admin', 'preselectedRecipientId'));
    }

    public function store(StoreConversationRequest $request)
    {
        $sender = $request->user();
        $property = $request->filled('property_id')
            ? Property::query()->find($request->integer('property_id'))
            : null;

        $recipient = $this->conversations->resolveRecipient(
            $sender,
            $request->filled('recipient_id') ? $request->integer('recipient_id') : null,
            $property?->id,
        );

        $conversation = $this->conversations->start(
            $sender,
            $recipient,
            $request->string('body')->toString(),
            $property,
        );

        return redirect()->route('messages.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation)
    {
        abort_unless(
            in_array($request->user()->id, [$conversation->initiator_id, $conversation->recipient_id]),
            403
        );

        $conversation->load(['messages.sender', 'property', 'initiator', 'recipient', 'messages.replyTo']);

        $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.show', compact('conversation'));
    }

    public function start(Request $request, Property $property)
    {
        $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $recipient = $property->owner;
        abort_if($recipient->id === $request->user()->id, 422, "You can't message yourself.");

        $conversation = $this->conversations->start(
            $request->user(),
            $recipient,
            $request->string('body')->toString(),
            $property,
        );

        return redirect()->route('messages.show', $conversation);
    }
}
