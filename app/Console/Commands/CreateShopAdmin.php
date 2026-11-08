<?php

namespace App\Console\Commands;

use App\Models\AdminRole;
use App\Models\AdminUser;
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
        if (AdminUser::where('email', $email)->exists()) {
            $this->error('Сотрудник с такой почтой уже существует. Права автоматически не изменяются.');

            return self::FAILURE;
        }
        $name = $this->ask('Имя администратора');
        $password = $this->secret('Пароль (не менее 12 символов, буквы и цифры)');
        $v = Validator::make(compact('name', 'email', 'password'), ['name' => 'required|max:120', 'email' => 'required|email', 'password' => ['required', Password::min(12)->letters()->numbers()]]);
        if ($v->fails()) {
            $this->error($v->errors()->first());

            return self::FAILURE;
        }
        AdminUser::create(compact('name', 'email', 'password') + ['admin_role_id' => AdminRole::where('code', 'owner')->firstOrFail()->id, 'active' => true]);
        $this->info('Отдельная учётная запись владельца создана. Вход: /admin. Покупательские аккаунты не изменены.');

        return self::SUCCESS;
    }
}
