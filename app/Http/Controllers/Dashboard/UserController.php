<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(20);
        return view('dashboard.users.index', compact('users'));
    }

    public function toggleAdmin(User $user)
    {
        // Prevent admins from revoking their own admin status
        if ($user->id === auth()->id()) {
            return redirect()->route('dashboard.users.index')
                ->with('error', 'You cannot modify your own admin status.');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        return redirect()->route('dashboard.users.index')
            ->with('success', "User {$user->name} admin status updated!");
    }

    public function assignAllGamepacks(User $user)
    {
        // Double-check: only admins may call this
        if (!auth()->user()->is_admin) {
            abort(403);
        }

        // Fetch IDs of gamepacks the user already owns
        $ownedIds = $user->gamepacks()->pluck('gamepack_id')->toArray();

        // Get all gamepacks the user does NOT yet have
        $missing = Gamepack::whereNotIn('id', $ownedIds)->get();

        if ($missing->isEmpty()) {
            return redirect()->route('dashboard.users.index')
                ->with('success', "{$user->name} already owns all gamepacks.");
        }

        $now  = now();
        $rows = $missing->map(fn($pack) => [
            'gamepack_id' => $pack->id,
            'user_id'     => $user->id,
            'opened'      => 0,
            'created_at'  => $now,
            'updated_at'  => $now,
        ])->toArray();

        \DB::table('gamepack_purchased')->insert($rows);

        return redirect()->route('dashboard.users.index')
            ->with('success', "Assigned {$missing->count()} gamepack(s) to {$user->name}.");
    }
}
