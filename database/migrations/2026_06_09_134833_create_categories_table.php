<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم المجموعة');
            $table->string('name_en')->nullable()->comment('اسم المجموعة بالإنجليزية');
            $table->string('code')->unique()->nullable()->comment('كود المجموعة');
            $table->text('description')->nullable()->comment('الوصف');
            $table->string('image')->nullable()->comment('الصورة');
            $table->boolean('is_active')->default(true)->comment('نشط');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};