<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use App\Services\Messaging\ConversationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PropertyContactController extends Controller
{
    public function __construct(private ConversationService $conversations) {}

    public function ajaxRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'status' => 'active',
        ]);

        Auth::login($user);

        return response()->json(['success' => true]);
    }

    public function sendMessage(Request $request, Property $property)
    {
        $request->validate(['body' => 'required|string|max:1000']);

        $this->conversations->start(
            $request->user(),
            $property->owner,
            $request->string('body')->toString(),
            $property,
        );

        return response()->json(['success' => true]);
    }
}
