<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->ask('Enter your name');
        $surname = $this->ask('Enter your surname');
        $username = $this->ask('Enter username');
        $nickname = $this->ask('Enter nickname');
        $email = $this->ask('Enter email');
        $password = $this->secret('Enter password');
        $password_confirmation = $this->secret('Enter password again');

        if ($password != $password_confirmation) {
            $this->error('Passwords do not match');
            return;
        }

        $user = new User();
        $user->name = $name;
        $user->surname = $surname;
        $user->username = $username;
        $user->nickname = $nickname;
        $user->email = $email;
        $user->password = Hash::make($password);

        if($user::count() == 0){
            $user->role = 'owner';
        }
        else{
            $user->role = 'admin';
        }
        $user->save();

        $this->info('User created successfully');

    }
}
