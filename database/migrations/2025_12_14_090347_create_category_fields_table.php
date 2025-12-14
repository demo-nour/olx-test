<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_fields', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->string('external_id');
            $table->string('attribute')->nullable();
            $table->string('name');
            $table->string('value_type')->nullable();
            $table->string('filter_type')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('min_value')->nullable();
            $table->integer('max_value')->nullable();
            $table->integer('min_length')->nullable();
            $table->integer('max_length')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_fields');
    }
};
