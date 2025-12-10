<?php

namespace App\Socialite;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class YandexProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopes = ['login:email', 'login:info', 'login:avatar'];
    
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://oauth.yandex.ru/authorize', $state);
    }
    
    protected function getTokenUrl()
    {
        return 'https://oauth.yandex.ru/token';
    }
    
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://login.yandex.ru/info', [
            'headers' => [
                'Authorization' => 'OAuth ' . $token,
            ],
        ]);
        
        return json_decode($response->getBody(), true);
    }
    
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => $user['login'],
            'name' => $user['display_name'] ?? $user['real_name'] ?? $user['first_name'] ?? null,
            'email' => $user['default_email'],
            'avatar' => !empty($user['default_avatar_id']) 
                ? 'https://avatars.yandex.net/get-yapic/' . $user['default_avatar_id'] . '/islands-200'
                : null,
        ]);
    }
    
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }
}