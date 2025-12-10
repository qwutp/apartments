<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class YandexController extends Controller
{
    public function redirectToYandex()
    {
        return Socialite::driver('yandex')->redirect();
    }

    public function handleYandexCallback()
    {
        try {
            $yandexUser = Socialite::driver('yandex')->user();
            
            // Ищем пользователя по yandex_id
            $user = User::where('yandex_id', $yandexUser->getId())->first();
            
            if (!$user) {
                // Ищем по email (если пользователь уже регистрировался обычным способом)
                $user = User::where('email', $yandexUser->getEmail())->first();
                
                if (!$user) {
                    // Создаем нового пользователя
                    $user = User::create([
                        'name' => $yandexUser->getName(),
                        'email' => $yandexUser->getEmail(),
                        'yandex_id' => $yandexUser->getId(),
                        'avatar' => $yandexUser->getAvatar(),
                        'password' => null, // Пароль не нужен для OAuth
                    ]);
                } else {
                    // Обновляем существующего пользователя с Яндекс ID
                    $user->update([
                        'yandex_id' => $yandexUser->getId(),
                        'avatar' => $yandexUser->getAvatar(),
                    ]);
                }
            }
            
            // Авторизуем пользователя
            Auth::login($user, true);
            
            return redirect()->intended('/dashboard');
            
        } catch (\Exception $e) {
            \Log::error('Yandex OAuth Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'msg' => 'Ошибка авторизации через Яндекс. Попробуйте еще раз.'
            ]);
        }
    }
}