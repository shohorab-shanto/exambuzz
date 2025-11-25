<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void {
        Admin::updateOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name'     => 'Demo Admin',
                'phone'    => '01700000000',
                'password' => Hash::make('password'),
                'address'  => 'Demo Address',
                'image'    => null,
                'status'   => 1,
            ]
        );

        User::updateOrCreate([
            'email' => 'user@demo.com',
        ], [
            'name'     => 'Demo User',
            'phone'    => '01800000000',
            'password' => Hash::make('password'),
            'address'  => 'Demo Address',
            'image'    => null,
        ]);

        $packages = [
            [
                'name'            => 'Starter Package',
                'details'         => 'Access to core MCQ exams and study materials.',
                'permission'      => ['mcq' => true, 'written' => false, 'materials' => true],
                'amount'          => 499,
                'validity'        => 30,
                'status'          => 1,
                'type'            => 1,
                'image'           => null,
                'banner_image'    => null,
                'published_at'    => now()->subDays(7),
                'discount_amount' => 0,
            ],
            [
                'name'            => 'Pro Package',
                'details'         => 'Includes all exams, written answers, and revision tools.',
                'permission'      => ['mcq' => true, 'written' => true, 'revision' => true],
                'amount'          => 999,
                'validity'        => 90,
                'status'          => 1,
                'type'            => 1,
                'image'           => null,
                'banner_image'    => null,
                'published_at'    => now()->subDays(3),
                'discount_amount' => 150,
            ],
            [
                'name'            => 'Ultimate Package',
                'details'         => 'All Pro features plus teacher review support and premium materials.',
                'permission'      => ['mcq' => true, 'written' => true, 'revision' => true, 'teacher_review' => true],
                'amount'          => 1499,
                'validity'        => 180,
                'status'          => 1,
                'type'            => 2,
                'image'           => null,
                'banner_image'    => null,
                'published_at'    => now(),
                'discount_amount' => 250,
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(
                ['name' => $package['name']],
                $package
            );
        }

        $this->call([
            SubjectTopicSeeder::class,
        ]);
    }
}
