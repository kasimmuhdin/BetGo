<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\UserVerificationReviewed;
use Illuminate\Http\Request;

class UserVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['owner', 'agent', 'users']);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $requests = $query->oldest('updated_at')->paginate(10)->withQueryString();

        $stats = [
            'all' => User::count(),
            'pending_count' => User::where('status', 'pending')->whereIn('role', ['owner', 'agent'])->count(),
            'approved' => User::where('status', 'approved')->count(),
            'rejected' => User::where('status', 'rejected')->count(),
            'owners_pending' => User::where('status', 'pending')->where('role', 'owner')->count(),
            'agents_pending' => User::where('status', 'pending')->where('role', 'agent')->count(),
        ];

        return view('admin.users.index', compact('requests', 'stats'));
    }
    public function pending(Request $request)
    {
        $query = User::where('status', 'pending')->whereIn('role', ['owner', 'agent']);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $requests = $query->oldest('updated_at')->paginate(10)->withQueryString();

        $stats = [
            'pending_count' => User::where('status', 'pending')->whereIn('role', ['owner', 'agent'])->count(),
            'approved_today' => User::where('status', 'active')->whereDate('verified_at', today())->count(),
            'rejected_today' => User::where('status', 'rejected')->whereDate('verified_at', today())->count(),
            'owners_pending' => User::where('status', 'pending')->where('role', 'owner')->count(),
            'agents_pending' => User::where('status', 'pending')->where('role', 'agent')->count(),
        ];

        return view('admin.users.pending', compact('requests', 'stats'));
    }

    public function show(User $user)
    {
        abort_unless(in_array($user->role, ['owner', 'agent']), 404);

        $user->load('verifier');

        return view('admin.users.show', compact('user'));
    }

    public function approve(Request $request, User $user)
    {
        abort_unless($user->status === 'pending', 422, 'Only pending requests can be approved.');

        $user->update([
            'status' => 'approved',
            'verification_rejection_reason' => null,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $user->notify(new UserVerificationReviewed($user));

        return redirect()
            ->route('admin.users.all')
            ->with('success', "{$user->name} has been approved as " . ucfirst($user->role) . '.');
    }

    public function reject(Request $request, User $user)
    {
        abort_unless($user->status === 'pending', 422, 'Only pending requests can be rejected.');

        $validated = $request->validate([
            'verification_rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'verification_rejection_reason.required' => 'Please explain why this request is being rejected.',
            'verification_rejection_reason.min' => 'Give a bit more detail (at least 10 characters).',
        ]);

        $user->update([
            'status' => 'rejected',
            'verification_rejection_reason' => $validated['verification_rejection_reason'],
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        $user->notify(new UserVerificationReviewed($user));

        return redirect()
            ->route('admin.users.all')
            ->with('success', "{$user->name}'s request has been rejected.");
    }
}
