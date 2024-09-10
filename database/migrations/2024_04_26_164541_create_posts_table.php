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
        Schema::create('posts', function (Blueprint $table)
        {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('image_path')->nullable();
            $table->string('slug');
            $table->enum('type', ['drawer', 'profile'])->default('profile');
            $table->unsignedBigInteger('drawer_id')->nullable();
            $table->unsignedBigInteger('post_status_id')->default(3);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('drawer_id')->references('id')->on('drawers')->onDelete('cascade');
            $table->foreign('post_status_id')->references('id')->on('post_statuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
