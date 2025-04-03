<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransSubserieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Comando
        // php artisan db:seed --class=TransSubserieSeeder

        $subserieResults = DB::table('anterior')->select('subserie')->whereNotNull('subserie')->distinct();
        $subserieResults->union(
            DB::table('anterior_2')->select('subserie')->whereNotNull('subserie')->distinct()
        );
        $subserieResults->union(
            DB::table('nuevo')->select('subserie')->whereNotNull('subserie')->distinct()
        );
        $subserieResults->union(
            DB::table('nuevo_2')->select('subserie')->whereNotNull('subserie')->distinct()
        );
        $subserieResults->union(
            DB::table('sia')->select('serie AS subserie')->whereNotNull('serie')->distinct()
        );
        $subserieResults->union(
            DB::table('sis2018')->select('subserie')->whereNotNull('subserie')->distinct()
        );
        $subserieResults->union(
            DB::table('sis2018_2')->select('subserie')->whereNotNull('subserie')->distinct()
        );
        $subserieResults = $subserieResults->orderByDesc('subserie')->get();

        foreach ($subserieResults as $key => $value) {
            DB::table('sub_series')->insert(['nombre'=>$value->subserie]);
        }
    }
}
