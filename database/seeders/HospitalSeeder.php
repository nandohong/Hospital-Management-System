<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            DB::table('doctors')->updateOrInsert(
                ['name' => "Dokter Umum $i"],
                [
                    'specialization' => 'Dokter Umum',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach (['I' => 10, 'II' => 10, 'III' => 20] as $class => $total) {
            for ($i = 1; $i <= $total; $i++) {
                $number = 'K' . $class . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);

                DB::table('inpatient_rooms')->updateOrInsert(
                    ['room_number' => $number],
                    [
                        'room_class' => $class,
                        'status' => 'available',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        foreach (['OK-01', 'OK-02'] as $room) {
            DB::table('operating_rooms')->updateOrInsert(
                ['room_name' => $room],
                [
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}