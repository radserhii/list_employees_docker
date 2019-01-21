<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentTableSeeder extends Seeder
{
    private const departments = ['HR', 'Sales', 'Security', 'Developer'];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::departments as $department) {
            DB::table('departments')->insert([
                'name' => $department,
            ]);
        }
    }
}
