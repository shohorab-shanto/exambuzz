<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('details')->nullable();
            $table->text('permission');
            $table->integer('amount');
            $table->integer('validity');
            $table->tinyInteger('status', 0, 1);
            $table->string('image')->nullable();

            $table->string('banner_image')->nullable(); // New column for banner image
            $table->date('published_at')->nullable(); // New column for published date
            $table->integer('discount_amount')->default(0); // New column for discount amount

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
