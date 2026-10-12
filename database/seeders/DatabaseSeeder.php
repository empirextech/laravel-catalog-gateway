<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Gateway Operator',
            'email' => 'operator@example.com',
        ]);

        Product::factory()->count(8)->create();

        $this->command?->info('Sanctum token: ' . $user->createToken('demo')->plainTextToken);
    }
}
