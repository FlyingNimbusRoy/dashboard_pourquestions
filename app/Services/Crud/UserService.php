<?php

namespace App\Services\Crud;

use App\Models\Gamepack;
use App\Models\GamepackPurchase;
use App\Models\InternalNote;
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

    /**
     * Revoke an admin-granted gamepack. Only works on purchases with access_through_admin_grant = true.
     * Logs the reason as an InternalNote before deleting.
     */
    public function revokeGamepack(GamepackPurchase $purchase, string $reason, ?int $revokedBy = null): void
    {
        if (! $purchase->access_through_admin_grant) {
            throw ValidationException::withMessages(['purchase' => 'Only admin-granted packs can be revoked through this action.']);
        }

        DB::transaction(function () use ($purchase, $reason, $revokedBy) {
            InternalNote::create([
                'note_type'  => 'gamepack_purchase',
                'note_id'    => $purchase->id,
                'note'       => "[REVOKED] {$reason}",
                'created_by' => $revokedBy,
            ]);
            $purchase->delete();
        });
    }

    /**
     * Grant a single gamepack to a user (staff grant). No-op if already owned.
     * Stores a note on the purchase for audit trail.
     */
    public function grantGamepack(User $user, Gamepack $gamepack, string $reason, ?int $grantedBy = null): bool
    {
        $alreadyOwned = GamepackPurchase::where('user_id', $user->id)
            ->where('gamepack_id', $gamepack->id)
            ->exists();

        if ($alreadyOwned) {
            return false;
        }

        DB::transaction(function () use ($user, $gamepack, $reason, $grantedBy) {
            $purchase = GamepackPurchase::create([
                'gamepack_id'                => $gamepack->id,
                'user_id'                    => $user->id,
                'opened'                     => false,
                'access_through_admin_grant' => true,
            ]);

            InternalNote::create([
                'note_type'  => 'gamepack_purchase',
                'note_id'    => $purchase->id,
                'note'       => $reason,
                'created_by' => $grantedBy,
            ]);
        });

        return true;
    }
}
