<?php

namespace Database\Factories;

use App\Models\AdminRole;
use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AdminUserFactory extends Factory
{
    protected $model = AdminUser::class;

    public function definition(): array
    {
        return ['name' => fake()->name(), 'email' => fake()->unique()->safeEmail(), 'password' => Hash::make('AdminPassword123'), 'active' => true, 'admin_role_id' => AdminRole::where('code', 'owner')->firstOrFail()->id];
    }
}
