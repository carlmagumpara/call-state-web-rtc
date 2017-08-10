<?php

use Illuminate\Database\Seeder;

use App\Admin;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new Admin();
        $user->name = "Carl Anthony Magumpara";
        $user->email = "magumparacarlanthony@gmail.com";
        $user->password = crypt("09071995","");
        $user->save();
    }
}
