<?php

namespace dmitryrogolev\Service\Tests\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запустить миграцию.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->uuid('id');
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    /**
     * Откатить миграцию.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
