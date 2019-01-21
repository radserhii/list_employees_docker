<?php

use App\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PositionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $departmentsIDs = $this->getDepartmentsIDs();

        foreach ($departmentsIDs as $departmentID) {
            DB::table('positions')->insert([
                'name' => $faker->word,
                'department_id' => $departmentID,
                'salary' => $faker->randomFloat(2, 5000, 100000),
            ]);
        }
    }

    private function getDepartmentsIDs()
    {
        return Department::take(10)->pluck('id')->toArray();

    }
}
