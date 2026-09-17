<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\User;
use App\Notifications\UserInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    /**
     * Display a listing of users for administrators.
     */
    public function index(Request $request)
    {
        $query = User::withCount('fields')->orderBy('name');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            if ($request->role === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->role === 'user') {
                $query->where('is_admin', false);
            }
        }

        $users = $query->paginate(20)->withQueryString();

        return view('fields.admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $fields = Field::orderBy('name')->get();
        return view('fields.admin.users.create', compact('fields'));
    }

    /**
     * Store a newly created user and dispatch an invitation/password-set email.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'is_admin' => 'nullable|boolean',
            'permission_level' => 'nullable|string|in:standard,admin',
            'fields' => 'nullable|array',
            'fields.*' => 'exists:fields,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(32)),
            'is_admin' => $request->boolean('is_admin'),
        ]);

        if (!empty($validated['fields'])) {
            $permission = $validated['permission_level'] ?? 'standard';
            $syncData = [];
            foreach ($validated['fields'] as $fieldId) {
                $syncData[$fieldId] = ['permission_level' => $permission];
            }
            $user->fields()->sync($syncData);
        }

        // Generate password set token and send invitation
        $token = Password::broker()->createToken($user);
        $user->notify(new UserInvitationNotification($token, $user->email, true));
        $activationLink = route('password.reset', ['token' => $token, 'email' => $user->email]);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' created successfully! An invitation email has been sent.")
            ->with('activation_link', $activationLink);
    }

    /**
     * Show the form for editing an existing user.
     */
    public function edit(User $user)
    {
        $fields = Field::orderBy('name')->get();
        $userFieldIds = $user->fields->pluck('id')->toArray();
        $userPermission = $user->fields->first()?->pivot?->permission_level ?? 'standard';

        return view('fields.admin.users.edit', compact('user', 'fields', 'userFieldIds', 'userPermission'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'is_admin' => 'nullable|boolean',
            'permission_level' => 'nullable|string|in:standard,admin',
            'fields' => 'nullable|array',
            'fields.*' => 'exists:fields,id',
        ]);

        // Prevent admin from removing their own admin status if they are the logged in user
        $isAdmin = (Auth::id() === $user->id) ? $user->is_admin : $request->boolean('is_admin');

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_admin' => $isAdmin,
        ]);

        $permission = $validated['permission_level'] ?? 'standard';
        $syncData = [];
        if (!empty($validated['fields'])) {
            foreach ($validated['fields'] as $fieldId) {
                $syncData[$fieldId] = ['permission_level' => $permission];
            }
        }
        $user->fields()->sync($syncData);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $userName = $user->name;
        $user->fields()->detach();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$userName}' has been deleted.");
    }

    /**
     * Trigger and send a password reset / activation link to an existing user.
     */
    public function sendReset(User $user)
    {
        $token = Password::broker()->createToken($user);
        $user->notify(new UserInvitationNotification($token, $user->email, false));
        $activationLink = route('password.reset', ['token' => $token, 'email' => $user->email]);

        return redirect()->route('admin.users.index')
            ->with('success', "Password reset link sent to {$user->email}.")
            ->with('activation_link', $activationLink);
    }
}
