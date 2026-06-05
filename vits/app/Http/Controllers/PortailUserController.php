<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class PortailUserController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => $validated['password'],
            'client_id' => $client->id,
            'is_admin'  => false,
        ]);

        $user->assignRole('client');

        return back()->with('success', 'Accès portail créé pour '.$user->email);
    }

    public function destroy(Client $client, User $user)
    {
        abort_unless($user->client_id === $client->id, 403);
        $user->delete();
        return back()->with('success', 'Accès portail supprimé.');
    }
}
