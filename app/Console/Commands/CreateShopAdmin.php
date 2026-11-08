<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateShopAdmin extends Command
{
    protected $signature = 'shop:admin {email}';

    protected $description = 'Создать администратора без стандартного пароля';

    public function handle(): int
    {
        $email = strtolower($this->argument('email'));
        if (User::where('email', $email)->exists()) {
            $this->error('Пользователь уже существует. Права автоматически не изменяются.');

            return self::FAILURE;
        }
        $name = $this->ask('Имя администратора');
        $password = $this->secret('Пароль (не менее 12 символов, буквы и цифры)');
        $v = Validator::make(compact('name', 'email', 'password'), ['name' => 'required|max:120', 'email' => 'required|email', 'password' => ['required', Password::min(12)->letters()->numbers()]]);
        if ($v->fails()) {
            $this->error($v->errors()->first());

            return self::FAILURE;
        }
        $user = User::create(compact('name', 'email', 'password'));
        $user->forceFill(['is_admin' => true])->save();
        $this->info('Администратор создан. Вход: /admin');

        return self::SUCCESS;
    }
}
