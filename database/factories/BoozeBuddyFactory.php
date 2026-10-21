<?php

namespace Database\Factories;

use App\Enums\BuddyStatus;
use App\Models\BoozeBuddy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoozeBuddy>
 */
class BoozeBuddyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'status' => BuddyStatus::Pending,
        ];
    }
}
