<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arrUsers = [
            [
                'name' => 'Client',
                'email' => "client-backend-test@talentify.com",
                'type' => 'client',
                'password' => 'secret'
            ],
            [
                'name' => 'Admin',
                'email' => "admin-backend-test@talentify.com",
                'type' => 'admin',
                'password' => 'secret'
            ],
        ];

        foreach ($arrUsers as $arrayToInsert => $array) {
            DB::table('users')->insert($array);
        }
    }
}
