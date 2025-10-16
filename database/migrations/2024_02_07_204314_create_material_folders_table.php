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
        Schema::create('material_folders', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            // parent_id for subfolders unsinged bigInteger
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('status')->default('1')->nullable()->comment('1 = Active | 0 = InActive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_folders');
    }
};
