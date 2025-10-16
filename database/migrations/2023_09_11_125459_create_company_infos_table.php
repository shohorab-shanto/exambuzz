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
        Schema::create('company_infos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('about');
            $table->string('address')->nullable();
            $table->string('phone_one');
            $table->string('phone_two')->nullable();
            $table->string('phone_three')->nullable();
            $table->string('app_link')->nullable();
            $table->string('email');
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('pinterest')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('app_logo')->nullable();

            $table->string('facebook_group')->nullable();
            $table->string('telegram_group')->nullable();
            $table->boolean('bkash_payment_enable')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_infos');
    }
};
