<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::with('role')->orderBy('created_at', 'desc')->get();
        $roles = Role::orderBy('level', 'desc')->get();
        
        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::orderBy('level', 'desc')->get();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Benutzer wurde erfolgreich erstellt.');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $roles = Role::orderBy('level', 'desc')->get();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];
        $user->is_active = $request->has('is_active');

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Benutzer wurde erfolgreich aktualisiert.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Sie können sich nicht selbst löschen.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Benutzer wurde erfolgreich gelöscht.');
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(User $user)
    {
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Sie können sich nicht selbst deaktivieren.'
            ], 422);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'is_active' => $user->is_active,
            'message' => $user->is_active ? 'Benutzer aktiviert.' : 'Benutzer deaktiviert.'
        ]);
    }
}

