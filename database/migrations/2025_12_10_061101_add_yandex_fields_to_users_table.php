<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Добавляем поля для Яндекс OAuth
            $table->string('yandex_id')->nullable()->unique()->after('id');
            $table->string('yandex_login')->nullable()->after('phone');
            $table->json('yandex_data')->nullable()->after('yandex_login');
            
            // Делаем пароль nullable для пользователей, которые регистрируются через Яндекс
            $table->string('password')->nullable()->change();
            
            // Добавляем индекс для быстрого поиска по yandex_id
            $table->index('yandex_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Удаляем добавленные поля
            $table->dropColumn(['yandex_id', 'yandex_login', 'yandex_data']);
            
            // Удаляем индекс
            $table->dropIndex(['yandex_id']);
            
            // Возвращаем пароль обратно как NOT NULL (если нужно)
            // Внимание: это может вызвать ошибки, если есть пользователи с null паролем
            // $table->string('password')->nullable(false)->change();
        });
    }
};