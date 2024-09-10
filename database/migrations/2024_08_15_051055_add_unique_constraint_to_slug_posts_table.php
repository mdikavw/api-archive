<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table)
        {
            $table->unique('slug'); // Add unique constraint to the existing slug column
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table)
        {
            $table->dropUnique(['slug']); // Drop the unique constraint if rolling back
        });
    }
};
