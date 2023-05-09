<?php

namespace Database\Seeders;

use App\Imports\RoomImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Excel::import(new RoomImport(), 'database/seeders/data/rooms.csv');
    }
}
