<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\UserRequestedVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class RoleRequestController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role'        => ['required', 'in:owner,agent'],
            'national_id' => ['required', 'numeric', 'digits:12'],
            'tin'         => ['required_if:role,agent', 'nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();

        $user->update([
            'role'        => $validated['role'],
            'national_id' => $validated['national_id'],
            'tin'         => $validated['role'] === 'agent' ? $validated['tin'] : null,
            'status'      => 'pending',
        ]);
        Notification::send(
            User::where('role', 'admin')->get(),
            new UserRequestedVerification($request->user())
        );
        return redirect()->route($validated['role'] . '.dashboard')
            ->with('success', 'Your request has been submitted and is awaiting approval.');
    }
}
