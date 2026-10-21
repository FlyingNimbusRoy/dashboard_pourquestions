<?php

namespace App\Services\Crud;

use App\Models\Gamepack;
use App\Models\GamepackPurchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function toggleAdmin(User $user, User $actor): void
    {
        if ($user->is($actor)) {
            throw ValidationException::withMessages([
                'user' => 'You cannot modify your own admin status.',
            ]);
        }

        $user->is_admin = ! $user->is_admin;
        $user->save();
    }

    /**
     * Assigns every gamepack the user does not yet own, tagged as admin-granted.
     * Uses Eloquent creates so each row is tracked by RecordsLiveChanges.
     *
     * @return int number of gamepacks assigned (0 if already complete)
     */
    public function assignAllGamepacks(User $user): int
    {
        $ownedIds = $user->gamepacks()->pluck('gamepack_id')->toArray();
        $missing = Gamepack::whereNotIn('id', $ownedIds)->get();

        if ($missing->isEmpty()) {
            return 0;
        }

        DB::transaction(function () use ($user, $missing) {
            foreach ($missing as $pack) {
                GamepackPurchase::create([
                    'gamepack_id'                => $pack->id,
                    'user_id'                    => $user->id,
                    'opened'                     => false,
                    'access_through_admin_grant' => true,
                ]);
            }
        });

        return $missing->count();
    }
}
