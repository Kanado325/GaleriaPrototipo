<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = base_path("database/data/tb_usuario.csv");

        if (!file_exists($filePath)) {
            $this->command->error("O ficheiro tb_usuario.csv não foi encontrado em: {$filePath}");
            return;
        }

        $csvFile = fopen($filePath, "r");
        $firstline = true;

        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if (!$firstline) {
                User::updateOrCreate(
                    ['id' => $data[0]],
                    [
                        'name'     => $data[1],
                        'username' => $data[2],
                        'email'    => $data[3],
                        'password' => Hash::make($data[4]),
                        'profile_img'   => $data[5],
                        'role'     => $data[6],
                        'created_at' => $data[7] ?? now(),
                        'updated_at' => $data[8] ?? now(),
                    ]
                );
            }
            $firstline = false;
        }

        fclose($csvFile);
    }
}