<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class YandexController extends Controller
{
    /**
     * Перенаправление на Яндекс для авторизации
     */
    public function redirectToYandex()
    {
        return Socialite::driver('yandex')->redirect();
    }

    /**
     * Обработка callback от Яндекс
     */
    public function handleYandexCallback(Request $request)
    {
        try {
            // Получаем данные пользователя от Яндекс
            $yandexUser = Socialite::driver('yandex')->user();
            $yandexData = $yandexUser->user;
            
            // Ищем пользователя по Яндекс ID
            $user = User::where('yandex_id', $yandexUser->getId())->first();
            
            if (!$user) {
                // Ищем по email (если пользователь уже регистрировался)
                $user = User::where('email', $yandexUser->getEmail())->first();
                
                if ($user) {
                    // Обновляем существующего пользователя
                    $user->update([
                        'yandex_id' => $yandexUser->getId(),
                        'yandex_login' => $yandexData['login'] ?? null,
                        'yandex_data' => $yandexData,
                    ]);
                } else {
                    // Создаем нового пользователя
                    $user = User::create([
                        'yandex_id' => $yandexUser->getId(),
                        'name' => $yandexUser->getName(),
                        'first_name' => $yandexData['first_name'] ?? null,
                        'last_name' => $yandexData['last_name'] ?? null,
                        'email' => $yandexUser->getEmail(),
                        'phone' => $yandexData['default_phone'] ?? $yandexData['phone'] ?? null,
                        'yandex_login' => $yandexData['login'] ?? null,
                        'yandex_data' => $yandexData,
                        'password' => null, // Пароль не нужен для OAuth
                        'role' => 'user',
                    ]);
                }
            }
            
            // Авторизуем пользователя
            Auth::login($user, true);
            $request->session()->regenerate();
            $request->session()->put('_last_activity', now()->timestamp);
            
            // Перенаправляем на главную
            return redirect()->intended('/?auth=yandex');
            
        } catch (\Exception $e) {
            // Логируем ошибку
            \Log::error('Yandex OAuth Error: ' . $e->getMessage());
            
            // Возвращаем с ошибкой на страницу логина
            return redirect('/login')->withErrors([
                'yandex' => 'Ошибка авторизации через Яндекс. Попробуйте еще раз.'
            ]);
        }
    }
}