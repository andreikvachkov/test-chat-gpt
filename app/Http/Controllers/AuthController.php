<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $d = $r->validate(['email' => 'required|email|max:255', 'password' => 'required|string', 'remember' => 'boolean']);
        if (! Auth::guard('web')->attempt(['email' => Str::lower($d['email']), 'password' => $d['password']], $r->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Неверная почта или пароль.']);
        }$r->session()->regenerate();

        return redirect()->intended('/account');
    }

    public function register(Request $r)
    {
        $r->merge(['email' => Str::lower((string) $r->email)]);
        $d = $r->validate(['name' => 'required|string|max:120', 'email' => 'required|email|max:255|unique:users', 'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()], 'consent' => 'accepted']);
        $user = User::create(collect($d)->only('name', 'email', 'password')->all());
        Auth::guard('web')->login($user);
        $r->session()->regenerate();

        return redirect()->intended('/account');
    }

    public function logout(Request $r)
    {
        Auth::guard('web')->logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect('/');
    }

    public function forgot(Request $r)
    {
        $r->validate(['email' => 'required|email']);
        Password::broker('users')->sendResetLink(['email' => Str::lower($r->email)]);

        return back()->with('status', 'Если такая почта зарегистрирована, мы отправили ссылку для восстановления пароля.');
    }

    public function reset(Request $r)
    {
        $r->validate(['token' => 'required', 'email' => 'required|email', 'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()]]);
        $status = Password::broker('users')->reset($r->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect('/login')->with('status', 'Пароль изменён. Войдите с новым паролем.');
    }
}
