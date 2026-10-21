<?php

namespace Database\Factories;

use App\Models\Gamepack;
use App\Models\GamepackPurchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GamepackPurchase>
 */
class GamepackPurchaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'gamepack_id' => Gamepack::factory(),
            'user_id' => User::factory(),
            'opened' => false,
            'access_through_admin_grant' => false,
        ];
    }
}
