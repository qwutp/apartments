<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'patronymic',
        'passport_series',
        'passport_number',
        'phone',
        'role',
        // Добавляем поля для Яндекс OAuth
        'yandex_id',
        'yandex_login',
        'yandex_data',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'yandex_data', // скрываем сырые данные Яндекс
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'yandex_data' => 'array', // автоматическая конвертация JSON в массив
        ];
    }

    /**
     * Get the apartments owned by the user.
     */
    public function apartments()
    {
        return $this->hasMany(Apartment::class, 'owner_id');
    }

    /**
     * Get the bookings made by the user.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the reviews written by the user.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the favorites of the user.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the notifications sent to the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a regular user.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if the user registered via Yandex OAuth.
     */
    public function isYandexUser(): bool
    {
        return !is_null($this->yandex_id);
    }

    /**
     * Get the user's full name with patronymic.
     */
    public function getFullNameAttribute(): string
    {
        $parts = [];
        
        if ($this->first_name) {
            $parts[] = $this->first_name;
        }
        
        if ($this->last_name) {
            $parts[] = $this->last_name;
        }
        
        if ($this->patronymic) {
            $parts[] = $this->patronymic;
        }
        
        return !empty($parts) ? implode(' ', $parts) : $this->name;
    }

    /**
     * Get the user's short name (first name + last name initial).
     */
    public function getShortNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . mb_substr($this->last_name, 0, 1) . '.';
        }
        
        return $this->name;
    }

    /**
     * Get passport data in formatted string.
     */
    public function getFormattedPassportAttribute(): ?string
    {
        if ($this->passport_series && $this->passport_number) {
            return $this->passport_series . ' ' . $this->passport_number;
        }
        
        return null;
    }

    /**
     * Check if user profile is complete for booking.
     */
    public function isProfileCompleteForBooking(): bool
    {
        return !empty($this->first_name) && 
               !empty($this->last_name) && 
               !empty($this->phone) && 
               !empty($this->email);
    }

    /**
     * Merge Yandex data with existing user data.
     */
    public function mergeYandexData(array $yandexData): void
    {
        // Основные поля из Яндекс
        $this->first_name = $yandexData['first_name'] ?? $this->first_name;
        $this->last_name = $yandexData['last_name'] ?? $this->last_name;
        $this->email = $yandexData['default_email'] ?? $yandexData['email'] ?? $this->email;
        $this->phone = $yandexData['default_phone'] ?? $yandexData['phone'] ?? $this->phone;
        $this->yandex_login = $yandexData['login'] ?? $this->yandex_login;
        $this->yandex_data = $yandexData;
        
        // Формируем name, если его нет
        if (empty($this->name) && ($this->first_name || $this->last_name)) {
            $this->name = trim($this->first_name . ' ' . $this->last_name);
        }
        
        // Верифицируем email, если пришел из Яндекс
        if (!empty($yandexData['default_email']) && !$this->email_verified_at) {
            $this->email_verified_at = now();
        }
        
        $this->save();
    }

    /**
     * Get Yandex avatar URL if available.
     */
    public function getYandexAvatarAttribute(): ?string
    {
        return $this->yandex_data['default_avatar_id'] 
            ? 'https://avatars.yandex.net/get-yapic/' . $this->yandex_data['default_avatar_id'] . '/islands-200'
            : null;
    }

    /**
     * Scope for Yandex users.
     */
    public function scopeYandexUsers($query)
    {
        return $query->whereNotNull('yandex_id');
    }

    /**
     * Scope for regular (non-Yandex) users.
     */
    public function scopeRegularUsers($query)
    {
        return $query->whereNull('yandex_id');
    }

    /**
     * Get authentication method.
     */
    public function getAuthMethodAttribute(): string
    {
        return $this->isYandexUser() ? 'yandex' : 'email';
    }
}