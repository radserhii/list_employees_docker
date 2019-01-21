<?php

use App\Position;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->addUsers();
        $this->addPositionToUser();
    }

    private function addUsers()
    {
        $faker = Faker::create();

        foreach (range(0, 10) as $item) {
            DB::table('users')->insert([
                'name' => $faker->name,
                'email' => $faker->email,
                'password' => bcrypt($faker->password),
            ]);
        }
    }

    private function addPositionToUser()
    {
        $users = User::all();
        $positionIDs = $this->getPositionsIDs();

        foreach ($users as $user) {
            $user->positions()->attach($positionIDs[array_rand($positionIDs)]);
        }
    }

    private function getPositionsIDs()
    {
        return Position::take(20)->pluck('id')->toArray();
    }
}
