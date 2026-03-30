<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Loan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'            => 'Admin LKS',
                'email'           => 'admin@lks.test',
                'password'        => 'password', // ← cukup plain text
                'role'            => 'admin',
                'monthly_revenue' => 100_000_000,
            ],
            [
                'name'            => 'Analyst LKS',
                'email'           => 'analyst@lks.test',
                'password'        => 'password', // ← cukup plain text
                'role'            => 'analyst',
                'monthly_revenue' => 100_000_000,
            ],
            [
                'name'            => 'Borrower LKS',
                'email'           => 'borrower@lks.test',
                'password'        => 'password', // ← cukup plain text
                'role'            => 'borrower',
                'business_name'   => 'UD Maju Jaya',
                'monthly_revenue' => 50_000_000,
            ],
        ];

        foreach ($users as $data) {
            User::create($data);
        }

        $borrower = User::where('role', 'borrower')->first();
        Loan::factory(10)->create([
            'user_id'    => $borrower->id,
            'created_by' => $borrower->id,
        ]);
    }
}
